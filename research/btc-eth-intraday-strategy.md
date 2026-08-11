# BTC/ETH intraday strategy research

Research date: 5 August 2026

## Conclusion

No tested strategy had a sufficiently robust net edge with ordinary taker
execution. The best candidate was a low-frequency New York opening-drive
momentum rule. It was close to break-even with one maker and one taker fill and
modestly profitable only under the optimistic assumption that both sides could
be executed at maker-like cost. It should therefore be forward-tested on paper,
not deployed with meaningful capital.

## Paper-trading candidate: New York opening drive

Market: Delta Exchange India BTCUSD and ETHUSD perpetual futures.

Chart and timezone: 15-minute candles, `America/New_York`.

Rules:

1. Observe the two candles from 09:30 through 10:00 New York time.
2. At the close of the 09:45 candle, calculate:

   `drive score = abs(close[09:45] - open[09:30]) / (ATR(14) * sqrt(2))`

3. A setup exists only when the drive score is at least 1.25.
4. Trade in the direction of the opening drive at the open of the 10:00 candle.
5. If both BTC and ETH qualify, trade only the asset with the larger drive
   score. Do not take both correlated positions.
6. Initial stop: 2.0 times 15-minute ATR(14) from entry.
7. Profit target: 2R, which is 4.0 times ATR from entry.
8. If neither stop nor target is reached, exit at the 16:00 New York close.
9. Maximum one trade per New York session; no re-entry after a loss.

Risk rules:

- Risk 0.25% while paper testing; never exceed 0.50% after validation.
- `position quantity = account equity * risk fraction / stop distance`.
- Cap position notional at two times account equity, regardless of the
  exchange's available leverage.
- On scheduled FOMC days, do not hold this setup through the 14:00 New York
  announcement. This is an operational safety rule and was not separately
  backtested.

## Backtest design

- Public Delta Exchange India 15-minute candles.
- BTC data: 29 December 2023 through 31 July 2026.
- ETH data: 6 February 2024 through 31 July 2026.
- Development window ended 31 December 2024.
- Holdout window: 1 January 2025 through 31 July 2026.
- One BTC-or-ETH portfolio trade per session.
- 0.50% account risk per trade with a two-times-notional cap.
- If stop and target were both touched inside one 15-minute candle, the stop
  was assumed to occur first.
- Funding was not modelled. Any funding paid would reduce the result.

### Best candidate results

| Cost assumption | Earlier window | Holdout window | Holdout profit factor | Holdout max drawdown |
|---|---:|---:|---:|---:|
| Taker entry + taker exit | -1.03% | -5.12% | 0.916 | -8.50% |
| Maker entry + taker exit | +1.89% | +0.13% | 1.010 | -6.96% |
| Maker entry + maker exit | +4.23% | +4.43% | 1.091 | -6.40% |
| Zero-cost upper bound | +8.11% | +11.76% | 1.242 | -5.50% |

The realistic mixed-execution result is too close to zero to establish an
edge. Maker-maker execution is not assured: post-only orders can be cancelled
or missed, and protective stops normally take liquidity.

## Rejected strategy families

With taker fees, GST, and estimated slippage included, all of these lost money
in both the earlier and holdout windows:

- trend-filtered New York opening-range breakout;
- 20-bar Donchian breakout;
- EMA/VWAP trend pullback;
- Bollinger/RSI reversal;
- prior-four-hour momentum and reversal variants;
- simple New York session trend holds.

## Forward-test acceptance gate

Freeze the rules and record at least 50 paper trades. Consider small live size
only if the forward sample has all of the following after actual estimated fees
and slippage:

- profit factor of at least 1.20;
- positive expectancy of at least 0.15R per trade;
- maximum drawdown no worse than 8R;
- no missed-fill assumption used to improve the recorded result.

## Sources

- Delta Exchange India API documentation, Historical OHLC Candles:
  https://docs.delta.exchange/
- Delta Exchange fees and GST disclosure:
  https://www.delta.exchange/fees
- Wen, Bouri, Xu, and Zhao, *Intraday Return Predictability in the
  Cryptocurrency Markets: Momentum, Reversal, or Both*:
  https://papers.ssrn.com/sol3/papers.cfm?abstract_id=4080253
- Han, Kang, and Ryu, *Momentum in the Cryptocurrency Market: A Comprehensive
  Analysis under Realistic Assumptions*:
  https://papers.ssrn.com/sol3/papers.cfm?abstract_id=4675565
- De Nicola, *On the Intraday Behavior of Bitcoin*:
  https://doi.org/10.5195/ledger.2021.213
- Yang and Wang, *Scheduled FOMC Statements and Cryptocurrency Trading
  Activity*:
  https://papers.ssrn.com/sol3/papers.cfm?abstract_id=6299551

## Reproduction

Run these scripts from the repository root using the bundled Python runtime:

- `research/btc_eth_intraday_backtest.py`
- `research/btc_eth_session_experiment.py`
- `research/btc_eth_cost_sensitivity.py`

Generated JSON reports and cached candles are written under
`storage/app/research/`.
