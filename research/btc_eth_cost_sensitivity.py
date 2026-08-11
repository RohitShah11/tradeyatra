"""Cost and symbol sensitivity for the best pre-holdout session rule."""

from __future__ import annotations

import json
from pathlib import Path

import pandas as pd

from btc_eth_intraday_backtest import Costs, add_indicators, load_or_download, metrics
from btc_eth_session_experiment import candidates_for_model, select_daily


START = pd.Timestamp("2023-01-01", tz="UTC")
END = pd.Timestamp("2026-08-01", tz="UTC")
SPLIT = pd.Timestamp("2025-01-01", tz="UTC")
CACHE = Path("storage/app/research/market-data")


def main() -> None:
    frames = {
        symbol: add_indicators(load_or_download(symbol, START, END, CACHE))
        for symbol in ["BTCUSD", "ETHUSD"]
    }
    regimes = {
        "taker_taker": Costs(taker_fee=0.0005, gst_rate=0.18, slippage_per_side=0.0001),
        # Equivalent to one 0.02% maker side and one 0.05% taker side, both
        # subject to GST, plus 0.005% estimated slippage on each side.
        "maker_taker": Costs(taker_fee=0.00035, gst_rate=0.18, slippage_per_side=0.00005),
        "maker_maker": Costs(taker_fee=0.0002, gst_rate=0.18, slippage_per_side=0.00005),
        "zero_cost_upper_bound": Costs(taker_fee=0, gst_rate=0, slippage_per_side=0),
    }
    report: dict = {}
    for label, costs in regimes.items():
        by_symbol = {
            symbol: candidates_for_model(
                frame,
                symbol,
                family="opening_drive",
                mode="momentum",
                threshold=1.25,
                stop_atr=2.0,
                reward_risk=2.0,
                costs=costs,
            )
            for symbol, frame in frames.items()
        }
        portfolio = select_daily(by_symbol["BTCUSD"] + by_symbol["ETHUSD"])
        report[label] = {
            "round_trip_cost": costs.round_trip,
            "portfolio": {
                "train": metrics(portfolio, START, SPLIT),
                "test": metrics(portfolio, SPLIT, END),
            },
            "BTCUSD": {
                "train": metrics(by_symbol["BTCUSD"], START, SPLIT),
                "test": metrics(by_symbol["BTCUSD"], SPLIT, END),
            },
            "ETHUSD": {
                "train": metrics(by_symbol["ETHUSD"], START, SPLIT),
                "test": metrics(by_symbol["ETHUSD"], SPLIT, END),
            },
        }
    output = Path("storage/app/research/btc-eth-cost-sensitivity.json")
    output.write_text(json.dumps(report, indent=2, allow_nan=False), encoding="utf-8")
    print(json.dumps(report, indent=2, allow_nan=False))


if __name__ == "__main__":
    main()
