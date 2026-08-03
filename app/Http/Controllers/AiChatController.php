<?php

namespace App\Http\Controllers;

use App\Models\AiConversation;
use App\Services\FreeTradingInsightsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AiChatController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $conversation = null;
        if ($request->filled('conversation_id')) {
            $conversation = $this->conversation((int) $request->input('conversation_id'));
        } else {
            $conversation = AiConversation::query()->where('user_id', auth()->id())->latest('updated_at')->first();
        }

        return response()->json([
            'conversation' => $conversation ? $this->conversationPayload($conversation) : null,
            'conversations' => $this->recentConversations(),
            'suggestions' => $this->suggestions(),
            'provider' => 'Free journal insights',
        ]);
    }

    public function createConversation(): JsonResponse
    {
        $conversation = AiConversation::create([
            'user_id' => auth()->id(),
            'title' => 'New conversation',
        ]);

        return response()->json([
            'conversation' => $this->conversationPayload($conversation),
            'conversations' => $this->recentConversations(),
        ], 201);
    }

    public function message(Request $request, FreeTradingInsightsService $insights): JsonResponse
    {
        $data = $request->validate([
            'conversation_id' => ['nullable', 'integer'],
            'message' => ['required', 'string', 'max:1000'],
        ]);

        $conversation = isset($data['conversation_id'])
            ? $this->conversation((int) $data['conversation_id'])
            : AiConversation::create(['user_id' => auth()->id(), 'title' => 'New conversation']);

        $conversation->messages()->create(['role' => 'user', 'content' => trim($data['message'])]);
        if ($conversation->title === 'New conversation') {
            $conversation->update(['title' => str(trim($data['message']))->limit(48)]);
        }

        $answer = $insights->answer(auth()->user(), $data['message']);
        $assistant = $conversation->messages()->create([
            'role' => 'assistant',
            'content' => $answer['message'],
            'metadata' => ['context' => $answer['context'], 'links' => $answer['links'], 'metrics' => $answer['metrics']],
        ]);
        $conversation->touch();

        return response()->json([
            'conversation_id' => $conversation->id,
            'message' => [
                'id' => $assistant->id,
                'role' => 'assistant',
                'content' => $assistant->content,
                'context' => $answer['context'],
                'links' => $answer['links'],
                'metrics' => $answer['metrics'],
            ],
            'conversations' => $this->recentConversations(),
        ]);
    }

    private function conversation(int $id): AiConversation
    {
        return AiConversation::query()->where('user_id', auth()->id())->findOrFail($id);
    }

    private function conversationPayload(AiConversation $conversation): array
    {
        return [
            'id' => $conversation->id,
            'title' => $conversation->title,
            'messages' => $conversation->messages()->oldest()->limit(100)->get()->map(fn ($message) => [
                'id' => $message->id,
                'role' => $message->role,
                'content' => $message->content,
                'context' => $message->metadata['context'] ?? null,
                'links' => $message->metadata['links'] ?? [],
                'metrics' => $message->metadata['metrics'] ?? [],
            ])->values(),
        ];
    }

    private function recentConversations(): array
    {
        return AiConversation::query()->where('user_id', auth()->id())->latest('updated_at')->limit(12)->get(['id', 'title'])->toArray();
    }

    private function suggestions(): array
    {
        return [
            'Summarise my performance this month',
            'Compare my Shark and Delta trades',
            'Which strategy performs best?',
            'What mistakes appear in my losing trades?',
            'Give me a weekly coaching review',
            'Review my trading plan for today',
        ];
    }
}
