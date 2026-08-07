<?php

namespace Tests\Feature;

use App\Models\SharkAccount;
use App\Models\Trade;
use App\Models\User;
use App\Services\SharkTradeHistorySyncService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SharkBrokerMatchingTest extends TestCase
{
    use RefreshDatabase;

    public function test_shark_realized_pnl_matches_broker_and_all_execution_fees_are_allocated(): void
    {
        Carbon::setTestNow('2026-07-24 12:00:00');
        $user = User::factory()->create();
        $account = SharkAccount::create([
            'user_id' => $user->id,
            'name' => 'Shark',
            'base_url' => 'https://api.sharkexchange.in',
            'public_base_url' => 'https://api.sharkexchange.in',
            'default_symbol' => 'BTCINR',
            'margin_asset' => 'INR',
            'is_active' => true,
        ]);
        $payload = [
            [
                'id' => 'entry-1',
                'positionId' => 'position-1',
                'time' => '2026-07-01T09:00:00Z',
                'symbol' => 'BTCUSDT',
                'side' => 'BUY',
                'quantity' => 1,
                'price' => 100,
                'feeInMarginAsset' => 77.95,
                'realizedProfitInMarginAsset' => 0,
            ],
            [
                'id' => 'close-1',
                'positionId' => 'position-1',
                'time' => '2026-07-23T09:00:00Z',
                'symbol' => 'BTCUSDT',
                'side' => 'SELL',
                'quantity' => 1,
                'price' => 110,
                'feeInMarginAsset' => 48.46,
                'realizedProfitInMarginAsset' => 37.68,
            ],
        ];

        app(SharkTradeHistorySyncService::class)->reconcileStoredPayload($account, $payload);

        $trade = Trade::firstOrFail();
        $this->assertSame(1, Trade::count());
        $this->assertSame('Long', $trade->trade_type);
        $this->assertSame('2026-07-01', $trade->date?->toDateString());
        $this->assertSame('09:00', $trade->time);
        $this->assertEquals(100, (float) $trade->entry_price);
        $this->assertSame('2026-07-23', $trade->exit_date?->toDateString());
        $this->assertSame('09:00', $trade->exit_time);
        $this->assertEquals(110, (float) $trade->exit_price);
        $this->assertEqualsWithDelta(37.68, $trade->net_pnl, 0.0001);
        $this->assertEqualsWithDelta(107.1271, (float) $trade->trading_fees, 0.0001);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('INR 37.68')
            ->assertSee('107.13')
            ->assertSee('Fees: INR 107.13');
    }

    public function test_shark_buy_closing_execution_is_imported_as_a_short_trade(): void
    {
        $user = User::factory()->create();
        $account = SharkAccount::create([
            'user_id' => $user->id,
            'name' => 'Shark',
            'base_url' => 'https://api.sharkexchange.in',
            'public_base_url' => 'https://api.sharkexchange.in',
            'default_symbol' => 'ETHUSDT',
            'margin_asset' => 'USDT',
            'is_active' => true,
        ]);

        app(SharkTradeHistorySyncService::class)->reconcileStoredPayload($account, [[
            'id' => 'short-close-1',
            'positionId' => 'short-position-1',
            'time' => '2026-07-23T09:00:00Z',
            'symbol' => 'ETHUSDT',
            'side' => 'BUY',
            'quantity' => 1,
            'price' => 3000,
            'realizedProfitInMarginAsset' => 25,
        ]]);

        $this->assertSame('Short', Trade::firstOrFail()->trade_type);
    }
}
