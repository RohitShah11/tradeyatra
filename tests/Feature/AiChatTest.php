<?php

namespace Tests\Feature;

use App\Models\AiConversation;
use App\Models\Trade;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AiChatTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_ai_chat(): void
    {
        $this->get('/ai-chat')->assertRedirect(route('login'));
    }

    public function test_user_can_receive_free_journal_analysis(): void
    {
        $user = User::factory()->create();
        Trade::create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'pair' => 'BTCUSDT',
            'asset_class' => 'Crypto',
            'market_segment' => 'Futures',
            'currency' => 'USD',
            'trade_type' => 'Long',
            'status' => 'Closed',
            'broker' => 'SharkExchange',
            'profit' => 125,
            'loss' => 0,
            'trading_fees' => 5,
            'plan_followed' => true,
        ]);

        $response = $this->actingAs($user)->postJson('/ai-chat/messages', [
            'message' => 'Summarise my performance this month',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('message.role', 'assistant')
            ->assertJsonPath('message.context', 'Analysing 1 trade')
            ->assertJsonPath('message.metrics.0.label', 'Net P&L')
            ->assertJsonPath('message.metrics.2.value', '1')
            ->assertJsonFragment(['conversation_id' => 1]);

        $this->assertStringContainsString('1 trade', $response->json('message.content'));
        $this->assertDatabaseCount('ai_messages', 2);
    }

    public function test_user_cannot_read_another_users_conversation(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $conversation = AiConversation::create(['user_id' => $owner->id, 'title' => 'Private review']);

        $this->actingAs($other)
            ->getJson('/ai-chat?conversation_id='.$conversation->id)
            ->assertNotFound();
    }
}
