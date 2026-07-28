<?php

namespace App\Http\Controllers;

use App\Mail\ContactMessageReceived;
use App\Models\ContactMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Throwable;

class ContactController extends Controller
{
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email:rfc', 'max:254'],
            'subject' => ['required', Rule::in(['product', 'broker', 'account', 'feedback', 'other'])],
            'message' => ['required', 'string', 'min:10', 'max:3000'],
            'website' => ['nullable', 'string', 'max:0'],
        ]);

        $contactMessage = ContactMessage::create([
            ...collect($validated)->only(['name', 'email', 'subject', 'message'])->all(),
            'ip_address' => $request->ip(),
            'user_agent' => mb_substr((string) $request->userAgent(), 0, 500),
        ]);

        if ($recipient = config('mail.contact_to')) {
            try {
                Mail::to($recipient)->send(new ContactMessageReceived($contactMessage));
            } catch (Throwable $exception) {
                Log::error('Contact notification could not be sent.', [
                    'contact_message_id' => $contactMessage->id,
                    'exception' => $exception,
                ]);
            }
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Thanks for reaching out. We received your message and will reply soon.',
            ], 201);
        }

        return redirect(route('home').'#contact')
            ->with('contact_success', 'Thanks for reaching out. We received your message and will reply soon.');
    }
}
