<?php

namespace Tests\Feature;

use App\Models\FinancialJuiceNews;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class NewsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        config(['services.news.alpha_vantage_key' => null]);
    }

    public function test_guest_is_redirected_from_news(): void
    {
        $this->get('/news')->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_read_live_news(): void
    {
        Http::fake(['news.google.com/*' => Http::response($this->feed(), 200)]);

        $this->actingAs(User::factory()->create())
            ->get('/news')
            ->assertOk()
            ->assertSee('Market News')
            ->assertSee('Markets rally after policy decision')
            ->assertSee('Example Finance')
            ->assertSee('bullish')
            ->assertSee('Estimated from the headline');
    }

    public function test_news_search_filters_articles(): void
    {
        Http::fake(['news.google.com/*' => Http::response($this->feed(), 200)]);

        $this->actingAs(User::factory()->create())
            ->get('/news?q=bitcoin')
            ->assertOk()
            ->assertDontSee('Markets rally after policy decision')
            ->assertSee('No headlines found');
    }

    public function test_alpha_vantage_news_includes_sentiment_and_tickers(): void
    {
        config(['services.news.alpha_vantage_key' => 'test-key']);
        Http::fake(['www.alphavantage.co/*' => Http::response([
            'feed' => [[
                'title' => 'Bitcoin gains as institutional demand rises',
                'url' => 'https://example.com/bitcoin-gains',
                'summary' => 'Institutional demand supported the latest move.',
                'source' => 'Example Markets',
                'time_published' => '20260720T083000',
                'overall_sentiment_score' => 0.42,
                'overall_sentiment_label' => 'Bullish',
                'ticker_sentiment' => [['ticker' => 'CRYPTO:BTC', 'relevance_score' => '0.95']],
            ]],
        ], 200)]);

        $this->actingAs(User::factory()->create())
            ->get('/news?category=crypto')
            ->assertOk()
            ->assertSee('Bitcoin gains as institutional demand rises')
            ->assertSee('bullish')
            ->assertSee('CRYPTO:BTC');

        Http::assertSent(fn ($request) => str_starts_with($request->url(), 'https://www.alphavantage.co/query?')
            && str_contains($request->url(), 'function=NEWS_SENTIMENT')
            && str_contains($request->url(), 'topics=blockchain'));
    }

    public function test_news_filters_can_be_loaded_as_ajax_json(): void
    {
        Http::fake(['news.google.com/*' => Http::response($this->feed(), 200)]);

        $this->actingAs(User::factory()->create())
            ->getJson('/news?category=markets')
            ->assertOk()
            ->assertJsonPath('category', 'markets')
            ->assertJsonPath('total', 1)
            ->assertJsonFragment(['query' => ''])
            ->assertJsonStructure(['html', 'total', 'category', 'query', 'error']);
    }

    public function test_financial_juice_headlines_are_merged_and_category_filtered(): void
    {
        config(['services.news.financial_juice_key' => 'test-key']);
        Http::fake(['news.google.com/*' => Http::response($this->feed(), 200)]);

        FinancialJuiceNews::query()->create([
            'external_id' => 4501234,
            'title' => "ECB's Lagarde: Rate path remains data dependent",
            'description' => 'The central bank is watching incoming inflation data.',
            'url' => 'https://www.financialjuice.com/news/example',
            'labels' => ['ECB', 'EUR'],
            'published_at' => now(),
        ]);

        $this->actingAs(User::factory()->create())
            ->get('/news?category=forex')
            ->assertOk()
            ->assertSee('ECB&#039;s Lagarde: Rate path remains data dependent', false)
            ->assertSee('FinancialJuice')
            ->assertSee('EUR');
    }

    private function feed(): string
    {
        return <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0"><channel><title>Market News</title><item>
<title>Markets rally after policy decision</title>
<link>https://example.com/markets-rally</link>
<description>Investors welcomed the latest policy announcement.</description>
<pubDate>Mon, 20 Jul 2026 08:00:00 GMT</pubDate>
<source url="https://example.com">Example Finance</source>
</item></channel></rss>
XML;
    }
}
