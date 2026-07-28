@extends('layouts.auth', ['title' => 'Reset Your Password | TradeYatra', 'description' => 'Request a secure password reset link for your TradeYatra account.'])

@section('content')
    <h2>Forgot your password?</h2>
    <p class="muted">Enter the email used for your journal. We will send a secure reset link if an account exists.</p>

    <form method="POST" action="{{ route('password.email') }}" data-loading-form>
        @csrf
        <div class="field">
            <label for="email">Account email</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" placeholder="you@example.com" required autofocus autocomplete="email">
        </div>
        <button class="btn" type="submit" data-loading-text="Sending link..."><span class="submit-spinner" aria-hidden="true"></span><span class="submit-label">Email reset link</span></button>
    </form>

    <div class="switch">
        Remembered your password? <a href="{{ route('login') }}">Back to login</a>
    </div>
@endsection
