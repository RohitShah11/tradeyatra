@extends('layouts.auth', ['title' => 'Create Your Trading Journal | TradeYatra', 'description' => 'Create a private TradeYatra account for Shark and Delta Exchange trade tracking and performance review.'])

@section('content')
    <h2>Create your account</h2>
    <p class="muted">Start your private multi-exchange trading journal.</p>

    <form method="POST" action="{{ route('register.store') }}" data-loading-form data-analytics-form="registration">
        @csrf
        <div class="field">
            <label for="name">Name</label>
            <input id="name" name="name" type="text" value="{{ old('name') }}" placeholder="Enter your full name" required autofocus autocomplete="name">
        </div>
        <div class="field">
            <label for="email">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" placeholder="you@example.com" required autocomplete="email">
        </div>
        <div class="field">
            <label for="password">Password</label>
            <div class="password-wrap">
                <input id="password" name="password" type="password" placeholder="Create a strong password" required minlength="10" autocomplete="new-password" data-strength-input>
                <button class="password-toggle" type="button" data-password-toggle="password" aria-label="Show password" aria-pressed="false"><svg class="eye-open" viewBox="0 0 24 24"><path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg><svg class="eye-off" viewBox="0 0 24 24"><path d="m3 3 18 18M10.6 10.6a2 2 0 0 0 2.8 2.8M9.9 5.1A11.5 11.5 0 0 1 12 5c6.5 0 10 7 10 7a18 18 0 0 1-2.1 3M6.2 6.2C3.4 8.1 2 12 2 12s3.5 7 10 7c1.5 0 2.8-.4 4-.9"/></svg></button>
            </div>
            <div class="strength" id="passwordStrength" hidden><div class="strength-head"><span>Password strength</span><span class="strength-value">Not entered</span></div><div class="strength-bars"><span></span><span></span><span></span></div></div>
            <div class="password-hint">Use at least 10 characters with uppercase, lowercase, a number, and a symbol.</div>
        </div>
        <div class="field">
            <label for="password_confirmation">Confirm password</label>
            <div class="password-wrap">
                <input id="password_confirmation" name="password_confirmation" type="password" placeholder="Re-enter your password" required minlength="10" autocomplete="new-password">
                <button class="password-toggle" type="button" data-password-toggle="password_confirmation" aria-label="Show password" aria-pressed="false"><svg class="eye-open" viewBox="0 0 24 24"><path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg><svg class="eye-off" viewBox="0 0 24 24"><path d="m3 3 18 18M10.6 10.6a2 2 0 0 0 2.8 2.8M9.9 5.1A11.5 11.5 0 0 1 12 5c6.5 0 10 7 10 7a18 18 0 0 1-2.1 3M6.2 6.2C3.4 8.1 2 12 2 12s3.5 7 10 7c1.5 0 2.8-.4 4-.9"/></svg></button>
            </div>
            <div class="password-match" id="passwordMatch" aria-live="polite"></div>
        </div>
        <p class="muted">By creating an account, you agree to the <a href="{{ route('legal.terms') }}">Terms</a>, <a href="{{ route('legal.privacy') }}">Privacy Policy</a>, and <a href="{{ route('legal.risk') }}">Risk Disclaimer</a>.</p>
        <button class="btn" type="submit" data-loading-text="Creating account..."><span class="submit-spinner" aria-hidden="true"></span><span class="submit-label">Create account</span></button>
    </form>

    <div class="switch">
        Already have an account? <a href="{{ route('login') }}">Log in</a>
    </div>
@endsection
