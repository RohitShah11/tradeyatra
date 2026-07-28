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
        $this->assertEqualsWithDelta(37.68, $trade->net_pnl, 0.0001);
        $this->assertEqualsWithDelta(107.1271, (float) $trade->trading_fees, 0.0001);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('INR 37.68')
            ->assertSee('107.13')
            ->assertSee('Fees: INR 107.13');
    }
}
