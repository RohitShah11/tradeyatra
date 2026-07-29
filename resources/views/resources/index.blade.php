<!DOCTYPE html>
<html lang="en-IN">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="description" content="Practical TradeYatra guides for maintaining a crypto trading journal, connecting supported exchanges, reviewing P&L, and improving your trading review process.">
    <meta name="robots" content="index,follow,max-image-preview:large"><link rel="canonical" href="{{ route('resources.index') }}">
    <meta property="og:type" content="website"><meta property="og:site_name" content="TradeYatra"><meta property="og:title" content="Trading Journal Resources | TradeYatra"><meta property="og:description" content="Practical guides for recording, reviewing, and learning from your trading history."><meta property="og:url" content="{{ route('resources.index') }}"><meta property="og:image" content="{{ asset('images/branding/tradeyatra-logo.png') }}">
    <title>Trading Journal Resources &amp; Guides | TradeYatra</title><link rel="icon" type="image/png" href="{{ asset('images/branding/tradeyatra-icon-v2.png') }}">
    @include('resources.styles') @livewireStyles
</head>
<body>
@include('partials.public-header')
<main>
    <header class="resource-hero"><div class="resource-wrap"><span class="resource-eyebrow">TradeYatra resources</span><h1>Build a trading review process that you can repeat.</h1><p class="resource-lead">Practical, risk-aware guides for organizing exchange history, reviewing P&amp;L, using screenshots and notes, and turning journal data into clearer decisions.</p></div></header>
    <section class="resource-wrap resource-grid" aria-label="Trading journal guides">
        @foreach($guides as $guide)<article class="resource-card"><small>{{ $guide['eyebrow'] }}</small><h2>{{ $guide['title'] }}</h2><p>{{ $guide['description'] }}</p><a href="{{ \App\Http\Controllers\ResourceController::routeFor($guide['slug']) }}" wire:navigate.hover>Read the guide →</a></article>@endforeach
    </section>
</main>
@include('partials.public-footer')
</body></html>
