<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="{{ $description ?? 'Access your private TradeYatra workspace for Shark Exchange and Delta Exchange trade review.' }}">
    <meta name="robots" content="noindex,follow">
    <meta name="theme-color" content="#071014">
    <title>{{ $title ?? 'TradeYatra' }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/branding/tradeyatra-icon-v2.png') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=manrope:400,500,600,700,800" rel="stylesheet" />
    <style>
        :root {
            --bg: #050b13;
            --bg-2: #071a24;
            --panel: #0b1722;
            --panel-2: #101f2d;
            --ink: #eef8ff;
            --muted: #8fa8b8;
            --line: rgba(120, 214, 255, .18);
            --accent: #18c7ff;
            --accent-2: #20e6a4;
            --accent-dark: #0aa4d6;
            --soft: rgba(24, 199, 255, .1);
            --danger: #ff6171;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            background:
                radial-gradient(circle at 16% 12%, rgba(24, 199, 255, .18), transparent 28rem),
                radial-gradient(circle at 84% 8%, rgba(32, 230, 164, .12), transparent 24rem),
                linear-gradient(180deg, #041019 0%, var(--bg) 56%, #071018 100%);
            color: var(--ink);
            font: 14px/1.5 "Manrope", ui-sans-serif, system-ui, sans-serif;
            position:relative;
        }
        body:before { content:""; position:fixed; inset:0; pointer-events:none; background-image:radial-gradient(circle at 7% 15%,rgba(25,199,181,.9) 0 1px,transparent 2px),radial-gradient(circle at 23% 62%,rgba(255,255,255,.55) 0 1px,transparent 2px),radial-gradient(circle at 43% 23%,rgba(25,199,181,.7) 0 1px,transparent 2px),radial-gradient(circle at 71% 12%,rgba(255,173,104,.8) 0 1px,transparent 2px),radial-gradient(circle at 93% 35%,rgba(25,199,181,.7) 0 1px,transparent 2px),radial-gradient(circle at 81% 82%,rgba(255,255,255,.45) 0 1px,transparent 2px); }
        body:after { content:""; position:fixed; width:620px; height:620px; left:-330px; top:-330px; border:1px solid rgba(255,122,26,.13); border-radius:50%; box-shadow:0 0 0 58px rgba(255,122,26,.035),0 0 0 116px rgba(25,199,181,.025); pointer-events:none; }
        a { color: var(--accent); text-decoration: none; font-weight: 700; }
        .auth-shell { min-height: 100vh; display: grid; grid-template-columns: minmax(0, 1.05fr) minmax(380px, .95fr); }
        .story {
            color: var(--ink);
            padding: 52px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            background:
                linear-gradient(135deg, rgba(24, 199, 255, .14), rgba(32, 230, 164, .08)),
                rgba(3, 16, 24, .72);
            border-right: 1px solid var(--line);
        }
        .brand { font-size: 20px; font-weight: 900; letter-spacing: 0; display: inline-flex; align-items: center; gap: 0; }
        .brand:before {
            content: "";
            width: 42px;
            height: 42px;
            border-radius: 0;
            display: inline-grid;
            place-items: center;
            color: #031018;
            background: transparent url("{{ asset('images/branding/tradeyatra-icon-v2.png') }}") center/contain no-repeat;
            box-shadow: none;
            margin-right: -4px;
        }
        .story h1 { margin: 80px 0 14px; max-width: 640px; font-size: 42px; line-height: 1.06; letter-spacing: 0; }
        .story p { max-width: 580px; color: #b5c9d7; font-size: 16px; margin: 0; }
        .points { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 12px; margin-top: 38px; max-width: 760px; }
        .point { border: 1px solid var(--line); border-radius: 8px; padding: 14px; background: rgba(255,255,255,.045); }
        .point strong { display: block; color: var(--ink); margin-bottom: 4px; }
        .point span { color: var(--muted); font-size: 13px; }
        .form-wrap { display: flex; align-items: center; justify-content: center; padding: 36px; }
        .panel {
            width: min(100%, 430px);
            background: linear-gradient(180deg, rgba(16, 31, 45, .96), rgba(8, 18, 30, .96));
            border: 1px solid var(--line);
            border-radius: 18px;
            padding: 28px;
            box-shadow: 0 30px 80px rgba(0,0,0,.34), inset 0 1px 0 rgba(255,255,255,.08);
        }
        h2 { font-size: 25px; margin: 0 0 6px; letter-spacing: 0; }
        .muted { color: var(--muted); margin: 0 0 22px; }
        label { display: block; font-size: 12px; color: var(--muted); margin-bottom: 6px; font-weight: 800; }
        input {
            width: 100%;
            border: 1px solid var(--line);
            border-radius: 10px;
            padding: 11px 12px;
            min-height: 42px;
            color: var(--ink);
            background: rgba(255,255,255,.06);
            outline: none;
        }
        input:focus { border-color: var(--accent); box-shadow: 0 0 0 3px rgba(24, 199, 255, .14); }
        .password-wrap { position:relative; }
        .password-wrap input { padding-right:48px; }
        .password-toggle { position:absolute; top:50%; right:7px; width:34px; height:34px; display:grid; place-items:center; transform:translateY(-50%); border:0; border-radius:8px; color:var(--muted); background:transparent; cursor:pointer; }
        .password-toggle:hover,.password-toggle:focus { color:var(--ink); background:var(--soft); outline:none; }
        .password-toggle svg { width:18px; height:18px; fill:none; stroke:currentColor; stroke-width:2; stroke-linecap:round; stroke-linejoin:round; }
        .password-toggle .eye-off { display:none; }
        .password-toggle.visible .eye-open { display:none; }
        .password-toggle.visible .eye-off { display:block; }
        .strength { margin-top:9px; }
        .strength-head { display:flex; justify-content:space-between; gap:10px; margin-bottom:6px; color:var(--muted); font-size:11px; font-weight:800; }
        .strength-bars { display:grid; grid-template-columns:repeat(3,1fr); gap:5px; }
        .strength-bars span { height:5px; border-radius:999px; background:rgba(255,255,255,.09); transition:.2s ease; }
        .strength.weak .strength-bars span:nth-child(1) { background:#fb7185; }
        .strength.medium .strength-bars span:nth-child(-n+2) { background:#fbbf24; }
        .strength.strong .strength-bars span { background:#20e6a4; }
        .strength.weak .strength-value { color:#fb7185; }
        .strength.medium .strength-value { color:#fbbf24; }
        .strength.strong .strength-value { color:#20e6a4; }
        .password-hint { margin-top:7px; color:var(--muted); font-size:11px; line-height:1.45; }
        .password-match { min-height:17px; margin-top:6px; color:var(--muted); font-size:11px; font-weight:700; }
        .password-match.good { color:#20e6a4; }
        .password-match.bad { color:#fb7185; }
        .field { margin-bottom: 14px; }
        .row { display: flex; justify-content: space-between; gap: 12px; align-items: center; margin: 4px 0 18px; }
        .check { display: flex; align-items: center; gap: 8px; color: var(--muted); font-weight: 700; font-size: 13px; }
        .check input { width: auto; min-height: auto; }
        .remember-row { margin:4px 0 18px; padding:11px 12px; border:1px solid var(--line); border-radius:10px; background:rgba(255,255,255,.025); }
        .remember-row .check { width:max-content; color:var(--ink); cursor:pointer; }
        .remember-row small { display:block; margin:5px 0 0 26px; color:var(--muted); font-size:10px; }
        .login-options { display:flex; align-items:center; justify-content:space-between; gap:14px; }
        .login-options a { font-size:12px; white-space:nowrap; }
        .btn {
            width: 100%;
            border: 0;
            border-radius: 8px;
            min-height: 44px;
            background: linear-gradient(135deg, var(--accent), var(--accent-2));
            color: #031018;
            font-weight: 900;
            cursor: pointer;
            box-shadow: 0 16px 44px rgba(24, 199, 255, .24);
        }
        .btn:hover { filter: brightness(1.06); transform: translateY(-1px); }
        .btn.is-loading { display:flex; align-items:center; justify-content:center; gap:9px; cursor:wait; filter:none; transform:none; opacity:.86; }
        .submit-spinner { display:none; width:17px; height:17px; flex:0 0 17px; border:2px solid rgba(255,255,255,.38); border-top-color:#fff; border-radius:50%; animation:submit-spin .7s linear infinite; }
        .btn.is-loading .submit-spinner { display:inline-block; }
        @keyframes submit-spin { to { transform:rotate(360deg); } }
        .toast-viewport {
            position: fixed;
            top: 18px;
            right: 18px;
            z-index: 80;
            width: min(380px, calc(100vw - 36px));
            display: grid;
            gap: 10px;
            pointer-events: none;
        }
        .toast {
            position: relative;
            overflow: hidden;
            border: 1px solid var(--line);
            border-radius: 8px;
            padding: 13px 42px 13px 14px;
            color: var(--ink);
            background: linear-gradient(180deg, rgba(16,31,45,.98), rgba(8,18,30,.98));
            box-shadow: 0 18px 50px rgba(0,0,0,.28);
            pointer-events: auto;
            animation: toast-in .18s ease-out;
        }
        .toast:before {
            content: "";
            position: absolute;
            inset: 0 auto 0 0;
            width: 4px;
            background: var(--accent);
        }
        .toast.success {
            border-color: rgba(32,230,164,.55);
            background: linear-gradient(135deg, rgba(32,230,164,.24), rgba(8,18,30,.97));
        }
        .toast.error {
            border-color: rgba(255,97,113,.58);
            background: linear-gradient(135deg, rgba(255,97,113,.24), rgba(8,18,30,.97));
        }
        .toast.success:before { background: var(--accent-2); }
        .toast.error:before { background: var(--danger); }
        .toast-title { display: block; margin-bottom: 2px; font-weight: 900; }
        .toast.success .toast-title { color: var(--accent-2); }
        .toast.error .toast-title { color: var(--danger); }
        .toast-message { color: currentColor; opacity: .84; }
        .toast-close {
            position: absolute;
            top: 8px;
            right: 8px;
            width: 28px;
            min-width: 28px;
            height: 28px;
            border: 1px solid var(--line);
            border-radius: 8px;
            color: var(--ink);
            background: rgba(255,255,255,.06);
            cursor: pointer;
        }
        @keyframes toast-in {
            from { opacity: 0; transform: translateY(-8px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .switch { margin-top: 18px; color: var(--muted); text-align: center; }
        /* Shark Ember theme */
        :root {
            --bg:#071014; --bg-2:#0c1a20; --panel:#0f1c22; --panel-2:#14272e;
            --ink:#f7fbfc; --muted:#94aeb5; --line:rgba(255,122,26,.2);
            --accent:#ff7a1a; --accent-2:#19c7b5; --accent-dark:#df5708;
            --soft:rgba(255,122,26,.12); --danger:#fb7185;
        }
        body { background:radial-gradient(circle at 12% 5%,rgba(255,122,26,.23),transparent 34rem),radial-gradient(circle at 88% 10%,rgba(25,199,181,.14),transparent 30rem),linear-gradient(155deg,#071014,#0b171c 55%,#050c0f); }
        .story { background:linear-gradient(145deg,rgba(255,122,26,.11),rgba(25,199,181,.04)),rgba(5,13,16,.72); }
        .brand:before { background:transparent url("{{ asset('images/branding/tradeyatra-icon-v2.png') }}") center/contain no-repeat; box-shadow:none; }
        .btn { background:linear-gradient(115deg,#ff4c00 0%,#bf4911 40%,#12a5ba 72%,#11a5bd 100%); color:#fff; box-shadow:0 14px 38px rgba(8,124,141,.24); }
        .panel { background:linear-gradient(145deg,rgba(20,39,46,.97),rgba(8,20,24,.98)); border-radius:20px; }
        .point { border-radius:12px; background:rgba(255,255,255,.04); }
        input:focus { border-color:#ff7a1a; box-shadow:0 0 0 3px rgba(255,122,26,.15); }
        a { color:#ff9a47; }
        @media (max-width: 900px) {
            .auth-shell { grid-template-columns: 1fr; }
            .story { padding: 30px; }
            .story h1 { margin-top: 38px; font-size: 32px; }
            .points { grid-template-columns: 1fr; }
            .form-wrap { padding: 24px; }
        }
    </style>
</head>
<body>
@include('partials.public-theme-toggle', ['floating' => true])
@php
    $toastMessages = collect();

    if (session('success')) {
        $toastMessages->push(['type' => 'success', 'title' => 'Success', 'message' => session('success')]);
    }

    if (session('error')) {
        $toastMessages->push(['type' => 'error', 'title' => 'Error', 'message' => session('error')]);
    }

    if ($errors->any()) {
        $toastMessages->push(['type' => 'error', 'title' => 'Please check', 'message' => $errors->first()]);
    }
@endphp
@if($toastMessages->isNotEmpty())
    <div class="toast-viewport" aria-live="polite" aria-atomic="true">
        @foreach($toastMessages as $toast)
            <div class="toast {{ $toast['type'] }}" role="status">
                <strong class="toast-title">{{ $toast['title'] }}</strong>
                <div class="toast-message">{{ $toast['message'] }}</div>
                <button class="toast-close" type="button" aria-label="Dismiss message" onclick="this.closest('.toast').remove()">x</button>
            </div>
        @endforeach
    </div>
@endif
<div class="auth-shell">
    <section class="story">
        <div>
            <a class="brand" href="{{ route('home') }}">TradeYatra</a>
            <h1>One journal. Every trade. Clearer decisions.</h1>
            <p>Bring Shark and Delta Exchange into one private performance workspace built for deliberate traders.</p>
            <div class="points">
                <div class="point"><strong>Two exchanges</strong><span>Shark and Delta, side by side.</span></div>
                <div class="point"><strong>Deep review</strong><span>Reports, calendar, notes and screenshots.</span></div>
                <div class="point"><strong>Private by design</strong><span>Your journal belongs only to you.</span></div>
            </div>
        </div>
    </section>
    <main class="form-wrap">
        <section class="panel">
            @yield('content')
        </section>
    </main>
</div>
<script>
setTimeout(() => {
    document.querySelectorAll('.toast').forEach((toast) => toast.remove());
}, 6000);

document.querySelectorAll('[data-password-toggle]').forEach((button) => {
    button.addEventListener('click', () => {
        const input = document.getElementById(button.dataset.passwordToggle);
        const show = input.type === 'password';
        input.type = show ? 'text' : 'password';
        button.classList.toggle('visible', show);
        button.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
        button.setAttribute('aria-pressed', show ? 'true' : 'false');
    });
});

const passwordInput = document.querySelector('[data-strength-input]');
const strengthBox = document.getElementById('passwordStrength');
const confirmationInput = document.getElementById('password_confirmation');
const matchMessage = document.getElementById('passwordMatch');

function updatePasswordFeedback() {
    if (!passwordInput || !strengthBox) return;
    const value = passwordInput.value;
    strengthBox.hidden = value.length === 0;
    let score = 0;
    if (value.length >= 10) score++;
    if (/[a-z]/.test(value) && /[A-Z]/.test(value)) score++;
    if (/\d/.test(value)) score++;
    if (/[^A-Za-z0-9]/.test(value)) score++;
    const level = !value ? '' : (score <= 1 ? 'weak' : (score <= 3 ? 'medium' : 'strong'));
    strengthBox.className = `strength ${level}`;
    strengthBox.querySelector('.strength-value').textContent = level ? level[0].toUpperCase() + level.slice(1) : 'Not entered';

    if (confirmationInput && matchMessage) {
        if (!confirmationInput.value) {
            matchMessage.textContent = '';
            matchMessage.className = 'password-match';
        } else {
            const matches = value === confirmationInput.value;
            matchMessage.textContent = matches ? 'Passwords match' : 'Passwords do not match';
            matchMessage.className = `password-match ${matches ? 'good' : 'bad'}`;
        }
    }
}

passwordInput?.addEventListener('input', updatePasswordFeedback);
confirmationInput?.addEventListener('input', updatePasswordFeedback);

document.querySelectorAll('[data-loading-form]').forEach((form) => {
    form.addEventListener('submit', () => {
        const button = form.querySelector('button[type="submit"]');
        if (!button || button.disabled) return;

        button.disabled = true;
        button.classList.add('is-loading');
        button.setAttribute('aria-busy', 'true');
        const label = button.querySelector('.submit-label');
        if (label) {
            button.dataset.originalText = label.textContent;
            label.textContent = button.dataset.loadingText || 'Please wait...';
        }
    });
});

window.addEventListener('pageshow', () => {
    document.querySelectorAll('[data-loading-form] button[type="submit"]').forEach((button) => {
        button.disabled = false;
        button.classList.remove('is-loading');
        button.removeAttribute('aria-busy');
        const label = button.querySelector('.submit-label');
        if (label && button.dataset.originalText) label.textContent = button.dataset.originalText;
    });
});
</script>
</body>
</html>
