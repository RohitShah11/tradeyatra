<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\AnalyticsTracker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request, AnalyticsTracker $analytics)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(10)->mixedCase()->numbers()->symbols()],
        ]);

        if ($validator->fails()) {
            $analytics->track($request, 'registration_validation_failed', [
                'fields' => array_keys($validator->errors()->toArray()),
            ]);

            throw new ValidationException($validator);
        }

        $data = $validator->validated();

        $user = User::create([
            'name' => $data['name'],
            'email' => strtolower($data['email']),
            'password' => $data['password'],
            'country' => 'IN',
            'currency' => 'INR',
            'timezone' => 'Asia/Kolkata',
            'locale' => 'en',
        ]);

        Auth::login($user);
        $request->session()->regenerate();
        $analytics->track($request, 'registration_completed', [], $user->id);

        return redirect()->route('dashboard')->with('success', 'Welcome to TradeYatra. Connect SharkExchange when you are ready.');
    }

    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $remember = $request->boolean('remember');

        if (! Auth::attempt(['email' => strtolower($credentials['email']), 'password' => $credentials['password']], $remember)) {
            return back()
                ->withErrors(['email' => 'The email or password is incorrect.'])
                ->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'You have been logged out.');
    }
}
