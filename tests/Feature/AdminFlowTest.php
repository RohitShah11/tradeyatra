<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\ContactMessage;
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
}
