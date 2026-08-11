<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupportTicketTest extends TestCase
{
    use RefreshDatabase;

    public function test_support_workspace_hides_exchange_sync_and_enables_ajax_navigation(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('support.index'))
            ->assertOk()
            ->assertDontSee('Sync exchanges')
            ->assertSee('id="supportApp"', false)
            ->assertSee('window.supportAjaxReady', false);
    }

    public function test_authenticated_workspace_renders_chat_modal_launcher(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('id="chatModal"', false)
            ->assertSee('data-chat-open', false)
            ->assertSee('tradeYatraMessageNotifications', false);
    }

    public function test_user_can_poll_their_unread_direct_message_count(): void
    {
        $user = User::factory()->create();
        SupportTicket::query()->create([
            'ticket_number' => 'TY-UNREAD-CHAT', 'user_id' => $user->id, 'subject' => 'Direct chat',
            'category' => 'chat', 'priority' => 'normal', 'status' => 'waiting_on_user',
            'user_unread_count' => 3, 'last_replied_at' => now(),
        ]);

        $this->actingAs($user)->getJson(route('messages.unread-count'))
            ->assertOk()
            ->assertExactJson(['count' => 3]);
    }

    public function test_authenticated_user_can_create_and_reply_to_a_ticket(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('support.store'), [
            'subject' => 'Delta sync is missing fills',
            'category' => 'sync_problem',
            'priority' => 'high',
            'message' => 'My latest Delta fills are not appearing after a completed sync.',
        ])->assertRedirect();

        $ticket = SupportTicket::query()->firstOrFail();
        $this->assertSame($user->id, $ticket->user_id);
        $this->assertSame('open', $ticket->status);
        $this->assertDatabaseHas('support_messages', ['support_ticket_id' => $ticket->id, 'sender_type' => 'user']);

        $this->actingAs($user)->post(route('support.reply', $ticket), [
            'message' => 'The affected symbol is BTCUSD and the sync completed today.',
        ])->assertSessionHas('success');

        $this->assertSame('waiting_on_support', $ticket->fresh()->status);
        $this->assertSame(2, $ticket->fresh()->admin_unread_count);
    }

    public function test_user_cannot_view_another_users_ticket(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $ticket = SupportTicket::query()->create([
            'ticket_number' => 'TY-TEST-PRIVATE',
            'user_id' => $owner->id,
            'subject' => 'Private account issue',
            'category' => 'account',
            'priority' => 'normal',
            'status' => 'open',
            'last_replied_at' => now(),
        ]);

        $this->actingAs($otherUser)->get(route('support.show', $ticket))->assertNotFound();
    }

    public function test_admin_can_reply_and_manage_ticket_workflow(): void
    {
        $user = User::factory()->create();
        $admin = Admin::query()->create(['name' => 'Support Admin', 'email' => 'support@example.com', 'password' => 'password']);
        $ticket = SupportTicket::query()->create([
            'ticket_number' => 'TY-TEST-ADMIN',
            'user_id' => $user->id,
            'subject' => 'Broker connection failed',
            'category' => 'broker_connection',
            'priority' => 'high',
            'status' => 'open',
            'last_replied_at' => now(),
        ]);
        $ticket->messages()->create(['sender_type' => 'user', 'sender_id' => $user->id, 'body' => 'The connection test fails.']);

        $this->actingAs($admin, 'admin')->post(route('admin.support.reply', $ticket), [
            'message' => 'Please confirm the API key has read-only trade permissions.',
        ])->assertSessionHas('success');

        $ticket->refresh();
        $this->assertSame('waiting_on_user', $ticket->status);
        $this->assertSame(1, $ticket->user_unread_count);
        $this->assertSame($admin->id, $ticket->assigned_admin_id);

        $this->actingAs($admin, 'admin')->patch(route('admin.support.update', $ticket), [
            'status' => 'resolved',
            'priority' => 'normal',
            'admin_notes' => 'Resolved after correcting permissions.',
        ])->assertSessionHas('success');

        $this->assertSame('resolved', $ticket->fresh()->status);
        $this->assertNotNull($ticket->fresh()->resolved_at);
    }

    public function test_admin_can_start_a_conversation_from_a_user(): void
    {
        $user = User::factory()->create(['name' => 'Chat User']);
        $admin = Admin::query()->create(['name' => 'Support Admin', 'email' => 'support@example.com', 'password' => 'password']);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.users.chat', $user))
            ->assertOk()
            ->assertSee('No messages yet')
            ->assertSee($user->email);

        $response = $this->actingAs($admin, 'admin')->post(route('admin.users.chat.store', $user), [
            'message' => 'Hello, we are following up about the sync problem you reported.',
        ]);

        $ticket = SupportTicket::query()->firstOrFail();
        $response->assertRedirect(route('admin.users.chat', $user));
        $this->assertSame($user->id, $ticket->user_id);
        $this->assertSame($admin->id, $ticket->assigned_admin_id);
        $this->assertSame('waiting_on_user', $ticket->status);
        $this->assertSame('chat', $ticket->category);
        $this->assertSame(1, $ticket->user_unread_count);
        $this->assertDatabaseHas('support_messages', [
            'support_ticket_id' => $ticket->id,
            'sender_type' => 'admin',
            'sender_id' => $admin->id,
        ]);
    }

    public function test_admin_chat_button_opens_an_existing_direct_conversation(): void
    {
        $user = User::factory()->create();
        $admin = Admin::query()->create(['name' => 'Support Admin', 'email' => 'support@example.com', 'password' => 'password']);
        $ticket = SupportTicket::query()->create([
            'ticket_number' => 'TY-TEST-ACTIVE', 'user_id' => $user->id, 'subject' => 'Existing conversation',
            'category' => 'chat', 'priority' => 'normal', 'status' => 'waiting_on_support', 'last_replied_at' => now(),
        ]);
        $ticket->messages()->create(['sender_type' => 'user', 'sender_id' => $user->id, 'body' => 'Can you help me?']);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.users.chat', $user))
            ->assertOk()
            ->assertSee('Can you help me?');
    }

    public function test_user_can_start_and_continue_a_direct_chat(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('messages.show'))
            ->assertOk()
            ->assertSee('How can we help?')
            ->assertDontSee('Sync exchanges');
        $this->actingAs($user)->post(route('messages.store'), ['message' => 'I need help with my account.'])
            ->assertRedirect(route('messages.show'));

        $ticket = SupportTicket::query()->firstOrFail();
        $this->assertSame('chat', $ticket->category);
        $this->assertSame('waiting_on_support', $ticket->status);
        $this->assertSame(1, $ticket->admin_unread_count);

        $this->actingAs($user)->get(route('messages.show'))->assertOk()->assertSee('I need help with my account.');

        $this->actingAs($user)
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->post(route('support.reply', $ticket), ['message' => 'This is another chat message.'])
            ->assertRedirect(route('messages.show'));
    }

    public function test_opening_conversation_clears_the_correct_unread_counter(): void
    {
        $user = User::factory()->create();
        $admin = Admin::query()->create(['name' => 'Support Admin', 'email' => 'support@example.com', 'password' => 'password']);
        $ticket = SupportTicket::query()->create([
            'ticket_number' => 'TY-TEST-UNREAD', 'user_id' => $user->id, 'subject' => 'Unread reply',
            'category' => 'other', 'priority' => 'normal', 'status' => 'waiting_on_user',
            'user_unread_count' => 2, 'admin_unread_count' => 3, 'last_replied_at' => now(),
        ]);

        $this->actingAs($user)->get(route('support.show', $ticket))->assertOk();
        $this->assertSame(0, $ticket->fresh()->user_unread_count);
        $this->assertSame(3, $ticket->fresh()->admin_unread_count);

        $this->actingAs($admin, 'admin')->get(route('admin.support.show', $ticket))->assertOk();
        $this->assertSame(0, $ticket->fresh()->admin_unread_count);
    }
}
