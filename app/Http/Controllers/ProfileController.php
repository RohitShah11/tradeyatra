<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function edit()
    {
        return view('profile.edit', [
            'user' => auth()->user(),
            'countries' => ['IN' => 'India'],
            'currencies' => ['INR' => 'Indian Rupee (INR)', 'USD' => 'US Dollar (USD)'],
            'timezones' => [
                'Asia/Kolkata' => 'India Standard Time (Asia/Kolkata)',
                'UTC' => 'UTC',
            ],
            'locales' => ['en' => 'English', 'hi' => 'Hindi ready'],
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'country' => ['required', Rule::in(['IN'])],
            'currency' => ['required', Rule::in(['INR', 'USD'])],
            'timezone' => ['required', Rule::in(['Asia/Kolkata', 'UTC'])],
            'locale' => ['required', Rule::in(['en', 'hi'])],
        ]);

        $request->user()->update($data);

        return redirect()->route('profile.edit')->with('success', 'Profile preferences updated.');
    }
}
