"""Second-stage, low-turnover session tests using the cached Delta candles.

This deliberately tests a small family of economically distinct ideas instead
of adding more indicators to the first-pass setups: follow/fade the New York
opening drive, follow/fade the preceding four-hour move, or hold the prevailing
hourly trend through the U.S. session.
"""

from __future__ import annotations

import itertools
import json
from pathlib import Path

import numpy as np
import pandas as pd

from btc_eth_intraday_backtest import Costs, Trade, add_indicators, load_or_download, metrics


START = pd.Timestamp("2023-01-01", tz="UTC")
END = pd.Timestamp("2026-08-01", tz="UTC")
TRAIN_END = pd.Timestamp("2025-01-01", tz="UTC")
CACHE = Path("storage/app/research/market-data")


def simulate_trade(
    model: str,
    symbol: str,
    session_date: str,
    day: pd.DataFrame,
    signal_time: pd.Timestamp,
    side: int,
    strength: float,
    stop_atr: float,
    reward_risk: float,
    costs: Costs,
) -> Trade | None:
    signal_pos = int(day.index.get_loc(signal_time))
    entry_pos = signal_pos + 1
    if entry_pos >= len(day):
        return None
    signal = day.iloc[signal_pos]
    entry_bar = day.iloc[entry_pos]
    entry = float(entry_bar["open"])
    stop_distance = float(signal["atr"]) * stop_atr
    if not np.isfinite(stop_distance) or stop_distance <= 0:
        return None
    stop = entry - side * stop_distance
    target = entry + side * reward_risk * stop_distance

    exit_time = day.index[-1]
    exit_price = float(day.iloc[-1]["close"])
    reason = "time"
    for timestamp, bar in day.iloc[entry_pos:].iterrows():
        stopped = bar["low"] <= stop if side == 1 else bar["high"] >= stop
        targeted = bar["high"] >= target if side == 1 else bar["low"] <= target
        if stopped:  # conservative ordering if both occur in one candle
            exit_time, exit_price, reason = timestamp, stop, "stop"
            break
        if targeted:
            exit_time, exit_price, reason = timestamp, target, "target"
            break

    gross_return = side * (exit_price - entry) / entry
    return Trade(
        strategy=model,
        symbol=symbol,
        session_date=session_date,
        side=side,
        signal_time=signal_time,
        entry_time=day.index[entry_pos],
        exit_time=exit_time,
        entry=entry,
        exit=exit_price,
        stop=stop,
        target=target,
        exit_reason=reason,
        gross_return=gross_return,
        net_return=gross_return - costs.round_trip,
        stop_pct=stop_distance / entry,
        strength=strength,
    )


def candidates_for_model(
    frame: pd.DataFrame,
    symbol: str,
    family: str,
    mode: str,
    threshold: float,
    stop_atr: float,
    reward_risk: float,
    costs: Costs,
) -> list[Trade]:
    output: list[Trade] = []
    four_hour_return = frame["close"].pct_change(16)
    four_hour_scale = (frame["atr"] / frame["close"]) * np.sqrt(16)

    for session_date, all_day in frame.groupby("ny_date", sort=True):
        management = all_day[(all_day["ny_minutes"] >= 9 * 60 + 30) & (all_day["ny_minutes"] <= 15 * 60 + 45)]
        if len(management) < 10:
            continue
        signal_time: pd.Timestamp | None = None
        raw_side = 0
        strength = 0.0

        if family == "opening_drive":
            opening = management[(management["ny_minutes"] >= 9 * 60 + 30) & (management["ny_minutes"] < 10 * 60)]
            if len(opening) < 2:
                continue
            signal_time = opening.index[-1]
            move = float(opening.iloc[-1]["close"] / opening.iloc[0]["open"] - 1)
            scale = float(opening.iloc[-1]["atr"] / opening.iloc[-1]["close"] * np.sqrt(2))
            if scale <= 0 or not np.isfinite(scale):
                continue
            strength = abs(move) / scale
            raw_side = 1 if move > 0 else -1

        elif family == "previous_four_hours":
            opening_bar = management.iloc[0]
            signal_time = management.index[0]
            move = float(four_hour_return.loc[signal_time])
            scale = float(four_hour_scale.loc[signal_time])
            if scale <= 0 or not np.isfinite(scale):
                continue
            strength = abs(move) / scale
            raw_side = 1 if move > 0 else -1

        elif family == "session_trend":
            opening_bar = management.iloc[0]
            signal_time = management.index[0]
            separation = float((opening_bar["ema50_1h"] - opening_bar["ema200_1h"]) / opening_bar["close"])
            strength = abs(separation) / max(float(opening_bar["atr"] / opening_bar["close"]), 1e-9)
            raw_side = 1 if separation > 0 else -1
            vwap_aligned = opening_bar["close"] > opening_bar["vwap"] if raw_side == 1 else opening_bar["close"] < opening_bar["vwap"]
            if not vwap_aligned:
                continue
        else:
            raise ValueError(family)

        if strength < threshold or raw_side == 0 or signal_time is None:
            continue
        side = raw_side if mode == "momentum" else -raw_side
        model_name = f"{family}_{mode}_t{threshold}_s{stop_atr}_r{reward_risk}"
        trade = simulate_trade(
            model_name,
            symbol,
            session_date,
            management,
            signal_time,
            side,
            strength,
            stop_atr,
            reward_risk,
            costs,
        )
        if trade:
            output.append(trade)
    return output


def select_daily(candidates: list[Trade]) -> list[Trade]:
    selected: list[Trade] = []
    for _, trades in itertools.groupby(sorted(candidates, key=lambda trade: (trade.session_date, -trade.strength)), key=lambda trade: trade.session_date):
        selected.append(next(iter(trades)))
    return sorted(selected, key=lambda trade: trade.entry_time)


def main() -> None:
    frames = {
        symbol: add_indicators(load_or_download(symbol, START, END, CACHE))
        for symbol in ["BTCUSD", "ETHUSD"]
    }
    costs = Costs()
    configurations: list[tuple] = []
    for family in ["opening_drive", "previous_four_hours"]:
        for mode in ["momentum", "reversal"]:
            for threshold in [0.75, 1.25, 1.75]:
                for stop_atr, reward_risk in [(1.0, 1.5), (1.5, 2.0), (2.0, 2.0)]:
                    configurations.append((family, mode, threshold, stop_atr, reward_risk))
    for threshold in [0.5, 1.0, 1.5]:
        for stop_atr, reward_risk in [(1.0, 1.5), (1.5, 2.0), (2.0, 2.0)]:
            configurations.append(("session_trend", "momentum", threshold, stop_atr, reward_risk))

    results: list[dict] = []
    for family, mode, threshold, stop_atr, reward_risk in configurations:
        candidates: list[Trade] = []
        for symbol, frame in frames.items():
            candidates.extend(
                candidates_for_model(frame, symbol, family, mode, threshold, stop_atr, reward_risk, costs)
            )
        selected = select_daily(candidates)
        train = metrics(selected, START, TRAIN_END)
        test = metrics(selected, TRAIN_END, END)
        results.append(
            {
                "family": family,
                "mode": mode,
                "threshold": threshold,
                "stop_atr": stop_atr,
                "reward_risk": reward_risk,
                "train": train,
                "test": test,
            }
        )

    eligible = [row for row in results if row["train"].get("trades", 0) >= 50]
    eligible.sort(key=lambda row: (row["train"].get("profit_factor", 0), row["train"].get("net_return", -1)), reverse=True)
    report = {
        "selection_rule": "ranked on 2023-2024 only; 2025-2026 metrics revealed afterward",
        "top_training_configurations": eligible[:10],
        "top_training_choice": eligible[0] if eligible else None,
        "profitable_in_both_windows": [
            row for row in eligible
            if row["train"].get("net_return", -1) > 0 and row["test"].get("net_return", -1) > 0
        ],
        "all_configurations": results,
    }
    output = Path("storage/app/research/btc-eth-session-experiment.json")
    output.write_text(json.dumps(report, indent=2, allow_nan=False), encoding="utf-8")
    print(json.dumps({key: value for key, value in report.items() if key != "all_configurations"}, indent=2, allow_nan=False))


if __name__ == "__main__":
    main()
