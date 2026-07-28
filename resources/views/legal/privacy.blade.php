<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Read how TradeYatra handles account information, exchange API credentials, trading records, and privacy.">
    <meta name="robots" content="index,follow">
    <link rel="canonical" href="{{ route('legal.privacy') }}">
    <meta property="og:type" content="article">
    <meta property="og:site_name" content="TradeYatra">
    <meta property="og:title" content="Privacy Policy | TradeYatra">
    <meta property="og:description" content="Read how TradeYatra handles account information, exchange credentials, trading records, and privacy.">
    <meta property="og:url" content="{{ route('legal.privacy') }}">
    <meta property="og:image" content="{{ asset('images/branding/tradeyatra-logo.png') }}">
    <meta name="twitter:card" content="summary_large_image">
    <title>Privacy Policy | TradeYatra</title>
    <link rel="icon" type="image/png" href="{{ asset('images/branding/tradeyatra-icon-v2.png') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=manrope:400,500,600,700,800" rel="stylesheet">
    <style>
        body { margin: 0; color: #eef8ff; background: radial-gradient(circle at 18% 8%, rgba(24,199,255,.16), transparent 28rem), linear-gradient(180deg, #041019 0%, #050b13 100%); font: 15px/1.65 "Manrope", ui-sans-serif, system-ui, sans-serif; }
        main { width: min(860px, calc(100% - 32px)); margin: 0 auto; padding: 42px 0; }
        a { color: #18c7ff; font-weight: 800; text-decoration: none; }
        h1 { font-size: 38px; line-height: 1.1; margin: 0 0 12px; }
        h2 { margin-top: 28px; }
        .panel { background: linear-gradient(180deg, rgba(16,31,45,.92), rgba(8,18,30,.92)); border: 1px solid rgba(120,214,255,.18); border-radius: 8px; padding: 28px; }
        .muted { color: #8fa8b8; }
    </style>
    @livewireStyles
</head>
<body>
@include('partials.public-header')
<main>
    <p><a href="{{ route('home') }}" wire:navigate.hover>Back to home</a></p>
    <section class="panel">
        <h1>Privacy Policy</h1>
        <p class="muted">Last updated: {{ now()->format('d M Y') }}</p>
        <h2>Data we store</h2>
        <p>We store account details, profile preferences, trade records, screenshots, sync logs, and exchange connection settings needed to run your journal.</p>
        <h2>Exchange credentials</h2>
        <p>API secrets are encrypted before storage. Do not enter withdrawal-enabled keys. Prefer read-only keys for trade history imports.</p>
        <h2>How data is used</h2>
        <p>Your data is used to show dashboards, filters, analytics, sync history, and CSV exports inside your account.</p>
        <h2>Website analytics and approximate location</h2>
        <p>For website traffic reporting, we may derive an approximate country, state, and city from a visitor's network address through an IP geolocation provider. This is not GPS or a precise address. TradeYatra stores the resulting general location with the analytics event and does not store the raw IP address in its analytics records.</p>
        <h2>Data access</h2>
        <p>Journal records are scoped to your user account. Administrators should access user data only for support, security, debugging, or legal requirements.</p>
        <h2>Retention and deletion</h2>
        <p>Before public launch, add a full account deletion workflow and a written data retention policy that matches your production hosting and backup setup.</p>
    </section>
</main>
@include('partials.public-footer')
</body>
</html>
