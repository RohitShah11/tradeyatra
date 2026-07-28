@extends('layouts.app')

@section('page_title', 'Profile')
@section('page_subtitle', 'Set India-first preferences used across your journal, exports, and analytics.')

@section('content')
<form method="POST" action="{{ route('profile.update') }}" class="panel form-grid">
    @csrf
    @method('PUT')

    <div class="span-2">
        <label>Name</label>
        <input name="name" value="{{ old('name', $user->name) }}" required>
    </div>
    <div>
        <label>Country</label>
        <select name="country" required>
            @foreach($countries as $code => $label)
                <option value="{{ $code }}" @selected(old('country', $user->country) === $code)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label>Display Currency</label>
        <select name="currency" required>
            @foreach($currencies as $code => $label)
                <option value="{{ $code }}" @selected(old('currency', $user->currency) === $code)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div class="span-2">
        <label>Timezone</label>
        <select name="timezone" required>
            @foreach($timezones as $code => $label)
                <option value="{{ $code }}" @selected(old('timezone', $user->timezone) === $code)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div class="span-2">
        <label>Language</label>
        <select name="locale" required>
            @foreach($locales as $code => $label)
                <option value="{{ $code }}" @selected(old('locale', $user->locale) === $code)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div class="span-4 actions">
        <button class="btn">Save Preferences</button>
        <a class="btn secondary" href="{{ route('dashboard') }}">Back to Dashboard</a>
    </div>
</form>

<div class="panel">
    <h2>India Product Notes</h2>
    <p class="muted">Default settings are India, INR, and Asia/Kolkata. Keep USD only for exchange products that settle in USDT or USD, while your journal can still report the final review in INR.</p>
</div>
@endsection
