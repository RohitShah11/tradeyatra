<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminContactController extends Controller
{
    public function index(Request $request): View
    {
        $status = (string) $request->query('status');
        $search = trim((string) $request->query('search'));
        $messages = ContactMessage::query()
            ->when(in_array($status, ['new', 'in_progress', 'resolved', 'closed'], true), fn ($query) => $query->where('status', $status))
            ->when($search, fn ($query) => $query->where(fn ($inner) => $inner
                ->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('message', 'like', "%{$search}%")))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.contacts.index', compact('messages', 'status', 'search'));
    }

    public function show(ContactMessage $contactMessage): View
    {
        if ($contactMessage->status === 'new') {
            $contactMessage->update(['status' => 'in_progress']);
        }

        return view('admin.contacts.show', compact('contactMessage'));
    }

    public function update(Request $request, ContactMessage $contactMessage): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['new', 'in_progress', 'resolved', 'closed'])],
            'admin_notes' => ['nullable', 'string', 'max:5000'],
        ]);

        $contactMessage->update([
            ...$data,
            'handled_by' => $request->user('admin')->id,
            'handled_at' => in_array($data['status'], ['resolved', 'closed'], true) ? now() : null,
        ]);

        return back()->with('success', 'Contact message updated.');
    }
}
