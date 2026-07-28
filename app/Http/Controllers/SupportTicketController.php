<?php

namespace App\Http\Controllers;

use App\Models\SupportTicket;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SupportTicketController extends Controller
{
    public const CATEGORIES = [
        'account' => 'Account help',
        'broker_connection' => 'Broker connection',
        'sync_problem' => 'Sync problem',
        'trade_data' => 'Trade data',
        'billing' => 'Billing',
        'other' => 'Other',
    ];

    public function index(Request $request): View
    {
        $status = (string) $request->query('status');
        $tickets = $request->user()->supportTickets()
            ->when(in_array($status, ['open', 'waiting_on_support', 'waiting_on_user', 'resolved', 'closed'], true), fn ($query) => $query->where('status', $status))
            ->latest('last_replied_at')
            ->paginate(15)
            ->withQueryString();

        return view('support.index', compact('tickets', 'status'));
    }

    public function create(): View
    {
        return view('support.create', ['categories' => self::CATEGORIES]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'subject' => ['required', 'string', 'max:160'],
            'category' => ['required', Rule::in(array_keys(self::CATEGORIES))],
            'priority' => ['required', Rule::in(['low', 'normal', 'high', 'urgent'])],
            'message' => ['required', 'string', 'min:10', 'max:5000'],
        ]);

        $ticket = DB::transaction(function () use ($request, $data) {
            $ticket = SupportTicket::query()->create([
                'ticket_number' => $this->ticketNumber(),
                'user_id' => $request->user()->id,
                'subject' => $data['subject'],
                'category' => $data['category'],
                'priority' => $data['priority'],
                'status' => 'open',
                'last_replied_at' => now(),
                'last_replied_by' => 'user',
            ]);

            $ticket->messages()->create(['sender_type' => 'user', 'sender_id' => $request->user()->id, 'body' => $data['message']]);

            return $ticket;
        });

        return redirect()->route('support.show', $ticket)->with('success', 'Support ticket created. We will reply as soon as possible.');
    }

    public function show(Request $request, SupportTicket $supportTicket): View
    {
        $this->ensureOwner($request, $supportTicket);
        $supportTicket->update(['user_unread_count' => 0]);
        $supportTicket->load(['messages' => fn ($query) => $query->oldest(), 'assignedAdmin']);

        return view('support.show', compact('supportTicket'));
    }

    public function reply(Request $request, SupportTicket $supportTicket): RedirectResponse
    {
        $this->ensureOwner($request, $supportTicket);
        abort_if($supportTicket->status === 'closed', 422, 'Closed tickets cannot receive replies.');
        $data = $request->validate(['message' => ['required', 'string', 'max:5000']]);

        DB::transaction(function () use ($request, $supportTicket, $data) {
            $supportTicket->messages()->create(['sender_type' => 'user', 'sender_id' => $request->user()->id, 'body' => $data['message']]);
            $supportTicket->update([
                'status' => 'waiting_on_support',
                'last_replied_at' => now(),
                'last_replied_by' => 'user',
                'user_unread_count' => 0,
            ]);
            $supportTicket->increment('admin_unread_count');
        });

        return back()->with('success', 'Your reply was sent.');
    }

    public function updateStatus(Request $request, SupportTicket $supportTicket): RedirectResponse
    {
        $this->ensureOwner($request, $supportTicket);
        $data = $request->validate(['action' => ['required', Rule::in(['resolve', 'reopen'])]]);
        $resolved = $data['action'] === 'resolve';
        $supportTicket->update([
            'status' => $resolved ? 'resolved' : 'waiting_on_support',
            'resolved_at' => $resolved ? now() : null,
            'admin_unread_count' => $resolved ? $supportTicket->admin_unread_count : $supportTicket->admin_unread_count + 1,
        ]);

        return back()->with('success', $resolved ? 'Ticket marked as resolved.' : 'Ticket reopened.');
    }

    private function ensureOwner(Request $request, SupportTicket $ticket): void
    {
        abort_unless($ticket->user_id === $request->user()->id, 404);
    }

    private function ticketNumber(): string
    {
        do {
            $number = 'TY-'.now()->format('ymd').'-'.Str::upper(Str::random(6));
        } while (SupportTicket::query()->where('ticket_number', $number)->exists());

        return $number;
    }
}
