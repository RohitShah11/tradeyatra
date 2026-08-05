<?php

namespace Tests\Feature;

use App\Models\Trade;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TradeChartTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_open_a_read_only_trade_chart(): void
    {
        $this->withoutVite();
        $user = User::factory()->create(['timezone' => 'Asia/Kolkata']);
        $trade = $this->trade($user);

        $this->actingAs($user)
            ->get(route('trades.chart', $trade))
            ->assertOk()
            ->assertSee('BTCUSD Trade Chart')
            ->assertSee('Read-only market chart')
            ->assertSee('Planned SL')
            ->assertSee(route('trades.candles', $trade), false);
    }

    public function test_trade_chart_and_candles_are_private_to_the_owner(): void
    {
        $this->withoutVite();
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $trade = $this->trade($owner);

        $this->actingAs($otherUser)->get(route('trades.chart', $trade))->assertNotFound();
        $this->actingAs($otherUser)->get(route('trades.candles', [$trade, 'interval' => '15m']))->assertNotFound();
        Http::assertNothingSent();
    }

    public function test_delta_trade_candles_are_loaded_from_the_public_read_only_api(): void
    {
        $user = User::factory()->create(['timezone' => 'Asia/Kolkata']);
        $trade = $this->trade($user);
        Http::fake([
            'https://api.india.delta.exchange/v2/history/candles*' => Http::response([
                'success' => true,
                'result' => [
                    ['time' => 1785872700, 'open' => 67000, 'high' => 67200, 'low' => 66900, 'close' => 67150, 'volume' => 12.5],
                    ['time' => 1785873600, 'open' => 67150, 'high' => 67400, 'low' => 67100, 'close' => 67350, 'volume' => 18.2],
                ],
            ]),
        ]);

        $this->actingAs($user)
            ->getJson(route('trades.candles', [$trade, 'interval' => '15m']))
            ->assertOk()
            ->assertJsonPath('source', 'Delta Exchange India')
            ->assertJsonPath('symbol', 'BTCUSD')
            ->assertJsonPath('interval', '15m')
            ->assertJsonCount(2, 'candles')
            ->assertJsonPath('candles.0.close', 67150);

        Http::assertSent(fn ($request) => str_starts_with($request->url(), 'https://api.india.delta.exchange/v2/history/candles')
            && $request['symbol'] === 'BTCUSD'
            && $request['resolution'] === '15m');
    }

    public function test_trade_form_saves_the_original_plan_and_exit_timing(): void
    {
        $user = User::factory()->create();
        $trade = $this->trade($user);

        $this->actingAs($user)->put(route('trades.update', $trade), [
            'date' => '2026-08-04',
            'time' => '09:15',
            'exit_date' => '2026-08-04',
            'exit_time' => '12:45',
            'pair' => 'BTCUSD',
            'asset_class' => 'Crypto',
            'market_segment' => 'Futures',
            'currency' => 'USD',
            'trade_type' => 'Long',
            'status' => 'Closed',
            'broker' => 'Delta Exchange',
            'entry_price' => 67000,
            'planned_stop_loss' => 66500,
            'planned_take_profit' => 68000,
            'exit_price' => 67750,
            'profit' => 150,
            'loss' => 0,
            'trading_fees' => 4.5,
        ])->assertRedirect(route('trades.index'));

        $trade->refresh();
        $this->assertEquals(66500, (float) $trade->planned_stop_loss);
        $this->assertEquals(68000, (float) $trade->planned_take_profit);
        $this->assertSame('2026-08-04', $trade->exit_date?->toDateString());
        $this->assertSame('12:45', $trade->exit_time);
    }

    private function trade(User $user): Trade
    {
        return Trade::create([
            'user_id' => $user->id,
            'date' => '2026-08-04',
            'time' => '09:15',
            'exit_date' => '2026-08-04',
            'exit_time' => '12:45',
            'pair' => 'BTCUSD',
            'asset_class' => 'Crypto',
            'market_segment' => 'Futures',
            'currency' => 'USD',
            'trade_type' => 'Long',
            'status' => 'Closed',
            'broker' => 'Delta Exchange',
            'entry_price' => 67000,
            'planned_stop_loss' => 66500,
            'planned_take_profit' => 68000,
            'exit_price' => 67750,
            'quantity' => 0.2,
            'profit' => 150,
            'loss' => 0,
            'trading_fees' => 4.5,
        ]);
    }
}
