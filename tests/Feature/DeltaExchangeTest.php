<?php

namespace Tests\Feature;

use App\Models\DeltaAccount;
use App\Models\Trade;
use App\Models\User;
use App\Services\DeltaExchangeClient;
use App\Services\DeltaTradeHistorySyncService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class DeltaExchangeTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_save_encrypted_delta_credentials(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->post(route('delta.settings.save'), [
            'name' => 'My Delta', 'api_key' => 'test-key', 'api_secret' => 'test-secret',
            'base_url' => 'https://api.india.delta.exchange', 'auto_sync_enabled' => '1',
        ])->assertRedirect(route('delta.settings'));

        $account = DeltaAccount::firstOrFail();
        $this->assertSame('test-key', $account->api_key);
        $this->assertSame('test-secret', $account->api_secret);
        $this->assertStringNotContainsString('test-secret', (string) $account->getRawOriginal('api_secret'));
    }

    public function test_client_signs_the_exact_delta_query_string(): void
    {
        Http::fake(['*' => Http::response(['success' => true, 'result' => []])]);
        $account = DeltaAccount::create([
            'user_id' => User::factory()->create()->id, 'api_key' => 'key', 'api_secret' => 'secret',
            'base_url' => 'https://api.india.delta.exchange',
        ]);
        (new DeltaExchangeClient($account))->fills(['page_size' => 50, 'product_ids' => '27,139']);

        Http::assertSent(function (Request $request) {
            $timestamp = $request->header('timestamp')[0];
            $expected = hash_hmac('sha256', 'GET'.$timestamp.'/v2/fills?page_size=50&product_ids=27%2C139', 'secret');
            return $request->header('api-key')[0] === 'key' && $request->header('signature')[0] === $expected;
        });
    }

    public function test_sync_imports_realized_wallet_cashflow_only_once_and_not_fills(): void
    {
        Http::fake(function (Request $request) {
            if (str_contains($request->url(), '/v2/fills')) {
                return Http::response(['success' => true, 'result' => [[
                    'id' => 991, 'order_id' => 88, 'product_id' => 27, 'product_symbol' => 'BTCUSD',
                    'side' => 'buy', 'size' => 2, 'price' => '65000', 'commission' => '1.25',
                    'created_at' => '2026-07-18T10:00:00Z',
                ]]]);
            }
            if (str_contains($request->url(), '/v2/wallet/transactions')) {
                return Http::response(['success' => true, 'result' => [[
                    'uuid' => 'txn-501', 'amount' => '245.75', 'balance' => '10245.75',
                    'transaction_type' => 'cashflow', 'product_id' => 27, 'asset_symbol' => 'INR',
                    'created_at' => '2026-07-18T10:00:01Z', 'meta_data' => ['entry_price' => '64000', 'exit_price' => '64200', 'position_size' => 2, 'product_symbol' => 'BTCUSD'],
                ], [
                    'id' => 502, 'amount' => '5000', 'transaction_type' => 'deposit',
                    'asset_symbol' => 'INR', 'created_at' => '2026-07-18T10:00:02Z',
                ], [
                    'id' => 503, 'amount' => '20', 'transaction_type' => 'cashflow',
                    'asset_symbol' => 'INR', 'created_at' => '2026-07-18T10:00:03Z',
                ], [
                    'uuid' => 'option-504', 'amount' => '12', 'transaction_type' => 'cashflow',
                    'product_id' => 142648, 'asset_symbol' => 'USD', 'created_at' => '2026-07-18T10:00:04Z',
                    'meta_data' => ['product_symbol' => 'C-BTC-63800-190726', 'position_size' => 0],
                ]]]);
            }
            return Http::response(['success' => true, 'result' => []]);
        });
        $account = DeltaAccount::create([
            'user_id' => User::factory()->create()->id, 'api_key' => 'key', 'api_secret' => 'secret',
            'base_url' => 'https://api.india.delta.exchange',
        ]);
        $service = app(DeltaTradeHistorySyncService::class);
        $this->assertSame(1, $service->sync($account)->imported_count);
        $this->assertSame(0, $service->sync($account)->imported_count);
        $this->assertSame(1, Trade::where('exchange', 'delta')->count());
        $this->assertSame('Closed', Trade::first()->status);
        $this->assertSame(245.75, (float) Trade::first()->profit);
        $this->assertSame('wallet-txn-501', Trade::first()->external_trade_id);
        $this->assertSame(64000.0, (float) Trade::first()->entry_price);
        $this->assertDatabaseMissing('trades', ['external_trade_id' => 'wallet-option-504']);
    }

    public function test_delta_and_shark_trade_lists_are_kept_separate(): void
    {
        $user = User::factory()->create();
        $base = [
            'user_id' => $user->id, 'date' => '2026-07-18', 'pair' => 'BTCUSD',
            'trade_type' => 'Long', 'status' => 'Closed', 'profit' => 10, 'loss' => 0,
        ];
        Trade::create($base + ['broker' => 'Delta Exchange', 'exchange' => 'delta', 'external_trade_id' => 'delta-1', 'currency' => 'USD']);
        Trade::create(array_merge($base, ['pair' => 'ETHINR', 'broker' => 'SharkExchange', 'shark_trade_id' => 'shark-1']));

        $this->actingAs($user)->get(route('trades.delta'))
            ->assertOk()->assertSee('Delta Exchange Trades')->assertSee('BTCUSD')->assertSee('USD 10.00')->assertDontSee('ETHINR');
        $this->actingAs($user)->get(route('trades.shark'))
            ->assertOk()->assertSee('Shark Exchange Trades')->assertSee('ETHINR')->assertDontSee('BTCUSD');

        $this->actingAs($user)->get(route('trades.calendar', ['broker' => 'Delta Exchange', 'calendar_month' => '2026-07']))
            ->assertOk()->assertSee('BTCUSD')->assertDontSee('ETHINR');
        $this->actingAs($user)->get(route('trades.analysis', ['broker' => 'Delta Exchange']))
            ->assertOk()->assertSee('USD 10.00')->assertSee('Delta Exchange')
            ->assertSee('analysisAjaxLoader')->assertSee('analysisPageContent');
    }
}
