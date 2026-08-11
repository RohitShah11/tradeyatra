# BTC trendline breakout and EMA pullback strategy

Pine file: `btc_trendline_ema_pullback_v6.pine`

## TradingView setup

1. Open a standard BTC candlestick chart. Start with `BINANCE:BTCUSDT` or a
   matching BTC perpetual market available to your account.
2. Select either the 1-hour or 4-hour timeframe.
3. Delete all existing Pine Editor text and paste the entire Pine file.
4. Click **Save**, then **Add to chart**.
5. Open Strategy Tester. Keep Deep Backtesting disabled for the first check.

The top-right table must show:

- `Loaded`;
- a BTC chart symbol in green;
- timeframe `60` or `240` in green;
- the current trend and setup state.

## Signal sequence

- Green chart background: bullish EMA 50/200 market regime.
- Red line: projected resistance from two confirmed lower pivot highs.
- `BREAK`: a confirmed candle closed above the descending trendline.
- Orange `PB`: price returned to the EMA 9/15 support zone.
- Green `BUY`: a later bullish candle closed above EMA 9 and above the prior
  candle's high. The simulated long order fills on the next candle.

The order has a stop below the pullback with a minimum distance of 1.25 ATR and
a default target of 2R. An optional EMA 9/15 bearish cross closes the position
early.

## Default trade size and costs

The strategy uses 10% of simulated equity per trade and does not require
leverage. Change order size under **Settings > Properties**.

Default commission is 0.059% per fill, approximating a 0.05% taker fee plus
18% GST. Add symbol-appropriate slippage under **Properties**.

## Historical signal check

Using cached Delta BTCUSD data from 29 December 2023 through 31 July 2026, the
default state sequence produced:

| Timeframe | Breakouts | EMA pullbacks | Buy confirmations |
|---|---:|---:|---:|
| 1 hour | 263 | 228 | 147 |
| 4 hours | 61 | 52 | 35 |

These are signal counts, not evidence of future profitability. Review Strategy
Tester results after commissions and slippage before paper trading.
