<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class AdminProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('admin.profile', ['admin' => $request->user('admin')]);
    }

    public function update(Request $request): RedirectResponse
    {
        $admin = $request->user('admin');
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'current_password' => ['required', 'current_password:admin'],
            'password' => ['nullable', 'confirmed', Password::min(10)->letters()->numbers()],
        ]);

        $admin->update([
            'name' => $data['name'],
            ...(filled($data['password'] ?? null) ? ['password' => $data['password']] : []),
        ]);

        return back()->with('success', 'Admin profile updated.');
    }
}
