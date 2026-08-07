<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CryptoIntelligenceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        Http::fake(fn (Request $request) => $this->exchangeResponse($request->url()));
    }

    public function test_guest_is_redirected_from_crypto_intelligence(): void
    {
        $this->get('/crypto-intelligence')->assertRedirect(route('login'));
    }

    public function test_old_crypto_intelligence_page_redirects_to_the_news_crypto_pulse(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/crypto-intelligence?symbol=BTC')
            ->assertRedirect(route('news.index', ['symbol' => 'BTC']).'#crypto-pulse');
    }

    public function test_crypto_pulse_is_available_inside_market_intelligence(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/news?symbol=ETH')
            ->assertOk()
            ->assertSee('Market News &amp; Intelligence', false)
            ->assertSee('Crypto market pulse')
            ->assertSee('Binance')
            ->assertSee('Bybit')
            ->assertSee('OKX')
            ->assertSee('$60,000.00');
    }

    private function exchangeResponse(string $url)
    {
        return match (true) {
            str_contains($url, 'binance.com/fapi/v1/ticker') => Http::response(['lastPrice' => '60000', 'priceChangePercent' => '2.5', 'quoteVolume' => '1000000000']),
            str_contains($url, 'binance.com/fapi/v1/premiumIndex') => Http::response(['markPrice' => '60000', 'lastFundingRate' => '0.0001', 'nextFundingTime' => '1784548800000']),
            str_contains($url, 'binance.com/fapi/v1/openInterest') => Http::response(['openInterest' => '10000']),
            str_contains($url, 'bybit.com') => Http::response(['result' => ['list' => [[
                'lastPrice' => '60000', 'price24hPcnt' => '0.025', 'fundingRate' => '0.0001', 'openInterest' => '10000', 'turnover24h' => '1000000000', 'nextFundingTime' => '1784548800000',
            ]]]]),
            str_contains($url, '/market/ticker') => Http::response(['data' => [['last' => '60000', 'open24h' => '58536.585', 'volCcy24h' => '16666.6667']]]),
            str_contains($url, '/funding-rate') => Http::response(['data' => [['fundingRate' => '0.0001', 'nextFundingTime' => '1784548800000']]]),
            str_contains($url, '/open-interest') => Http::response(['data' => [['oiUsd' => '600000000']]]),
            default => Http::response([], 404),
        };
    }
}
