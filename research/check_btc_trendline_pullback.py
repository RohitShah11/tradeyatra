"""Count signals for the Pine BTC trendline/EMA pullback defaults."""

from pathlib import Path

import numpy as np
import pandas as pd


SOURCE = Path("storage/app/research/market-data/BTCUSD_15m_20230101_20260801.csv")


def ema(series: pd.Series, length: int) -> pd.Series:
    return series.ewm(span=length, adjust=False, min_periods=length).mean()


def check(rule: str) -> dict:
    raw = pd.read_csv(SOURCE, parse_dates=["timestamp"]).set_index("timestamp").sort_index()
    bars = raw.resample(rule, label="left", closed="left").agg(
        {"open": "first", "high": "max", "low": "min", "close": "last", "volume": "sum"}
    ).dropna()
    bars["ema9"] = ema(bars["close"], 9)
    bars["ema15"] = ema(bars["close"], 15)
    bars["ema50"] = ema(bars["close"], 50)
    bars["ema200"] = ema(bars["close"], 200)
    previous_close = bars["close"].shift()
    tr = pd.concat(
        [
            bars["high"] - bars["low"],
            (bars["high"] - previous_close).abs(),
            (bars["low"] - previous_close).abs(),
        ],
        axis=1,
    ).max(axis=1)
    bars["atr"] = tr.ewm(alpha=1 / 14, adjust=False, min_periods=14).mean()

    strength = 3
    hours = pd.Timedelta(rule).total_seconds() / 3600
    maximum_wait = max(2, round(48 / hours))
    older_price = newer_price = np.nan
    older_index = newer_index = None
    previous_line = np.nan
    state = 0
    breakout_index = pullback_index = None
    pullback_low = np.nan
    counts = {"bars": len(bars), "breakouts": 0, "pullbacks": 0, "buys": 0}

    for i in range(len(bars)):
        row = bars.iloc[i]
        new_pivot = False
        pivot_index = i - strength
        if pivot_index >= strength:
            window = bars["high"].iloc[pivot_index - strength:pivot_index + strength + 1]
            if len(window) == strength * 2 + 1 and bars["high"].iloc[pivot_index] == window.max():
                older_price, older_index = newer_price, newer_index
                newer_price, newer_index = float(bars["high"].iloc[pivot_index]), pivot_index
                new_pivot = True

        descending = older_index is not None and newer_index is not None and newer_price < older_price
        line = np.nan
        line_current = False
        if descending:
            slope = (newer_price - older_price) / (newer_index - older_index)
            line = newer_price + slope * (i - newer_index)
            line_current = i - newer_index <= 100 and line > 0

        bullish = (
            np.isfinite(row["ema200"])
            and row["close"] > row["ema200"]
            and row["ema50"] > row["ema200"]
            and row["ema50"] > bars["ema50"].iloc[i - 1]
            and row["ema200"] >= bars["ema200"].iloc[i - 1]
        ) if i > 0 else False
        crossed = line_current and np.isfinite(previous_line) and bars["close"].iloc[i - 1] <= previous_line and row["close"] > line
        breakout = bullish and crossed and row["close"] > row["ema9"] and not new_pivot
        if breakout:
            counts["breakouts"] += 1
            state, breakout_index, pullback_index, pullback_low = 1, i, None, np.nan

        if state and (not bullish or i - breakout_index > maximum_wait):
            state, breakout_index, pullback_index, pullback_low = 0, None, None, np.nan

        zone_top = max(row["ema9"], row["ema15"]) * 1.0025
        zone_bottom = min(row["ema9"], row["ema15"]) * 0.9975
        touches = row["low"] <= zone_top and row["high"] >= zone_bottom and row["close"] >= row["ema15"]
        if state == 1 and i > breakout_index and touches:
            counts["pullbacks"] += 1
            state, pullback_index, pullback_low = 2, i, row["low"]
        if state == 2:
            pullback_low = min(pullback_low, row["low"])
            confirms = i > pullback_index and row["close"] > row["open"] and row["close"] > row["ema9"] and row["ema9"] > row["ema15"] and row["close"] > bars["high"].iloc[i - 1]
            if confirms and bullish:
                counts["buys"] += 1
                state, breakout_index, pullback_index, pullback_low = 0, None, None, np.nan
        previous_line = line

    counts["first"] = bars.index.min().isoformat()
    counts["last"] = bars.index.max().isoformat()
    return counts


for timeframe in ["1h", "4h"]:
    print(timeframe, check(timeframe))
