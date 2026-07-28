<?php

namespace Tests\Feature;

use App\Models\DeltaAccount;
use App\Models\SharkAccount;
use App\Models\SyncLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MarketApiResponsesTest extends TestCase
{
    use RefreshDatabase;

    public function test_market_page_displays_shark_and_delta_api_responses(): void
    {
        $user = User::factory()->create();
        $sharkAccount = SharkAccount::create([
            'user_id' => $user->id,
            'name' => 'Shark',
            'base_url' => 'https://api.sharkexchange.in',
            'public_base_url' => 'https://api.sharkexchange.in',
            'default_symbol' => 'BTCINR',
            'margin_asset' => 'INR',
            'is_active' => true,
        ]);
        $deltaAccount = DeltaAccount::create([
            'user_id' => $user->id,
            'name' => 'Delta',
            'api_key' => 'delta-key',
            'api_secret' => 'delta-secret',
            'base_url' => 'https://api.india.delta.exchange',
            'is_active' => true,
        ]);

        SyncLog::create([
            'user_id' => $user->id,
            'shark_account_id' => $sharkAccount->id,
            'status' => 'success',
            'imported_count' => 1,
            'raw_payload' => ['trade_history' => ['symbol' => 'ETHINR', 'realizedPnl' => '250000']],
        ]);
        SyncLog::create([
            'user_id' => $user->id,
            'delta_account_id' => $deltaAccount->id,
            'status' => 'success',
            'imported_count' => 2,
            'raw_payload' => ['wallet_transactions' => ['result' => [['uuid' => 'delta-raw-501']]]],
        ]);
        Http::fake();

        $this->actingAs($user)
            ->get(route('shark.market'))
            ->assertOk()
            ->assertSee('SharkExchange response')
            ->assertSee('Delta Exchange response')
            ->assertSee('ETHINR')
            ->assertSee('250000')
            ->assertSee('delta-raw-501');

        Http::assertNothingSent();
    }

    public function test_market_page_explains_when_no_saved_sync_responses_exist(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('shark.market'))
            ->assertOk()
            ->assertSee('No SharkExchange API response has been saved')
            ->assertSee('No Delta Exchange API response has been saved');
    }
}
