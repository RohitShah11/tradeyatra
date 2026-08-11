# TradingView test instructions

Script: `btc_eth_ny_opening_drive_v6.pine`

For the easiest first test, use `btc_eth_ny_opening_drive_simple_v6.pine`.

## Basic test

1. Open a standard BTC perpetual or spot chart in TradingView.
2. Select the 15-minute timeframe.
3. Open Pine Editor, paste the complete script, and choose **Add to chart**.
4. Open **Strategy Tester** and review Overview, Performance Summary, and List
   of Trades.
5. Repeat on a matching ETH chart from the same exchange and contract type.

The first character in the editor must be the first `/` in `//@version=6`.
Delete any pre-existing template code before pasting, and do not include Markdown
code-fence lines containing triple backticks. A generic "could not be
translated" error normally means TradingView did not recognize the version
directive and tried to parse the script as legacy Pine.

The strategy calculates its New York sessions with the
`America/New_York` IANA timezone. The chart can remain in India time; changing
the chart display timezone does not change the strategy logic.

## Costs

The default commission is 0.059% on every fill. This represents a 0.05% taker
fee plus 18% GST. In the strategy's **Settings > Properties**:

- keep `0.059%` per order for a conservative taker/taker test;
- use `0.0413%` per order only as an approximation of one maker and one taker
  fill;
- add realistic slippage in ticks for the exact chart symbol.

Do not evaluate the zero-cost result.

## Test BTC and ETH as one portfolio

TradingView strategies can place simulated orders only on their chart symbol.
To approximate the researched one-BTC-or-ETH-trade rule:

1. Add the strategy to both BTC and ETH 15-minute charts.
2. On BTC, enable **Trade only when this chart has the stronger score** and set
   the comparison symbol to the matching ETH market.
3. On ETH, enable the same setting and select the matching BTC market.
4. Use the same exchange, quote currency, and contract type for both symbols.

The strict greater-than comparison ensures that only the stronger score is
eligible on a normal day. Results from two TradingView Strategy Tester panels
are not a compounded portfolio equity curve; export the trades if a combined
portfolio report is needed.

## Defaults that reproduce the research rule

- Trading days: `1234567` (all days)
- ATR length: `14`
- Minimum score: `1.25`
- Stop: `2 ATR`
- Target: `2R` (`4 ATR` from entry)
- Time exit: `16:00 America/New_York`
- One setup per New York session
- Maximum notional: `2x equity`

The script defaults to 0.25% equity risk for safer paper testing. Change it to
0.50% only when comparing directly with the Python research results.

## If Strategy Tester shows no trades

The simple script displays a status table in the top-right corner. Confirm:

- status is **Loaded**;
- timeframe is **15** and shown in green;
- **Last drive score** is a number rather than "No 09:45 bar loaded";
- **Eligible setups** is greater than zero.

The simple script uses 10% of equity per position so contract sizing cannot
silently reject all orders. Temporarily enable **Diagnostic mode** to take every
non-flat 09:30-10:00 New York opening drive. If diagnostic trades appear, turn
the mode off; the absence of normal trades then means the 1.25 score threshold
was not reached in the loaded history.

## Validation checklist

- Use normal candles, not Heikin Ashi, Renko, range, or another synthetic chart.
- Confirm trades are signalled on the 09:45 New York candle and filled at the
  next 10:00 candle open.
- Confirm every trade has a 2 ATR stop and a 4 ATR target.
- Confirm remaining positions close at 16:00 New York time.
- Forward-test without changing parameters for at least 50 trades.
