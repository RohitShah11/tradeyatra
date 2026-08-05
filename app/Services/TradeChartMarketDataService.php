<?php

namespace App\Services;

use App\Models\Trade;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

class TradeChartMarketDataService
{
    private const INTERVAL_SECONDS = [
        '1m' => 60,
        '3m' => 180,
        '5m' => 300,
        '15m' => 900,
        '30m' => 1800,
        '1h' => 3600,
        '2h' => 7200,
        '4h' => 14400,
        '6h' => 21600,
        '1d' => 86400,
    ];

    public static function intervals(): array
    {
        return array_keys(self::INTERVAL_SECONDS);
    }

    public function candles(Trade $trade, string $interval): array
    {
        if (! isset(self::INTERVAL_SECONDS[$interval])) {
            throw new RuntimeException('The selected chart timeframe is not supported.');
        }

        [$start, $end] = $this->chartRange($trade, $interval);
        $provider = $this->providerFor($trade);
        $symbol = $this->cleanSymbol($trade->pair);
        $cacheKey = implode(':', ['trade-chart', $provider, $symbol, $interval, $start, $end]);

        return Cache::remember($cacheKey, now()->addMinutes(3), function () use ($provider, $symbol, $interval, $start, $end) {
            if ($provider === 'delta') {
                return $this->deltaCandles($symbol, $interval, $start, $end);
            }

            if ($provider === 'shark') {
                try {
                    return $this->sharkCandles($symbol, $interval, $start, $end);
                } catch (Throwable $exception) {
                    if ($this->isBinanceSymbol($symbol)) {
                        $fallback = $this->binanceCandles($symbol, $interval, $start, $end);
                        $fallback['warning'] = 'Shark market candles were unavailable, so the chart is using the matching Binance public symbol.';

                        return $fallback;
                    }

                    throw $exception;
                }
            }

            return $this->binanceCandles($symbol, $interval, $start, $end);
        });
    }

    private function chartRange(Trade $trade, string $interval): array
    {
        $seconds = self::INTERVAL_SECONDS[$interval];
        $entry = $this->entryTimestamp($trade);
        $exit = $this->exitTimestamp($trade, $entry) ?? ($entry + ($seconds * 160));
        $spanBars = (int) ceil(max(0, $exit - $entry) / $seconds);

        if ($spanBars > 1700) {
            throw new RuntimeException('This trade is too long for the selected timeframe. Choose a larger timeframe to keep both entry and exit visible.');
        }

        $paddingBars = min(100, max(40, (int) floor((1900 - $spanBars) / 2)));

        return [
            max(0, $entry - ($paddingBars * $seconds)),
            $exit + ($paddingBars * $seconds),
        ];
    }

    private function entryTimestamp(Trade $trade): int
    {
        $timezone = $trade->user?->timezone ?: config('app.timezone', 'UTC');
        $date = $trade->date?->toDateString() ?: Carbon::parse($trade->date)->toDateString();
        $time = $trade->time ? substr((string) $trade->time, 0, 8) : '00:00:00';

        return Carbon::parse($date.' '.$time, $timezone)->utc()->timestamp;
    }

    private function exitTimestamp(Trade $trade, int $entry): ?int
    {
        if (! $trade->exit_time) {
            return null;
        }

        $timezone = $trade->user?->timezone ?: config('app.timezone', 'UTC');
        $hasExitDate = (bool) $trade->exit_date;
        $date = $trade->exit_date?->toDateString() ?: $trade->date?->toDateString();
        $exit = Carbon::parse($date.' '.substr((string) $trade->exit_time, 0, 8), $timezone)->utc()->timestamp;

        if (! $hasExitDate && $exit < $entry) {
            $exit += 86400;
        }

        return max($entry, $exit);
    }

    private function providerFor(Trade $trade): string
    {
        $broker = strtolower((string) $trade->broker);
        $symbol = $this->cleanSymbol($trade->pair);

        if (str_contains($broker, 'delta') || (str_ends_with($symbol, 'USD') && ! str_ends_with($symbol, 'USDT'))) {
            return 'delta';
        }

        if (str_contains($broker, 'shark') || str_ends_with($symbol, 'INR')) {
            return 'shark';
        }

        return 'binance';
    }

    private function deltaCandles(string $symbol, string $interval, int $start, int $end): array
    {
        $response = Http::acceptJson()->timeout(15)->get('https://api.india.delta.exchange/v2/history/candles', [
            'resolution' => $interval,
            'symbol' => $symbol,
            'start' => $start,
            'end' => $end,
        ]);

        if ($response->failed() || $response->json('success') === false) {
            throw new RuntimeException($response->json('error.message') ?: 'Delta Exchange candle data is unavailable for this symbol.');
        }

        return $this->result($response->json() ?: [], 'Delta Exchange India', $symbol, $interval, $start, $end);
    }

    private function sharkCandles(string $symbol, string $interval, int $start, int $end): array
    {
        $response = Http::acceptJson()->timeout(15)->post('https://api.sharkexchange.in/v1/market/klines?priceType=MARK_PRICE', [
            'symbol' => $symbol,
            'interval' => $interval,
            'startTime' => $start * 1000,
            'endTime' => $end * 1000,
            'limit' => 2000,
        ]);

        if ($response->failed()) {
            throw new RuntimeException($response->json('message') ?: 'Shark Exchange candle data is unavailable for this symbol.');
        }

        return $this->result($response->json() ?: [], 'Shark Exchange mark price', $symbol, $interval, $start, $end);
    }

    private function binanceCandles(string $symbol, string $interval, int $start, int $end): array
    {
        if (! $this->isBinanceSymbol($symbol)) {
            throw new RuntimeException('No public candle source is configured for this market symbol.');
        }

        $response = Http::acceptJson()->timeout(15)->get('https://data-api.binance.vision/api/v3/klines', [
            'symbol' => $symbol,
            'interval' => $interval,
            'startTime' => $start * 1000,
            'endTime' => $end * 1000,
            'limit' => 1000,
        ]);

        if ($response->failed()) {
            throw new RuntimeException($response->json('msg') ?: 'Binance public candle data is unavailable for this symbol.');
        }

        return $this->result($response->json() ?: [], 'Binance public market data', $symbol, $interval, $start, $end);
    }

    private function result(array $payload, string $source, string $symbol, string $interval, int $start, int $end): array
    {
        $candles = collect($this->records($payload))
            ->map(fn ($record) => $this->normalizeCandle($record))
            ->filter()
            ->keyBy('time')
            ->sortKeys()
            ->values()
            ->all();

        if ($candles === []) {
            throw new RuntimeException('The market-data provider returned no candles for this trade period. Check the symbol and timeframe.');
        }

        return [
            'candles' => $candles,
            'source' => $source,
            'symbol' => $symbol,
            'interval' => $interval,
            'start' => $start,
            'end' => $end,
        ];
    }

    private function records(array $payload): array
    {
        if (array_is_list($payload)) {
            return $payload;
        }

        foreach (['result', 'data.rows', 'data.result', 'data', 'rows'] as $path) {
            $records = Arr::get($payload, $path);

            if (is_array($records) && array_is_list($records)) {
                return $records;
            }
        }

        return [];
    }

    private function normalizeCandle(mixed $record): ?array
    {
        if (! is_array($record)) {
            return null;
        }

        if (array_is_list($record)) {
            [$timestamp, $open, $high, $low, $close] = array_pad(array_slice($record, 0, 5), 5, null);
            $volume = $record[5] ?? 0;
        } else {
            $timestamp = $this->first($record, ['time', 'timestamp', 'openTime', 'open_time', 'startTime', 'start_time', 't']);
            $open = $this->first($record, ['open', 'o']);
            $high = $this->first($record, ['high', 'h']);
            $low = $this->first($record, ['low', 'l']);
            $close = $this->first($record, ['close', 'c']);
            $volume = $this->first($record, ['volume', 'v']) ?? 0;
        }

        if (! is_numeric($timestamp) || ! is_numeric($open) || ! is_numeric($high) || ! is_numeric($low) || ! is_numeric($close)) {
            return null;
        }

        $timestamp = (int) $timestamp;
        if ($timestamp > 9999999999) {
            $timestamp = (int) floor($timestamp / 1000);
        }

        return [
            'time' => $timestamp,
            'open' => (float) $open,
            'high' => (float) $high,
            'low' => (float) $low,
            'close' => (float) $close,
            'volume' => is_numeric($volume) ? (float) $volume : 0,
        ];
    }

    private function first(array $record, array $keys): mixed
    {
        foreach ($keys as $key) {
            if (Arr::has($record, $key)) {
                return Arr::get($record, $key);
            }
        }

        return null;
    }

    private function cleanSymbol(?string $pair): string
    {
        return strtoupper((string) preg_replace('/[^A-Z0-9]/i', '', (string) $pair));
    }

    private function isBinanceSymbol(string $symbol): bool
    {
        return (bool) preg_match('/^[A-Z0-9]{5,20}$/', $symbol)
            && (str_ends_with($symbol, 'USDT') || str_ends_with($symbol, 'USDC') || str_ends_with($symbol, 'BTC') || str_ends_with($symbol, 'ETH'));
    }
}
