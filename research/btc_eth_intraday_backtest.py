"""Research harness for low-frequency BTC/ETH intraday strategies.

The script downloads public 15-minute BTCUSD and ETHUSD perpetual candles from
Delta Exchange India, caches them locally, and compares a small set of
pre-declared strategies. It is intentionally conservative about same-candle
stop/target ambiguity and includes Delta taker fees, GST, and estimated
slippage.
"""

from __future__ import annotations

import argparse
import json
import math
import time
import urllib.parse
import urllib.request
from dataclasses import dataclass
from pathlib import Path
from zoneinfo import ZoneInfo

import numpy as np
import pandas as pd


BASE_URL = "https://api.india.delta.exchange/v2/history/candles"
NY = ZoneInfo("America/New_York")
UTC = ZoneInfo("UTC")
BAR_SECONDS = 15 * 60
MAX_CANDLES_PER_REQUEST = 1_900


@dataclass(frozen=True)
class Costs:
    taker_fee: float = 0.0005
    gst_rate: float = 0.18
    slippage_per_side: float = 0.0001

    @property
    def round_trip(self) -> float:
        return 2 * (self.taker_fee * (1 + self.gst_rate) + self.slippage_per_side)


@dataclass
class Trade:
    strategy: str
    symbol: str
    session_date: str
    side: int
    signal_time: pd.Timestamp
    entry_time: pd.Timestamp
    exit_time: pd.Timestamp
    entry: float
    exit: float
    stop: float
    target: float
    exit_reason: str
    gross_return: float
    net_return: float
    stop_pct: float
    strength: float


def _request_json(url: str, attempts: int = 4) -> dict:
    headers = {"Accept": "application/json", "User-Agent": "TradeYatra-Research/1.0"}
    last_error: Exception | None = None
    for attempt in range(attempts):
        try:
            request = urllib.request.Request(url, headers=headers)
            with urllib.request.urlopen(request, timeout=30) as response:
                return json.loads(response.read().decode("utf-8"))
        except Exception as exc:  # network retries are bounded
            last_error = exc
            time.sleep(1.5 * (attempt + 1))
    raise RuntimeError(f"Delta candle request failed: {last_error}")


def download_candles(symbol: str, start: pd.Timestamp, end: pd.Timestamp) -> pd.DataFrame:
    start_s = int(start.timestamp())
    end_s = int(end.timestamp())
    step = BAR_SECONDS * MAX_CANDLES_PER_REQUEST
    rows: list[dict] = []

    cursor = start_s
    while cursor < end_s:
        chunk_end = min(cursor + step, end_s)
        query = urllib.parse.urlencode(
            {
                "resolution": "15m",
                "symbol": symbol,
                "start": cursor,
                "end": chunk_end,
            }
        )
        payload = _request_json(f"{BASE_URL}?{query}")
        if payload.get("success") is False:
            raise RuntimeError(f"Delta rejected {symbol} candle request: {payload}")
        rows.extend(payload.get("result", []))
        cursor = chunk_end + 1

    if not rows:
        raise RuntimeError(f"No candle data returned for {symbol}")

    frame = pd.DataFrame(rows)
    frame = frame.rename(columns={"time": "timestamp"})
    frame["timestamp"] = pd.to_datetime(frame["timestamp"], unit="s", utc=True)
    for column in ["open", "high", "low", "close", "volume"]:
        frame[column] = pd.to_numeric(frame[column], errors="coerce")
    frame = (
        frame.dropna(subset=["timestamp", "open", "high", "low", "close"])
        .drop_duplicates("timestamp", keep="last")
        .sort_values("timestamp")
        .set_index("timestamp")
    )
    return frame.loc[(frame.index >= start) & (frame.index < end)]


def load_or_download(symbol: str, start: pd.Timestamp, end: pd.Timestamp, cache_dir: Path) -> pd.DataFrame:
    cache_dir.mkdir(parents=True, exist_ok=True)
    cache_file = cache_dir / f"{symbol}_15m_{start:%Y%m%d}_{end:%Y%m%d}.csv"
    if cache_file.exists():
        frame = pd.read_csv(cache_file, parse_dates=["timestamp"]).set_index("timestamp")
        frame.index = pd.to_datetime(frame.index, utc=True)
        return frame

    frame = download_candles(symbol, start, end)
    frame.to_csv(cache_file, index_label="timestamp")
    return frame


def add_indicators(frame: pd.DataFrame) -> pd.DataFrame:
    df = frame.copy()
    previous_close = df["close"].shift(1)
    true_range = pd.concat(
        [
            df["high"] - df["low"],
            (df["high"] - previous_close).abs(),
            (df["low"] - previous_close).abs(),
        ],
        axis=1,
    ).max(axis=1)
    df["atr"] = true_range.ewm(alpha=1 / 14, adjust=False, min_periods=14).mean()
    df["ema20"] = df["close"].ewm(span=20, adjust=False, min_periods=20).mean()
    df["volume_median20"] = df["volume"].rolling(20, min_periods=20).median()

    delta = df["close"].diff()
    gains = delta.clip(lower=0).ewm(alpha=1 / 14, adjust=False, min_periods=14).mean()
    losses = (-delta.clip(upper=0)).ewm(alpha=1 / 14, adjust=False, min_periods=14).mean()
    relative_strength = gains / losses.replace(0, np.nan)
    df["rsi"] = 100 - (100 / (1 + relative_strength))

    mean20 = df["close"].rolling(20, min_periods=20).mean()
    std20 = df["close"].rolling(20, min_periods=20).std(ddof=0)
    df["bb_upper"] = mean20 + 2 * std20
    df["bb_lower"] = mean20 - 2 * std20
    df["donchian_high"] = df["high"].shift(1).rolling(20, min_periods=20).max()
    df["donchian_low"] = df["low"].shift(1).rolling(20, min_periods=20).min()

    hourly = df[["close"]].resample("1h", label="right", closed="right").last().dropna()
    hourly["ema50_1h"] = hourly["close"].ewm(span=50, adjust=False, min_periods=50).mean()
    hourly["ema200_1h"] = hourly["close"].ewm(span=200, adjust=False, min_periods=200).mean()
    for column in ["ema50_1h", "ema200_1h"]:
        df[column] = hourly[column].reindex(df.index, method="ffill")

    utc_day = pd.Series(df.index.date, index=df.index)
    typical_price = (df["high"] + df["low"] + df["close"]) / 3
    cumulative_pv = (typical_price * df["volume"]).groupby(utc_day).cumsum()
    cumulative_volume = df["volume"].groupby(utc_day).cumsum().replace(0, np.nan)
    df["vwap"] = cumulative_pv / cumulative_volume

    local_index = df.index.tz_convert(NY)
    df["ny_date"] = local_index.date.astype(str)
    df["ny_minutes"] = local_index.hour * 60 + local_index.minute
    return df


def _trend_side(row: pd.Series) -> int:
    if row["ema50_1h"] > row["ema200_1h"]:
        return 1
    if row["ema50_1h"] < row["ema200_1h"]:
        return -1
    return 0


def _simulate_exit(
    day: pd.DataFrame,
    entry_pos: int,
    side: int,
    entry: float,
    stop: float,
    target: float,
) -> tuple[pd.Timestamp, float, str]:
    # If stop and target are touched in one candle, assume the stop happened
    # first. This avoids optimistic conclusions from 15-minute OHLC data.
    for timestamp, bar in day.iloc[entry_pos:].iterrows():
        if side == 1:
            stopped = bar["low"] <= stop
            targeted = bar["high"] >= target
        else:
            stopped = bar["high"] >= stop
            targeted = bar["low"] <= target
        if stopped:
            return timestamp, stop, "stop"
        if targeted:
            return timestamp, target, "target"
    timestamp = day.index[-1]
    return timestamp, float(day.iloc[-1]["close"]), "time"


def _make_trade(
    strategy: str,
    symbol: str,
    session_date: str,
    day: pd.DataFrame,
    signal_pos: int,
    side: int,
    stop_atr: float,
    reward_risk: float,
    strength: float,
    costs: Costs,
) -> Trade | None:
    entry_pos = signal_pos + 1
    if entry_pos >= len(day):
        return None
    signal = day.iloc[signal_pos]
    entry_bar = day.iloc[entry_pos]
    entry = float(entry_bar["open"])
    atr = float(signal["atr"])
    if not math.isfinite(atr) or atr <= 0 or entry <= 0:
        return None
    stop_distance = stop_atr * atr
    stop = entry - side * stop_distance
    target = entry + side * reward_risk * stop_distance
    exit_time, exit_price, exit_reason = _simulate_exit(day, entry_pos, side, entry, stop, target)
    gross_return = side * (exit_price - entry) / entry
    return Trade(
        strategy=strategy,
        symbol=symbol,
        session_date=session_date,
        side=side,
        signal_time=day.index[signal_pos],
        entry_time=day.index[entry_pos],
        exit_time=exit_time,
        entry=entry,
        exit=exit_price,
        stop=stop,
        target=target,
        exit_reason=exit_reason,
        gross_return=gross_return,
        net_return=gross_return - costs.round_trip,
        stop_pct=stop_distance / entry,
        strength=strength,
    )


def generate_candidates(strategy: str, symbol: str, df: pd.DataFrame, costs: Costs) -> list[Trade]:
    candidates: list[Trade] = []
    for session_date, all_day in df.groupby("ny_date", sort=True):
        opening = all_day[(all_day["ny_minutes"] >= 8 * 60 + 30) & (all_day["ny_minutes"] < 9 * 60 + 30)]
        trading = all_day[(all_day["ny_minutes"] >= 9 * 60 + 30) & (all_day["ny_minutes"] <= 12 * 60)]
        management = all_day[(all_day["ny_minutes"] >= 9 * 60 + 30) & (all_day["ny_minutes"] <= 15 * 60 + 45)]
        if len(opening) < 3 or len(trading) < 2 or len(management) < 2:
            continue

        range_high = float(opening["high"].max())
        range_low = float(opening["low"].min())
        day = management.copy()
        signal_timestamp: pd.Timestamp | None = None
        signal_side = 0
        strength = 0.0
        stop_atr = 1.0
        reward_risk = 1.8

        for timestamp, row in trading.iterrows():
            trend_side = _trend_side(row)
            atr = float(row["atr"])
            if not math.isfinite(atr) or atr <= 0:
                continue
            relative_volume = float(row["volume"] / row["volume_median20"]) if row["volume_median20"] > 0 else 0

            if strategy == "ny_orb_trend":
                long_signal = trend_side == 1 and row["close"] > range_high + 0.05 * atr and row["close"] > row["vwap"]
                short_signal = trend_side == -1 and row["close"] < range_low - 0.05 * atr and row["close"] < row["vwap"]
                if relative_volume < 1.0:
                    continue
                signal_side = 1 if long_signal else -1 if short_signal else 0
                strength = abs(float(row["close"] - (range_high if signal_side == 1 else range_low))) / atr if signal_side else 0

            elif strategy == "donchian_trend":
                long_signal = trend_side == 1 and row["close"] > row["donchian_high"] and row["close"] > row["vwap"]
                short_signal = trend_side == -1 and row["close"] < row["donchian_low"] and row["close"] < row["vwap"]
                if relative_volume < 1.0:
                    continue
                signal_side = 1 if long_signal else -1 if short_signal else 0
                boundary = row["donchian_high"] if signal_side == 1 else row["donchian_low"]
                strength = abs(float(row["close"] - boundary)) / atr if signal_side else 0

            elif strategy == "vwap_pullback":
                previous = df.loc[:timestamp].iloc[-2] if len(df.loc[:timestamp]) >= 2 else None
                if previous is None:
                    continue
                long_signal = (
                    trend_side == 1
                    and previous["close"] <= previous["ema20"]
                    and row["close"] > row["ema20"]
                    and row["close"] > row["vwap"]
                    and row["rsi"] >= 50
                )
                short_signal = (
                    trend_side == -1
                    and previous["close"] >= previous["ema20"]
                    and row["close"] < row["ema20"]
                    and row["close"] < row["vwap"]
                    and row["rsi"] <= 50
                )
                signal_side = 1 if long_signal else -1 if short_signal else 0
                strength = abs(float(row["close"] - row["ema20"])) / atr if signal_side else 0

            elif strategy == "bollinger_reversal":
                long_signal = row["close"] < row["bb_lower"] and row["rsi"] < 25
                short_signal = row["close"] > row["bb_upper"] and row["rsi"] > 75
                signal_side = 1 if long_signal else -1 if short_signal else 0
                strength = abs(50 - float(row["rsi"])) / 25 if signal_side else 0
                reward_risk = 1.2

            else:
                raise ValueError(f"Unknown strategy: {strategy}")

            if signal_side:
                signal_timestamp = timestamp
                break

        if signal_timestamp is None:
            continue
        signal_pos = int(day.index.get_loc(signal_timestamp))
        trade = _make_trade(
            strategy,
            symbol,
            session_date,
            day,
            signal_pos,
            signal_side,
            stop_atr,
            reward_risk,
            strength,
            costs,
        )
        if trade:
            candidates.append(trade)
    return candidates


def select_one_trade_per_day(candidates: list[Trade]) -> list[Trade]:
    by_day: dict[str, list[Trade]] = {}
    for trade in candidates:
        by_day.setdefault(trade.session_date, []).append(trade)
    selected: list[Trade] = []
    for trades in by_day.values():
        # BTC and ETH are strongly related; take only the earliest clean setup.
        # If simultaneous, prefer the greater ATR-normalized breakout strength.
        trades.sort(key=lambda trade: (trade.signal_time, -trade.strength))
        earliest = trades[0].signal_time
        simultaneous = [trade for trade in trades if trade.signal_time == earliest]
        selected.append(max(simultaneous, key=lambda trade: trade.strength))
    return sorted(selected, key=lambda trade: trade.entry_time)


def metrics(trades: list[Trade], start: pd.Timestamp, end: pd.Timestamp) -> dict:
    relevant = [trade for trade in trades if start <= trade.entry_time < end]
    if not relevant:
        return {"trades": 0}

    risk_fraction = 0.005
    max_notional_multiple = 2.0
    equity = 1.0
    peak = 1.0
    max_drawdown = 0.0
    account_returns: list[float] = []
    wins = 0
    gross_profit = 0.0
    gross_loss = 0.0
    for trade in relevant:
        notional_multiple = min(max_notional_multiple, risk_fraction / trade.stop_pct)
        account_return = notional_multiple * trade.net_return
        account_returns.append(account_return)
        equity *= 1 + account_return
        peak = max(peak, equity)
        max_drawdown = min(max_drawdown, equity / peak - 1)
        if account_return > 0:
            wins += 1
            gross_profit += account_return
        elif account_return < 0:
            gross_loss += abs(account_return)

    years = max((end - start).total_seconds() / (365.25 * 86400), 1 / 365.25)
    annualized = equity ** (1 / years) - 1 if equity > 0 else -1
    return {
        "trades": len(relevant),
        "trades_per_week": len(relevant) / max(years * 52.1775, 1),
        "win_rate": wins / len(relevant),
        "profit_factor": gross_profit / gross_loss if gross_loss > 0 else math.inf,
        "net_return": equity - 1,
        "annualized_return": annualized,
        "max_drawdown": max_drawdown,
        "avg_trade_account_return": float(np.mean(account_returns)),
    }


def main() -> None:
    parser = argparse.ArgumentParser()
    parser.add_argument("--start", default="2023-01-01")
    parser.add_argument("--end", default="2026-08-01")
    parser.add_argument("--cache-dir", default="storage/app/research/market-data")
    parser.add_argument("--output", default="storage/app/research/btc-eth-intraday-results.json")
    args = parser.parse_args()

    start = pd.Timestamp(args.start, tz="UTC")
    end = pd.Timestamp(args.end, tz="UTC")
    cache_dir = Path(args.cache_dir)
    frames = {
        symbol: add_indicators(load_or_download(symbol, start, end, cache_dir))
        for symbol in ["BTCUSD", "ETHUSD"]
    }
    costs = Costs()
    strategies = ["ny_orb_trend", "donchian_trend", "vwap_pullback", "bollinger_reversal"]
    train_start = start
    train_end = pd.Timestamp("2025-01-01", tz="UTC")
    test_start = train_end

    report = {
        "assumptions": {
            "exchange": "Delta Exchange India BTCUSD/ETHUSD perpetual candles",
            "bar_size": "15m",
            "round_trip_cost": costs.round_trip,
            "risk_per_trade": 0.005,
            "max_notional_multiple": 2.0,
            "portfolio_limit": "one BTC or ETH trade per New York session",
            "same_bar_ambiguity": "stop assumed before target",
        },
        "data": {
            symbol: {
                "rows": len(frame),
                "first": frame.index.min().isoformat(),
                "last": frame.index.max().isoformat(),
            }
            for symbol, frame in frames.items()
        },
        "strategies": {},
    }

    for strategy in strategies:
        all_candidates: list[Trade] = []
        for symbol, frame in frames.items():
            all_candidates.extend(generate_candidates(strategy, symbol, frame, costs))
        selected = select_one_trade_per_day(all_candidates)
        report["strategies"][strategy] = {
            "train_2023_2024": metrics(selected, train_start, train_end),
            "test_2025_2026": metrics(selected, test_start, end),
            "all": metrics(selected, start, end),
            "symbol_counts": {
                symbol: sum(1 for trade in selected if trade.symbol == symbol)
                for symbol in frames
            },
        }

    output = Path(args.output)
    output.parent.mkdir(parents=True, exist_ok=True)
    output.write_text(json.dumps(report, indent=2, allow_nan=False), encoding="utf-8")
    print(json.dumps(report, indent=2, allow_nan=False))


if __name__ == "__main__":
    main()
