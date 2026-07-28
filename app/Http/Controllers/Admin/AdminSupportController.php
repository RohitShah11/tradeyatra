<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\SupportTicketController;
use App\Models\SupportTicket;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminSupportController extends Controller
{
    public function index(Request $request): View
    {
        $status = (string) $request->query('status');
        $priority = (string) $request->query('priority');
        $search = trim((string) $request->query('search'));
        $tickets = SupportTicket::query()->with('user')
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
        $supportTicket->update(['admin_unread_count' => 0]);
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
}
