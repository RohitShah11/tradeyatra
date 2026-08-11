<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\SupportTicketController;
use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminSupportController extends Controller
{
    public function start(User $user): View|RedirectResponse
    {
        $ticket = $user->supportTickets()
            ->where('category', 'chat')
            ->latest('last_replied_at')
            ->first();

        if ($ticket) {
            if ($ticket->admin_unread_count > 0) {
                $ticket->update(['admin_unread_count' => 0]);
            }
            $ticket->load(['messages' => fn ($query) => $query->oldest(), 'user']);
        }

        return view('admin.support.chat', compact('user', 'ticket'));
    }

    public function store(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'message' => ['required', 'string', 'max:5000'],
        ]);

        $admin = $request->user('admin');
        $ticket = DB::transaction(function () use ($admin, $data, $user) {
            $ticket = $user->supportTickets()->where('category', 'chat')->latest('last_replied_at')->lockForUpdate()->first();
            if (! $ticket) {
                $ticket = SupportTicket::query()->create([
                    'ticket_number' => $this->ticketNumber(), 'user_id' => $user->id, 'assigned_admin_id' => $admin->id,
                    'subject' => 'Direct chat', 'category' => 'chat', 'priority' => 'normal', 'status' => 'waiting_on_user',
                    'user_unread_count' => 1, 'admin_unread_count' => 0, 'last_replied_at' => now(), 'last_replied_by' => 'admin',
                ]);
            } else {
                $ticket->update([
                    'assigned_admin_id' => $ticket->assigned_admin_id ?: $admin->id,
                    'status' => 'waiting_on_user', 'last_replied_at' => now(), 'last_replied_by' => 'admin',
                ]);
                $ticket->increment('user_unread_count');
            }
            $ticket->messages()->create([
                'sender_type' => 'admin',
                'sender_id' => $admin->id,
                'body' => $data['message'],
            ]);

            return $ticket;
        });

        return redirect()->route('admin.users.chat', $user);
    }

    public function index(Request $request): View
    {
        $status = (string) $request->query('status');
        $priority = (string) $request->query('priority');
        $search = trim((string) $request->query('search'));
        $tickets = SupportTicket::query()->with('user')->where('category', '!=', 'chat')
            ->when($status, fn ($query) => $query->where('status', $status))
            ->when($priority, fn ($query) => $query->where('priority', $priority))
            ->when($search, fn ($query) => $query->where(fn ($inner) => $inner
                ->where('ticket_number', 'like', "%{$search}%")
                ->orWhere('subject', 'like', "%{$search}%")
                ->orWhereHas('user', fn ($users) => $users->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"))))
            ->latest('last_replied_at')->paginate(20)->withQueryString();

        return view('admin.support.index', compact('tickets', 'status', 'priority', 'search'));
    }

    public function show(Request $request, SupportTicket $supportTicket): View
    {
        if ($supportTicket->admin_unread_count > 0) {
            $supportTicket->update(['admin_unread_count' => 0]);
        }
        $supportTicket->load(['user', 'assignedAdmin', 'messages' => fn ($query) => $query->oldest()]);

        return view('admin.support.show', ['supportTicket' => $supportTicket, 'categories' => SupportTicketController::CATEGORIES]);
    }

    public function reply(Request $request, SupportTicket $supportTicket): RedirectResponse
    {
        abort_if($supportTicket->status === 'closed', 422, 'Closed tickets cannot receive replies.');
        $data = $request->validate(['message' => ['required', 'string', 'max:5000']]);

        DB::transaction(function () use ($request, $supportTicket, $data) {
            $admin = $request->user('admin');
            $supportTicket->messages()->create(['sender_type' => 'admin', 'sender_id' => $admin->id, 'body' => $data['message']]);
            $supportTicket->update([
                'assigned_admin_id' => $supportTicket->assigned_admin_id ?: $admin->id,
                'status' => 'waiting_on_user',
                'last_replied_at' => now(),
                'last_replied_by' => 'admin',
                'admin_unread_count' => 0,
            ]);
            $supportTicket->increment('user_unread_count');
        });

        if ($supportTicket->category === 'chat' && $request->ajax()) {
            return redirect()->route('admin.users.chat', $supportTicket->user_id);
        }

        return back()->with('success', 'Reply sent to the user.');
    }

    public function update(Request $request, SupportTicket $supportTicket): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['open', 'waiting_on_support', 'waiting_on_user', 'resolved', 'closed'])],
            'priority' => ['required', Rule::in(['low', 'normal', 'high', 'urgent'])],
            'admin_notes' => ['nullable', 'string', 'max:5000'],
        ]);
        $admin = $request->user('admin');
        $supportTicket->update([
            ...$data,
            'assigned_admin_id' => $supportTicket->assigned_admin_id ?: $admin->id,
            'resolved_at' => in_array($data['status'], ['resolved', 'closed'], true) ? ($supportTicket->resolved_at ?: now()) : null,
        ]);

        return back()->with('success', 'Ticket workflow updated.');
    }

    private function ticketNumber(): string
    {
        do {
            $number = 'TY-'.now()->format('ymd').'-'.str()->upper(str()->random(6));
        } while (SupportTicket::query()->where('ticket_number', $number)->exists());

        return $number;
    }
}
