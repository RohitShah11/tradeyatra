<!DOCTYPE html>
<html lang="en-IN">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="description" content="{{ $guide['description'] }}"><meta name="robots" content="index,follow,max-image-preview:large">
    <link rel="canonical" href="{{ \App\Http\Controllers\ResourceController::routeFor($guide['slug']) }}">
    <meta property="og:type" content="article"><meta property="og:site_name" content="TradeYatra"><meta property="og:title" content="{{ $guide['metaTitle'] }}"><meta property="og:description" content="{{ $guide['description'] }}"><meta property="og:url" content="{{ \App\Http\Controllers\ResourceController::routeFor($guide['slug']) }}"><meta property="og:image" content="{{ asset('images/branding/tradeyatra-logo.png') }}">
    <meta property="article:published_time" content="2026-07-29"><meta property="article:modified_time" content="2026-07-29">
    <title>{{ $guide['metaTitle'] }}</title><link rel="icon" type="image/png" href="{{ asset('images/branding/tradeyatra-icon-v2.png') }}">
    @include('resources.styles') @livewireStyles
    <script type="application/ld+json">{!! json_encode(['@context'=>'https://schema.org','@type'=>'Article','headline'=>$guide['title'],'description'=>$guide['description'],'datePublished'=>'2026-07-29','dateModified'=>'2026-07-29','author'=>['@type'=>'Organization','name'=>'TradeYatra'],'publisher'=>['@type'=>'Organization','name'=>'TradeYatra','logo'=>['@type'=>'ImageObject','url'=>asset('images/branding/tradeyatra-icon-v2.png')]],'mainEntityOfPage'=>\App\Http\Controllers\ResourceController::routeFor($guide['slug'])], JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}</script>
</head>
<body>
@include('partials.public-header')
<main>
    <div class="resource-wrap breadcrumb"><a href="{{ route('resources.index') }}" wire:navigate.hover>Resources</a> / {{ $guide['title'] }}</div>
    <header class="resource-hero"><div class="resource-wrap"><span class="resource-eyebrow">{{ $guide['eyebrow'] }}</span><h1>{{ $guide['title'] }}</h1><p class="resource-lead">{{ $guide['intro'] }}</p></div></header>
    <div class="resource-wrap article-layout">
        <article class="article-main">
            @foreach($guide['sections'] as $section)<section class="article-section" id="section-{{ $loop->iteration }}"><h2>{{ $section['title'] }}</h2>@foreach($section['paragraphs'] as $paragraph)<p>{{ $paragraph }}</p>@endforeach @if(!empty($section['points']))<ul class="checklist">@foreach($section['points'] as $point)<li>{{ $point }}</li>@endforeach</ul>@endif</section>@endforeach
        </article>
        <aside class="article-aside"><div class="aside-card"><strong>In this guide</strong>@foreach($guide['sections'] as $section)<a href="#section-{{ $loop->iteration }}">{{ $section['title'] }}</a>@endforeach</div><div class="aside-card"><strong>Important</strong><span style="color:var(--muted);font-size:13px">TradeYatra is a record-keeping and review tool, not financial advice. Trading involves risk.</span></div></aside>
    </div>
    <section class="resource-wrap"><h2>Frequently asked questions</h2><div class="faq-list">@foreach($guide['faq'] as $item)<article class="faq-item"><h3>{{ $item['q'] }}</h3><p>{{ $item['a'] }}</p></article>@endforeach</div></section>
    <section class="resource-wrap resource-cta"><div><h2>Put the process into practice</h2><p>Keep supported exchange records, notes, screenshots, and performance reviews in one private journal.</p></div><a class="resource-button" href="{{ route('register') }}">Start free</a></section>
    <section class="resource-wrap related"><h2>Related guides</h2><div class="resource-grid">@foreach($relatedGuides as $related)<article class="resource-card"><small>{{ $related['eyebrow'] }}</small><h2>{{ $related['title'] }}</h2><p>{{ $related['description'] }}</p><a href="{{ \App\Http\Controllers\ResourceController::routeFor($related['slug']) }}" wire:navigate.hover>Read next →</a></article>@endforeach</div></section>
</main>
@include('partials.public-footer')
</body></html>
