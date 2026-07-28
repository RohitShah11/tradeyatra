<?php

namespace Tests\Feature;

use App\Models\Trade;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SecurityHardeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_security_headers_are_applied(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'DENY')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
            ->assertHeader('Content-Security-Policy');
    }

    public function test_authenticated_responses_are_not_cacheable(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('dashboard'))
            ->assertOk()
            ->assertHeader('Cache-Control', 'no-store, private');
    }

    public function test_trade_screenshot_requires_ownership(): void
    {
        Storage::fake('local');
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $trade = Trade::query()->create([
            'user_id' => $owner->id,
            'date' => now()->toDateString(),
            'pair' => 'BTCUSDT',
            'asset_class' => 'Crypto',
            'market_segment' => 'Futures',
            'currency' => 'USD',
            'trade_type' => 'Long',
            'status' => 'Closed',
            'screenshot' => json_encode(['private-chart.png']),
        ]);
        Storage::disk('local')->put('trade-screenshots/private-chart.png', 'private image');

        $this->actingAs($other)
            ->get(route('trades.screenshot', [$trade, 'filename' => 'private-chart.png']))
            ->assertNotFound();

        $this->actingAs($owner)
            ->get(route('trades.screenshot', [$trade, 'filename' => 'private-chart.png']))
            ->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff');
    }

    public function test_exchange_urls_cannot_target_an_attacker_server(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('delta.settings.save'), [
                'name' => 'Delta',
                'api_key' => 'key',
                'api_secret' => 'secret',
                'base_url' => 'https://attacker.example',
            ])
            ->assertSessionHasErrors('base_url');
    }
}
