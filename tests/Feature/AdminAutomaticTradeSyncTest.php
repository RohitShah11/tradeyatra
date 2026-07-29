<?php

namespace Tests\Feature;

use App\Jobs\SyncDeltaAccountTradeHistory;
use App\Jobs\SyncSharkAccountTradeHistory;
use App\Models\Admin;
use App\Models\DeltaAccount;
use App\Models\PlatformSetting;
use App\Models\SharkAccount;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class AdminAutomaticTradeSyncTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_turn_global_automatic_trade_sync_off_and_on(): void
    {
        $admin = Admin::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => 'password',
            'is_active' => true,
        ]);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Automatic trade sync')
            ->assertSee('Turn automatic sync OFF');

        $this->actingAs($admin, 'admin')
            ->patch(route('admin.settings.automatic-trade-sync'), [
                'automatic_trade_sync_enabled' => '0',
            ])
            ->assertRedirect(route('admin.dashboard'));

        $this->assertFalse(PlatformSetting::automaticTradeSyncEnabled());

        $this->actingAs($admin, 'admin')
            ->patch(route('admin.settings.automatic-trade-sync'), [
                'automatic_trade_sync_enabled' => '1',
            ]);

        $this->assertTrue(PlatformSetting::automaticTradeSyncEnabled());
    }

    public function test_disabled_global_switch_prevents_scheduled_jobs_from_being_queued(): void
    {
        Bus::fake();
        $user = User::factory()->create();

        SharkAccount::create([
            'user_id' => $user->id,
            'name' => 'Shark',
            'api_key' => 'key',
            'api_secret' => 'secret',
            'is_active' => true,
            'auto_sync_enabled' => true,
        ]);

        DeltaAccount::create([
            'user_id' => $user->id,
            'name' => 'Delta',
            'api_key' => 'key',
            'api_secret' => 'secret',
            'base_url' => 'https://api.india.delta.exchange',
            'is_active' => true,
            'auto_sync_enabled' => true,
        ]);

        PlatformSetting::current()->update(['automatic_trade_sync_enabled' => false]);

        $this->artisan('shark:sync-active-accounts')->assertSuccessful();
        $this->artisan('delta:sync-active-accounts')->assertSuccessful();

        Bus::assertNotDispatched(SyncSharkAccountTradeHistory::class);
        Bus::assertNotDispatched(SyncDeltaAccountTradeHistory::class);
    }
}
