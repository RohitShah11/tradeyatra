<?php

namespace App\Services;

use App\Models\SharkAccount;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class SharkExchangeClient
{
    public function __construct(private readonly SharkAccount $account)
    {
        if (rtrim((string) $account->base_url, '/') !== 'https://api.sharkexchange.in'
            || rtrim((string) $account->public_base_url, '/') !== 'https://api.sharkexchange.in') {
            throw new RuntimeException('The configured Shark Exchange API host is not allowed.');
        }
    }

    public function openOrders(array $params = []): array
    {
        return $this->get('/v1/order/open-orders', $params);
    }

    public function orderHistory(array $params = []): array
    {
        return $this->get('/v1/order/order-history', $params);
    }

    public function positions(string $status = 'OPEN', array $params = []): array
    {
        return $this->get('/v1/positions/'.strtoupper($status), $params);
    }

    public function tradeHistory(array $params = []): array
    {
        return $this->get('/v1/user-data/trade-history', $params);
    }

    public function transactionHistory(array $params = []): array
    {
        return $this->get('/v1/user-data/transaction-history', $params);
    }

    public function futuresWallet(array $params = []): array
    {
        return $this->get('/v1/wallet/futures-wallet/details', $params);
    }

    public function ticker(string $symbol): array
    {
        if (! preg_match('/^[A-Z0-9_-]{2,20}$/', strtoupper($symbol))) {
            throw new RuntimeException('The requested market symbol is invalid.');
        }

        return $this->get('/v1/market/ticker24Hr/'.strtoupper($symbol));
    }

    public function exchangeInfo(array $params = []): array
    {
        return $this->get('/v1/exchange/exchangeInfo', $params);
    }

    public function klines(array $params = []): array
    {
        return $this->post('/v1/market/klines?priceType=MARK_PRICE', $params);
    }

    private function get(string $endpoint, array $params = []): array
    {
        $isPublicEndpoint = $this->isPublicEndpoint($endpoint);
        $baseUrl = $isPublicEndpoint ? $this->account->public_base_url : $this->account->base_url;

        if (! $isPublicEndpoint) {
            $params['timestamp'] = $this->timestamp();
        }

        $query = http_build_query($params, '', '&', PHP_QUERY_RFC3986);
        $headers = $isPublicEndpoint ? [] : $this->signedHeaders($query);
        $url = rtrim($baseUrl, '/').$endpoint;

        return $this->decode(Http::withHeaders($headers)->acceptJson()->get($url, $params));
    }

    private function post(string $endpoint, array $params = []): array
    {
        $isPublicEndpoint = $this->isPublicEndpoint($endpoint);
        $baseUrl = $isPublicEndpoint ? $this->account->public_base_url : $this->account->base_url;

        if (! $isPublicEndpoint) {
            $params['timestamp'] = $this->timestamp();
        }

        $body = json_encode($params, JSON_UNESCAPED_SLASHES);
        $headers = $isPublicEndpoint ? [] : $this->signedHeaders($body ?: '{}');

        return $this->decode(Http::withHeaders($headers)->acceptJson()->post(rtrim($baseUrl, '/').$endpoint, $params));
    }

    private function signedHeaders(string $payload): array
    {
        return [
            'api-key' => $this->account->api_key,
            'signature' => hash_hmac('sha256', $payload, $this->account->api_secret),
            'accept' => '*/*',
        ];
    }

    private function isPublicEndpoint(string $endpoint): bool
    {
        return Str::startsWith($endpoint, ['/v1/market', '/v1/exchange']);
    }

    private function timestamp(): string
    {
        return (string) ((int) floor(microtime(true) * 1000));
    }

    private function decode(Response $response): array
    {
        if ($response->failed()) {
            throw new RuntimeException($response->body() ?: 'SharkExchange request failed.');
        }

        $json = $response->json();

        return is_array($json) ? $json : ['data' => $json];
    }
}
