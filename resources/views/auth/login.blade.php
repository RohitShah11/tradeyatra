@extends('layouts.auth', ['title' => 'Log in to Your Trading Journal | TradeYatra', 'description' => 'Log in to review your Shark and Delta Exchange trades, trading calendar, notes, and performance reports.'])

@section('content')
    <h2>Welcome back</h2>
    <p class="muted">Log in to continue your Shark and Delta performance journal.</p>

    <form method="POST" action="{{ route('login.store') }}" autocomplete="on" data-loading-form>
        @csrf
        <div class="field">
            <label for="email">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" placeholder="you@example.com" required autofocus autocomplete="username">
        </div>
        <div class="field">
            <label for="password">Password</label>
            <div class="password-wrap">
                <input id="password" name="password" type="password" placeholder="Enter your password" required autocomplete="current-password">
                <button class="password-toggle" type="button" data-password-toggle="password" aria-label="Show password" aria-pressed="false">
                    <svg class="eye-open" viewBox="0 0 24 24"><path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg>
                    <svg class="eye-off" viewBox="0 0 24 24"><path d="m3 3 18 18M10.6 10.6a2 2 0 0 0 2.8 2.8M9.9 5.1A11.5 11.5 0 0 1 12 5c6.5 0 10 7 10 7a18 18 0 0 1-2.1 3M6.2 6.2C3.4 8.1 2 12 2 12s3.5 7 10 7c1.5 0 2.8-.4 4-.9"/></svg>
                </button>
            </div>
        </div>
        <div class="remember-row">
            <div class="login-options">
                <label class="check" for="remember">
                    <input id="remember" name="remember" type="checkbox" value="1" {{ old('remember') ? 'checked' : '' }}>
                    <span>Keep me signed in</span>
                </label>
                <a href="{{ route('password.request') }}">Forgot password?</a>
            </div>
            <small>Stays signed in after closing the browser. Logging out will end it.</small>
        </div>
        <button class="btn" type="submit" data-loading-text="Logging in..."><span class="submit-spinner" aria-hidden="true"></span><span class="submit-label">Log in</span></button>
    </form>

    <div class="switch">
        Start your trading yatra — <a href="{{ route('register') }}">Register now</a>
    </div>
@endsection
