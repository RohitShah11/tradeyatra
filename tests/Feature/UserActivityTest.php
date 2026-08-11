<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\User;
use App\Models\UserActivitySession;
use App\Models\UserPageSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserActivityTest extends TestCase
{
    use RefreshDatabase;

    public function test_heartbeat_records_active_time_and_current_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->postJson(route('activity.heartbeat'), [
            'client_session' => '11111111-1111-4111-8111-111111111111', 'route' => 'trades.calendar', 'path' => '/calendar', 'visible' => true, 'idle' => false, 'elapsed' => 30,
        ])->assertOk()->assertJson(['recorded' => true, 'status' => 'active']);

        $activity = UserActivitySession::query()->firstOrFail();
        $this->assertSame($user->id, $activity->user_id);
        $this->assertSame('/calendar', $activity->current_path);
        $this->assertSame(30, $activity->active_seconds);
        $this->assertNotNull($activity->last_interacted_at);
        $this->assertDatabaseHas('user_page_sessions', [
            'user_id' => $user->id, 'path' => '/calendar', 'active_seconds' => 30,
        ]);
    }

    public function test_hidden_or_idle_time_is_not_counted_as_active(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->postJson(route('activity.heartbeat'), [
            'client_session' => '22222222-2222-4222-8222-222222222222', 'route' => 'dashboard', 'path' => '/dashboard', 'visible' => false, 'idle' => true, 'elapsed' => 30,
        ])->assertOk();

        $activity = UserActivitySession::query()->firstOrFail();
        $this->assertSame(0, $activity->active_seconds);
        $this->assertSame(30, $activity->idle_seconds);
        $this->assertSame('idle', $activity->presenceStatus());
    }

    public function test_changing_pages_closes_the_previous_page_session(): void
    {
        $user = User::factory()->create();
        $payload = ['client_session' => '33333333-3333-4333-8333-333333333333', 'visible' => true, 'idle' => false, 'elapsed' => 30];

        $this->actingAs($user);
        $this->postJson(route('activity.heartbeat'), [...$payload, 'route' => 'dashboard', 'path' => '/dashboard']);
        $this->postJson(route('activity.heartbeat'), [...$payload, 'route' => 'trades.index', 'path' => '/trades']);

        $this->assertSame(1, UserActivitySession::query()->count());
        $this->assertSame(2, UserPageSession::query()->count());
        $this->assertNotNull(UserPageSession::query()->where('path', '/dashboard')->firstOrFail()->ended_at);
        $this->assertNull(UserPageSession::query()->where('path', '/trades')->firstOrFail()->ended_at);
    }

    public function test_admin_user_screens_show_presence_and_page_usage(): void
    {
        $user = User::factory()->create(['name' => 'Active Trader']);
        $admin = Admin::query()->create(['name' => 'Admin', 'email' => 'admin@example.com', 'password' => 'password']);
        $this->actingAs($user)->postJson(route('activity.heartbeat'), [
            'client_session' => '44444444-4444-4444-8444-444444444444', 'route' => 'trades.calendar', 'path' => '/calendar', 'visible' => true, 'idle' => false, 'elapsed' => 30,
        ]);

        $this->actingAs($admin, 'admin')->get(route('admin.users.index'))
            ->assertOk()->assertSee('Active now')->assertSee('/calendar');
        $this->actingAs($admin, 'admin')->get(route('admin.users.show', $user))
            ->assertOk()->assertSee('Live activity')->assertSee('Page time')->assertSee('/calendar');
    }
}
