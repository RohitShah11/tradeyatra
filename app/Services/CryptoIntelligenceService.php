<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Throwable;

class CryptoIntelligenceService
{
    public function snapshot(string $symbol): array
    {
        $symbol = in_array($symbol, ['BTC', 'ETH'], true) ? $symbol : 'BTC';

        return Cache::remember("crypto-intelligence:v1:{$symbol}", now()->addMinutes(2), function () use ($symbol) {
            $venues = [];
            $errors = [];

            foreach (['Binance', 'Bybit', 'OKX'] as $exchange) {
                try {
                    $venues[] = match ($exchange) {
                        'Binance' => $this->binance($symbol),
                        'Bybit' => $this->bybit($symbol),
                        'OKX' => $this->okx($symbol),
                    };
                } catch (Throwable $exception) {
                    report($exception);
                    $errors[] = "{$exchange} data is temporarily unavailable.";
                }
            }

            $collection = collect($venues);

            return [
                'venues' => $venues,
                'errors' => $errors,
                'summary' => [
                    'price' => $collection->avg('price') ?: null,
                    'change_24h' => $collection->avg('change_24h'),
                    'open_interest_usd' => $collection->sum('open_interest_usd'),
                    'funding_rate' => $collection->avg('funding_rate'),
                    'volume_24h_usd' => $collection->sum('volume_24h_usd'),
                    'venues_online' => $collection->count(),
                ],
                'updated_at' => now()->toIso8601String(),
            ];
        });
    }

    private function binance(string $symbol): array
    {
        $pair = $symbol.'USDT';
        $ticker = $this->get('https://fapi.binance.com/fapi/v1/ticker/24hr', ['symbol' => $pair]);
        $premium = $this->get('https://fapi.binance.com/fapi/v1/premiumIndex', ['symbol' => $pair]);
        $interest = $this->get('https://fapi.binance.com/fapi/v1/openInterest', ['symbol' => $pair]);
        $price = (float) ($ticker['lastPrice'] ?? $premium['markPrice'] ?? 0);

        return $this->venue('Binance', $price, (float) ($ticker['priceChangePercent'] ?? 0), (float) ($premium['lastFundingRate'] ?? 0), (float) ($interest['openInterest'] ?? 0) * $price, (float) ($ticker['quoteVolume'] ?? 0), $premium['nextFundingTime'] ?? null);
    }

    private function bybit(string $symbol): array
    {
        $payload = $this->get('https://api.bybit.com/v5/market/tickers', ['category' => 'linear', 'symbol' => $symbol.'USDT']);
        $ticker = $payload['result']['list'][0] ?? [];
        $price = (float) ($ticker['lastPrice'] ?? 0);

        return $this->venue('Bybit', $price, (float) ($ticker['price24hPcnt'] ?? 0) * 100, (float) ($ticker['fundingRate'] ?? 0), (float) ($ticker['openInterest'] ?? 0) * $price, (float) ($ticker['turnover24h'] ?? 0), $ticker['nextFundingTime'] ?? null);
    }

    private function okx(string $symbol): array
    {
        $pair = $symbol.'-USDT-SWAP';
        $ticker = $this->get('https://www.okx.com/api/v5/market/ticker', ['instId' => $pair])['data'][0] ?? [];
        $funding = $this->get('https://www.okx.com/api/v5/public/funding-rate', ['instId' => $pair])['data'][0] ?? [];
        $interest = $this->get('https://www.okx.com/api/v5/public/open-interest', ['instType' => 'SWAP', 'instId' => $pair])['data'][0] ?? [];
        $price = (float) ($ticker['last'] ?? 0);
        $change = ($ticker['open24h'] ?? 0) > 0 ? (($price / (float) $ticker['open24h']) - 1) * 100 : 0;

        return $this->venue('OKX', $price, $change, (float) ($funding['fundingRate'] ?? 0), (float) ($interest['oiUsd'] ?? 0), (float) ($ticker['volCcy24h'] ?? 0) * $price, $funding['nextFundingTime'] ?? null);
    }

    private function venue(string $exchange, float $price, float $change, float $funding, float $interest, float $volume, string|int|null $nextFunding): array
    {
        return ['exchange' => $exchange, 'price' => $price, 'change_24h' => $change, 'funding_rate' => $funding, 'open_interest_usd' => $interest, 'volume_24h_usd' => $volume, 'next_funding_at' => $this->milliseconds($nextFunding)];
    }

    private function get(string $url, array $query): array
    {
        return Http::acceptJson()->timeout(7)->retry(1, 150)->get($url, $query)->throw()->json();
    }

    private function milliseconds(string|int|null $value): ?string
    {
        return $value && is_numeric($value) ? now()->setTimestamp((int) floor(((int) $value) / 1000))->toIso8601String() : null;
    }
}
