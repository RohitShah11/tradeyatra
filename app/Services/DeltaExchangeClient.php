<?php

namespace App\Services;

use App\Models\DeltaAccount;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class DeltaExchangeClient
{
    public function __construct(private readonly DeltaAccount $account)
    {
        if (rtrim((string) $account->base_url, '/') !== 'https://api.india.delta.exchange') {
            throw new RuntimeException('The configured Delta Exchange API host is not allowed.');
        }
    }

    public function profile(): array
    {
        return $this->get('/v2/profile');
    }

    public function fills(array $params = []): array
    {
        return $this->get('/v2/fills', $params);
    }

    public function orderHistory(array $params = []): array
    {
        return $this->get('/v2/orders/history', $params);
    }

    public function positions(array $params = []): array
    {
        return $this->get('/v2/positions/margined', $params);
    }

    public function walletBalances(): array
    {
        return $this->get('/v2/wallet/balances');
    }

    public function walletTransactions(array $params = []): array
    {
        return $this->get('/v2/wallet/transactions', $params);
    }

    public function allWalletTransactions(array $params = [], int $maxPages = 20): array
    {
        $records = [];
        $lastMeta = [];
        for ($page = 0; $page < $maxPages; $page++) {
            $payload = $this->walletTransactions($params);
            $records = array_merge($records, $payload['result'] ?? []);
            $lastMeta = $payload['meta'] ?? [];
            $after = $lastMeta['after'] ?? null;
            if (! $after || ($params['after'] ?? null) === $after) {
                break;
            }
            $params['after'] = $after;
            unset($params['before']);
        }

        return ['success' => true, 'result' => $records, 'meta' => $lastMeta];
    }

    private function get(string $path, array $params = []): array
    {
        $query = http_build_query($params, '', '&', PHP_QUERY_RFC3986);
        $queryWithPrefix = $query === '' ? '' : '?'.$query;
        $timestamp = (string) time();
        $signature = hash_hmac('sha256', 'GET'.$timestamp.$path.$queryWithPrefix, $this->account->api_secret);
        $response = Http::withHeaders([
            'api-key' => $this->account->api_key,
            'signature' => $signature,
            'timestamp' => $timestamp,
            'User-Agent' => 'TradeYatra-Laravel/1.0',
        ])->acceptJson()->timeout(20)->get(rtrim($this->account->base_url, '/').$path, $params);

        return $this->decode($response);
    }

    private function decode(Response $response): array
    {
        if ($response->failed()) {
            $message = $response->json('error.message') ?? $response->json('message') ?? $response->body();
            throw new RuntimeException($message ?: 'Delta Exchange request failed.');
        }
        $json = $response->json();
        if (! is_array($json) || (($json['success'] ?? true) === false)) {
            throw new RuntimeException($json['error']['message'] ?? 'Delta Exchange returned an invalid response.');
        }

        return $json;
    }
}
