<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Review the terms for using TradeYatra, its Shark and Delta Exchange integrations, and trading performance tools.">
    <meta name="robots" content="index,follow">
    <link rel="canonical" href="{{ route('legal.terms') }}">
    <meta property="og:type" content="article">
    <meta property="og:site_name" content="TradeYatra">
    <meta property="og:title" content="Terms of Use | TradeYatra">
    <meta property="og:description" content="Review the terms for using TradeYatra, its exchange integrations, journal, and trading performance tools.">
    <meta property="og:url" content="{{ route('legal.terms') }}">
    <meta property="og:image" content="{{ asset('images/branding/tradeyatra-logo.png') }}">
    <meta name="twitter:card" content="summary_large_image">
    <title>Terms of Use | TradeYatra</title>
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
        <h1>Terms of Use</h1>
        <p class="muted">Last updated: {{ now()->format('d M Y') }}</p>
        <h2>Use of the journal</h2>
        <p>TradeYatra is a record-keeping and performance-review tool. You are responsible for the accuracy of trades, notes, screenshots, and exchange settings you add.</p>
        <h2>No financial advice</h2>
        <p>The journal does not provide investment, tax, legal, or trading advice. Analytics are informational and should not be treated as a recommendation to buy, sell, hold, or trade any instrument.</p>
        <h2>Account security</h2>
        <p>You must keep your login and exchange API credentials safe. Use read-only API keys wherever your broker or exchange supports them.</p>
        <h2>Acceptable use</h2>
        <p>Do not upload illegal content, attempt to access another user's data, abuse sync endpoints, or use the service for activity that violates Indian law or exchange rules.</p>
        <h2>Data exports</h2>
        <p>CSV exports are provided for convenience. You should verify exported records before using them for tax, audit, or compliance purposes.</p>
    </section>
</main>
@include('partials.public-footer')
</body>
</html>
