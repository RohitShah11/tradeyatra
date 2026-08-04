<script>
    window.tradeYatraPublicNavigationController?.abort();
    window.tradeYatraPublicNavigationController = new AbortController();
    window.tradeYatraPublicNavigationSignal = window.tradeYatraPublicNavigationController.signal;
</script>
<style>
    :root { --livewire-progress-bar-color:#ff9c36; }
    .public-header { position:sticky; top:0; z-index:50; border-top:1px solid rgba(255,255,255,.08); border-bottom:1px solid rgba(0,184,217,.17); background:linear-gradient(90deg,rgba(7,16,20,.96),rgba(5,20,23,.94)); box-shadow:0 10px 35px rgba(0,0,0,.2); backdrop-filter:blur(18px); }
    .public-header-wrap { width:min(1180px,calc(100% - 36px)); min-height:64px; display:flex; align-items:center; justify-content:space-between; gap:18px; margin:0 auto; }
    .public-header-brand { display:inline-flex; align-items:center; gap:0; color:var(--text,var(--ink,#f7fbfc)); font-size:18px; font-weight:900; text-decoration:none; }
    .public-header-mark { width:36px; height:36px; display:grid; place-items:center; color:inherit; background:transparent; box-shadow:none; }
    .public-header-links { display:flex; align-items:center; gap:3px; padding:4px; border:1px solid rgba(255,255,255,.07); border-radius:11px; background:rgba(255,255,255,.025); }
    .public-header-links a { padding:7px 11px; border-radius:8px; color:var(--muted,#94aeb5); font-size:13px; font-weight:600; text-decoration:none; transition:color .18s ease,background .18s ease; }
    .public-header-links a:hover { color:var(--text,var(--ink,#f7fbfc)); background:rgba(255,122,26,.1); }
    .public-header-actions { display:flex; align-items:center; gap:10px; }
    .public-mobile-menu { position:relative; display:none; }
    .public-mobile-menu summary { width:36px; height:36px; display:grid; place-content:center; gap:4px; padding:0; border:1px solid rgba(255,255,255,.12); border-radius:8px; color:var(--text,var(--ink,#f7fbfc)); background:rgba(255,255,255,.06); cursor:pointer; list-style:none; }
    .public-mobile-menu summary::-webkit-details-marker { display:none; }
    .public-mobile-menu summary span { width:16px; height:2px; display:block; border-radius:2px; background:currentColor; transition:transform .18s ease,opacity .18s ease; }
    .public-mobile-menu[open] summary span:nth-child(1) { transform:translateY(6px) rotate(45deg); }
    .public-mobile-menu[open] summary span:nth-child(2) { opacity:0; }
    .public-mobile-menu[open] summary span:nth-child(3) { transform:translateY(-6px) rotate(-45deg); }
    .public-mobile-menu nav { position:fixed; top:64px; right:12px; left:12px; z-index:60; max-height:calc(100dvh - 76px); display:grid; gap:3px; padding:10px; overflow-y:auto; border:1px solid rgba(24,199,255,.2); border-radius:0 0 12px 12px; background:rgba(7,20,25,.99); box-shadow:0 20px 55px rgba(0,0,0,.42); }
    .public-mobile-menu nav a { padding:11px 12px; border-radius:8px; color:var(--muted,#94aeb5); font-size:13px; font-weight:700; text-decoration:none; }
    .public-mobile-menu nav a:hover,.public-mobile-menu nav a:focus-visible { color:var(--text,var(--ink,#f7fbfc)); background:rgba(255,122,26,.11); outline:none; }
    .public-header-actions .public-theme-toggle { width:36px; min-width:36px; height:36px; border-radius:8px; }
    .public-header-button { box-sizing:border-box; height:36px; min-height:36px; display:inline-flex; align-items:center; justify-content:center; padding:0 14px; border:1px solid rgba(255,255,255,.12); border-radius:8px; color:var(--text,var(--ink,#f7fbfc)); background:rgba(255,255,255,.06); font-size:13px; line-height:1; font-weight:600; white-space:nowrap; text-decoration:none; }
    .public-header-button.login { width:68px; }
    .public-header-button.login {
        border-color:transparent;
        background:
            linear-gradient(color-mix(in srgb,var(--panel,#0f1c22) 96%,transparent),color-mix(in srgb,var(--panel,#0f1c22) 96%,transparent)) padding-box,
            linear-gradient(90deg,#ff7a1a,#ffb13b,#18c7ff,#00dce8,#ff7a1a) border-box;
        background-size:100% 100%,300% 100%;
        animation:public-login-border-flow 3s linear infinite;
        box-shadow:0 7px 22px rgba(24,199,255,.08);
    }
    .public-header-button.login:hover { box-shadow:0 9px 26px rgba(255,122,26,.13),0 7px 22px rgba(24,199,255,.1); }
    .public-header-button.primary { min-width:86px; color:#fff; border-color:transparent; background:linear-gradient(115deg,#ff4c00 0%,#bf4911 40%,#12a5ba 72%,#11a5bd 100%); }
    html[data-public-theme="light"] .public-header { background:linear-gradient(90deg,rgba(255,255,255,.95),rgba(245,251,252,.93)); border-color:rgba(22,139,216,.15); }
    html[data-public-theme="light"] .public-header-links { border-color:rgba(22,139,216,.12); background:rgba(22,139,216,.035); }
    html[data-public-theme="light"] .public-mobile-menu nav { border-color:rgba(22,139,216,.16); background:rgba(255,255,255,.98); box-shadow:0 20px 55px rgba(24,55,70,.18); }
    @keyframes public-login-border-flow { to { background-position:0 0,300% 0; } }
    @media(prefers-reduced-motion:reduce){ .public-header-button.login{animation:none} }
    @media(max-width:980px){ .public-header-wrap{justify-content:flex-start}.public-header-links{display:none}.public-mobile-menu{display:block}.public-header-actions{margin-left:auto} }
    @media(max-width:640px){ .public-header-wrap{width:min(100% - 24px,1180px);min-height:60px;gap:8px}.public-mobile-menu nav{top:60px}.public-header-actions{gap:6px}.public-header-actions .public-theme-toggle,.public-header-actions .public-header-button.login{display:none}.public-header-button{padding-inline:12px} }
    @media(max-width:420px){ .public-header-wrap{width:calc(100% - 12px);gap:6px}.public-mobile-menu summary{width:32px;height:32px}.public-header-mark{width:26px;height:26px}.public-header-brand{font-size:13px}.public-header-button{height:32px;min-height:32px;padding-inline:8px;font-size:11px}.public-header-button.primary{min-width:64px}.public-mobile-menu nav{right:6px;left:6px} }
</style>
<header class="public-header">
    <div class="public-header-wrap">
        <details class="public-mobile-menu">
            <summary aria-label="Open navigation menu"><span></span><span></span><span></span></summary>
            <nav aria-label="Mobile navigation"><a href="{{ route('home') }}#inside-app">Inside App</a><a href="{{ route('broker.guide') }}" wire:navigate.hover>Connect Broker</a><a href="{{ route('resources.index') }}" wire:navigate.hover>Guides</a><a href="{{ route('home') }}#faq">FAQ</a><a href="{{ route('founder') }}" wire:navigate.hover>Founder</a><a href="{{ route('home') }}#contact">Contact</a><a href="{{ route('support-fund.index') }}" wire:navigate.hover>Support Us</a></nav>
        </details>
        <a class="public-header-brand" href="{{ route('home') }}" wire:navigate.hover><span class="public-header-mark"><img src="{{ asset('images/branding/tradeyatra-icon-v2.png') }}" alt="" style="width:100%;height:100%;object-fit:contain"></span><span>TradeYatra</span></a>
        <nav class="public-header-links" aria-label="Main navigation"><a href="{{ route('home') }}#inside-app">Inside App</a><a href="{{ route('broker.guide') }}" wire:navigate.hover>Connect Broker</a><a href="{{ route('resources.index') }}" wire:navigate.hover>Guides</a><a href="{{ route('home') }}#faq">FAQ</a><a href="{{ route('founder') }}" wire:navigate.hover>Founder</a><a href="{{ route('home') }}#contact">Contact</a><a href="{{ route('support-fund.index') }}" wire:navigate.hover>Support Us</a></nav>
        <div class="public-header-actions">
            @include('partials.public-theme-toggle')
            @auth
                <a class="public-header-button primary" href="{{ route('dashboard') }}">Dashboard</a>
            @else
                <a class="public-header-button login" href="{{ route('login') }}">Login</a>
                <a class="public-header-button primary" href="{{ route('register') }}" data-analytics-event="registration_cta_clicked" data-analytics-placement="header">Start Free</a>
            @endauth
        </div>
    </div>
</header>
@include('partials.analytics-events')
<script>
    document.querySelectorAll('.public-mobile-menu a').forEach(link => link.addEventListener('click', () => link.closest('details')?.removeAttribute('open'), { signal: window.tradeYatraPublicNavigationSignal }));
    document.addEventListener('click', event => document.querySelectorAll('.public-mobile-menu[open]').forEach(menu => {
        if (!menu.contains(event.target)) menu.removeAttribute('open');
    }), { signal: window.tradeYatraPublicNavigationSignal });
</script>
