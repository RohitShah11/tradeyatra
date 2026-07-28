<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BrokerSyncCenterContentTest extends TestCase
{
    use RefreshDatabase;

    public function test_sync_centers_explain_five_minute_auto_sync_without_repeating_setup_guide(): void
    {
        $user = User::factory()->create();

        foreach (['shark.sync', 'delta.sync'] as $route) {
            $this->actingAs($user)
                ->get(route($route))
                ->assertOk()
                ->assertSee('Automatic sync every 5 minutes')
                ->assertSee('Setup incomplete')
                ->assertSee('Connection guide')
                ->assertDontSee('Before the first sync')
                ->assertDontSee('Run Sync Now once')
                ->assertDontSee('Server IP whitelist');
        }
    }
}
