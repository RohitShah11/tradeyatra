<?php

namespace Tests\Feature;

use App\Models\DailyPlan;
use App\Models\SyncLog;
use App\Models\Trade;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login_from_dashboard(): void
    {
        $response = $this->get('/dashboard');

        $response->assertRedirect(route('login'));
    }

    public function test_home_page_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_authenticated_user_can_open_homepage_anchor_sections(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('home'))
            ->assertOk()
            ->assertSee('id="faq"', false)
            ->assertSee('id="contact"', false);
    }

    public function test_login_page_returns_a_successful_response(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_authenticated_user_can_open_analysis_page(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/analysis');

        $response->assertStatus(200);
    }

    public function test_authenticated_user_can_open_calendar_page(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/calendar');

        $response->assertStatus(200);
    }

    public function test_authenticated_sidebar_uses_livewire_navigation(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('wire:navigate.hover', false)
            ->assertSee('Livewire Scripts', false)
            ->assertSee('window.tradeYatraNavigationController', false);
    }

    public function test_dashboard_balance_can_use_latest_wallet_snapshot(): void
    {
        $user = User::factory()->create();
        SyncLog::create([
            'user_id' => $user->id,
            'status' => 'success',
            'wallet_snapshot' => ['walletBalance' => 12345.67, 'marginAsset' => 'INR'],
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('INR 12,345.67');
    }

    public function test_daily_plan_can_be_saved_with_ajax(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/dashboard/daily-plan', [
            'content' => 'Risk no more than one percent per trade.',
        ]);

        $response
            ->assertOk()
            ->assertJson(['message' => "Today's trading plan has been saved."]);

        $this->assertDatabaseHas('daily_plans', [
            'user_id' => $user->id,
            'content' => 'Risk no more than one percent per trade.',
        ]);
    }

    public function test_user_can_load_and_update_a_previous_daily_plan(): void
    {
        $user = User::factory()->create();
        $date = now()->subDay()->toDateString();
        DailyPlan::query()->create([
            'user_id' => $user->id,
            'plan_date' => $date,
            'content' => 'Wait for the opening range.',
        ]);

        $this->actingAs($user)
            ->getJson(route('dashboard.daily-plan.show', ['date' => $date]))
            ->assertOk()
            ->assertJson([
                'date' => $date,
                'content' => 'Wait for the opening range.',
                'has_plan' => true,
            ]);

        $this->actingAs($user)
            ->postJson(route('dashboard.daily-plan.save'), [
                'plan_date' => $date,
                'content' => 'Wait for the opening range and risk 1%.',
            ])
            ->assertOk();

        $this->assertSame('Wait for the opening range and risk 1%.', DailyPlan::query()->where('user_id', $user->id)->whereDate('plan_date', $date)->firstOrFail()->content);
    }

    public function test_daily_plan_cannot_be_saved_for_a_future_date(): void
    {
        $this->actingAs(User::factory()->create())
            ->postJson(route('dashboard.daily-plan.save'), [
                'plan_date' => now()->addDay()->toDateString(),
                'content' => 'Future plan.',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('plan_date');
    }

    public function test_trade_with_seconds_can_be_edited_and_updated(): void
    {
        $user = User::factory()->create();
        $trade = Trade::create([
            'user_id' => $user->id,
            'date' => '2026-07-20',
            'time' => '09:30:00',
            'pair' => 'BTCUSDT',
            'asset_class' => 'Crypto',
            'market_segment' => 'Futures',
            'currency' => 'USD',
            'trade_type' => 'Long',
            'status' => 'Closed',
        ]);

        $this->actingAs($user)
            ->get(route('trades.edit', $trade))
            ->assertOk()
            ->assertSee('value="09:30"', false);

        $this->actingAs($user)
            ->patch(route('trades.update', $trade), [
                'date' => '2026-07-20',
                'time' => '09:30:00',
                'pair' => 'BTCUSDT',
                'asset_class' => 'Crypto',
                'market_segment' => 'Futures',
                'currency' => 'USD',
                'trade_type' => 'Long',
                'status' => 'Closed',
                'broker' => '',
            ])
            ->assertRedirect(route('trades.index'));

        $this->assertSame('09:30', $trade->fresh()->time);
    }
}
