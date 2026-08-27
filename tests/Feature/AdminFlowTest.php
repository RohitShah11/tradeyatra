<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\ContactMessage;
use App\Models\Trade;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_pages_require_admin_authentication(): void
    {
        $this->get(route('admin.dashboard'))->assertRedirect(route('admin.login'));
        $this->get(route('admin.users.index'))->assertRedirect(route('admin.login'));
        $this->get(route('admin.contacts.index'))->assertRedirect(route('admin.login'));
    }

    public function test_active_admin_can_login_and_user_cannot_access_admin(): void
    {
        $admin = Admin::create([
            'name' => 'Platform Admin',
            'email' => 'admin@example.com',
            'password' => 'SecureAdmin123',
        ]);
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('admin.dashboard'))->assertRedirect(route('admin.login'));

        $this->post(route('admin.login.store'), [
            'email' => 'admin@example.com',
            'password' => 'SecureAdmin123',
        ])->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($admin, 'admin');
        $this->assertNotNull($admin->fresh()->last_login_at);
    }

    public function test_inactive_admin_cannot_login(): void
    {
        Admin::create([
            'name' => 'Inactive Admin',
            'email' => 'inactive@example.com',
            'password' => 'SecureAdmin123',
            'is_active' => false,
        ]);

        $this->post(route('admin.login.store'), [
            'email' => 'inactive@example.com',
            'password' => 'SecureAdmin123',
        ])->assertSessionHasErrors('email');

        $this->assertGuest('admin');
    }

    public function test_admin_can_review_and_resolve_contact_message(): void
    {
        $admin = Admin::create([
            'name' => 'Platform Admin',
            'email' => 'admin@example.com',
            'password' => 'SecureAdmin123',
        ]);
        $message = ContactMessage::create([
            'name' => 'Visitor',
            'email' => 'visitor@example.com',
            'subject' => 'product',
            'message' => 'I need help understanding the reports.',
        ]);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.contacts.show', $message))
            ->assertOk();
        $this->assertSame('in_progress', $message->fresh()->status);

        $this->actingAs($admin, 'admin')
            ->patch(route('admin.contacts.update', $message), [
                'status' => 'resolved',
                'admin_notes' => 'Replied by email.',
            ])->assertSessionHas('success');

        $message->refresh();
        $this->assertSame('resolved', $message->status);
        $this->assertSame($admin->id, $message->handled_by);
        $this->assertNotNull($message->handled_at);
    }

    public function test_admin_can_view_and_filter_a_users_trades(): void
    {
        $admin = Admin::create(['name' => 'Admin', 'email' => 'admin@example.com', 'password' => 'password']);
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        Trade::create([
            'user_id' => $user->id, 'date' => '2026-08-20', 'pair' => 'BTCUSDT',
            'trade_type' => 'Long', 'broker' => 'SharkExchange', 'profit' => 125, 'loss' => 0,
            'strategy' => 'Breakout', 'currency' => 'USD',
        ]);
        Trade::create([
            'user_id' => $otherUser->id, 'date' => '2026-08-21', 'pair' => 'PRIVATEPAIR',
            'trade_type' => 'Short', 'profit' => 50, 'loss' => 0,
        ]);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.users.show', [$user, 'trade_search' => 'BTC']))
            ->assertOk()
            ->assertSee('Trade history')
            ->assertSee('BTCUSDT')
            ->assertSee('USD 125.00')
            ->assertDontSee('PRIVATEPAIR');
    }
}
