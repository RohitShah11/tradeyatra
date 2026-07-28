@extends('layouts.app')

@section('page_title', 'Market News')
@section('page_subtitle', 'Follow market-moving stories before you plan your next trade.')
@if($error)
    @section('toast_error', $error)
@endif

@section('content')
<style>
    .news-hero { position:relative; overflow:hidden; padding:24px; margin-bottom:16px; display:flex; align-items:center; justify-content:space-between; gap:24px; }
    .news-hero:after { content:""; position:absolute; width:260px; height:260px; right:-95px; top:-140px; border-radius:50%; background:radial-gradient(circle,rgba(25,199,181,.24),transparent 68%); pointer-events:none; }
    .news-eyebrow { display:flex; align-items:center; gap:8px; color:var(--accent-2); font-size:10px; font-weight:900; letter-spacing:.15em; text-transform:uppercase; }
    .news-live-dot { width:8px; height:8px; border-radius:50%; background:var(--good); box-shadow:0 0 0 5px color-mix(in srgb,var(--good) 14%,transparent); }
    .news-hero h2 { max-width:620px; margin:9px 0 6px; font-size:24px; line-height:1.25; }
    .news-hero p { margin:0; max-width:690px; color:var(--muted); }
    .news-hero-actions { position:relative; z-index:1; display:flex; align-items:center; gap:10px; }
    .news-crypto-link { white-space:nowrap; display:inline-flex; align-items:center; gap:8px; min-height:47px; padding:10px 16px; border:1px solid color-mix(in srgb,var(--accent-2) 35%,var(--line)); border-radius:12px; color:var(--ink); background:linear-gradient(135deg,color-mix(in srgb,var(--accent) 12%,var(--panel)),color-mix(in srgb,var(--accent-2) 12%,var(--panel))); font-size:12px; font-weight:900; transition:transform .18s ease,border-color .18s ease; }
    .news-crypto-link:hover { transform:translateY(-2px); border-color:var(--accent-2); }
    .news-crypto-link .icon { color:var(--accent-2); }
    .news-count { position:relative; z-index:1; min-width:118px; padding:15px 18px; border:1px solid var(--line); border-radius:13px; text-align:center; background:color-mix(in srgb,var(--panel) 70%,transparent); }
    .news-count strong,.news-count small { display:block; }
    .news-count strong { font-size:24px; }
    .news-count small { color:var(--muted); font-size:9px; font-weight:800; letter-spacing:.12em; text-transform:uppercase; }
    .news-filter { display:flex; gap:12px; align-items:center; padding:14px; margin-bottom:16px; }
    .news-search { display:flex; flex:1; min-width:220px; }
    .news-search input { width:100%; min-height:43px; border-radius:10px 0 0 10px; }
    .news-search button { border-radius:0 10px 10px 0; }
    .news-categories { display:flex; gap:7px; overflow-x:auto; padding:2px 0 7px; margin-bottom:10px; scrollbar-width:thin; }
    .news-category { white-space:nowrap; padding:8px 13px; border:1px solid var(--line); border-radius:999px; color:var(--muted); font-size:12px; font-weight:800; background:rgba(255,255,255,.025); }
    .news-category:hover,.news-category.active { color:#fff; border-color:transparent; background:linear-gradient(135deg,var(--accent),color-mix(in srgb,var(--accent-2) 78%,var(--accent))); }
    .news-grid { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:16px; }
    .news-card { position:relative; min-height:255px; display:flex; flex-direction:column; padding:20px; overflow:hidden; transition:transform .18s ease,border-color .18s ease; }
    .news-card:hover { transform:translateY(-3px); border-color:color-mix(in srgb,var(--accent) 40%,transparent); }
    .news-card:before { content:""; position:absolute; inset:0 0 auto; height:3px; background:linear-gradient(90deg,var(--accent),var(--accent-2)); opacity:.7; }
    .news-card.featured { grid-column:span 2; min-height:300px; background:linear-gradient(135deg,color-mix(in srgb,var(--accent) 12%,var(--panel)),color-mix(in srgb,var(--accent-2) 7%,var(--panel))); }
    .news-card.featured h2 { font-size:23px; max-width:740px; }
    .news-meta { display:flex; align-items:center; gap:8px; color:var(--muted); font-size:10px; font-weight:750; text-transform:uppercase; letter-spacing:.06em; }
    .news-source { color:var(--accent-2); }
    .news-signals { display:flex; flex-wrap:wrap; align-items:center; gap:6px; margin:-2px 0 14px; }
    .news-sentiment,.news-ticker { display:inline-flex; align-items:center; min-height:23px; padding:3px 8px; border-radius:999px; font-size:9px; font-weight:900; letter-spacing:.07em; text-transform:uppercase; }
    .news-sentiment.bullish { color:var(--good); background:color-mix(in srgb,var(--good) 12%,transparent); border:1px solid color-mix(in srgb,var(--good) 25%,transparent); }
    .news-sentiment.bearish { color:var(--bad); background:color-mix(in srgb,var(--bad) 11%,transparent); border:1px solid color-mix(in srgb,var(--bad) 24%,transparent); }
    .news-sentiment.neutral { color:var(--muted); background:rgba(255,255,255,.045); border:1px solid var(--line); }
    .news-ticker { color:var(--accent); background:var(--soft); }
    .news-card h2 { margin:16px 0 10px; font-size:16px; line-height:1.45; }
    .news-card p { margin:0 0 20px; color:var(--muted); font-size:12px; }
    .news-read { margin-top:auto; display:flex; align-items:center; justify-content:space-between; color:var(--ink); font-size:12px; font-weight:900; }
    .news-read span:last-child { width:28px; height:28px; border-radius:50%; display:grid; place-items:center; background:var(--soft); color:var(--accent); }
    .news-empty { grid-column:1/-1; padding:48px 24px; text-align:center; }
    .news-empty svg { width:38px; height:38px; color:var(--muted); margin-bottom:12px; }
    .news-pagination { display:flex; align-items:center; justify-content:center; gap:12px; margin-top:20px; }
    .news-pagination .disabled { opacity:.4; pointer-events:none; }
    .news-page-state { color:var(--muted); font-size:12px; }
    .news-results-shell { position:relative; min-height:180px; }
    .news-results { transition:opacity .16s ease,transform .16s ease,filter .16s ease; }
    .news-results.loading { opacity:.45; transform:translateY(4px); pointer-events:none; }
    .news-loader { position:absolute; z-index:8; top:22px; left:50%; display:flex; align-items:center; gap:11px; min-width:190px; padding:12px 17px; border:1px solid color-mix(in srgb,var(--accent) 36%,var(--line)); border-radius:999px; color:var(--ink); background:color-mix(in srgb,var(--panel) 94%,transparent); box-shadow:0 16px 45px rgba(0,0,0,.28); backdrop-filter:blur(14px); opacity:0; visibility:hidden; transform:translate(-50%,-8px); transition:opacity .16s ease,transform .16s ease,visibility .16s; pointer-events:none; }
    .news-loader.active { opacity:1; visibility:visible; transform:translate(-50%,0); }
    .news-loader-spinner { width:20px; height:20px; flex:0 0 20px; border:2px solid color-mix(in srgb,var(--accent) 22%,transparent); border-top-color:var(--accent); border-right-color:var(--accent-2); border-radius:50%; animation:news-spin .7s linear infinite; }
    .news-loader strong,.news-loader small { display:block; line-height:1.15; }
    .news-loader strong { font-size:11px; }
    .news-loader small { margin-top:3px; color:var(--muted); font-size:9px; }
    @keyframes news-spin { to { transform:rotate(360deg); } }
    @media(prefers-reduced-motion:reduce) { .news-loader-spinner { animation-duration:1.5s; } }
    .news-ajax-error { padding:16px; margin-bottom:14px; color:var(--bad); border:1px solid color-mix(in srgb,var(--bad) 30%,transparent); border-radius:12px; background:color-mix(in srgb,var(--bad) 8%,transparent); }
    @media(max-width:1080px) { .news-grid { grid-template-columns:repeat(2,minmax(0,1fr)); } }
    @media(max-width:680px) { .news-hero { align-items:flex-start; flex-direction:column; } .news-count { display:none; } .news-hero-actions { width:100%; } .news-crypto-link { width:100%; justify-content:center; } .news-filter { align-items:stretch; flex-direction:column; } .news-grid { grid-template-columns:1fr; } .news-card.featured { grid-column:span 1; min-height:255px; } }
</style>

<section class="panel news-hero">
    <div>
        <div class="news-eyebrow"><span class="news-live-dot"></span>Live market intelligence</div>
        <h2>News that can shape your trading day</h2>
        <p>Scan the latest financial headlines, then open the original publisher for the complete story.</p>
    </div>
    <div class="news-hero-actions">
        <a class="news-crypto-link" href="{{ route('crypto-intelligence.index') }}"><svg class="icon"><use href="#icon-market"></use></svg>Crypto Intelligence <span aria-hidden="true">→</span></a>
        <div class="news-count"><strong id="newsCount">{{ number_format($articles->total()) }}</strong><small>Headlines</small></div>
    </div>
</section>

<form class="panel news-filter" method="GET" action="{{ route('news.index') }}">
    <input type="hidden" name="category" value="{{ $category }}">
    <div class="news-search">
        <input type="search" name="q" value="{{ $query }}" placeholder="Search headlines, topics or sources…" aria-label="Search market news">
        <button class="btn" type="submit">Search</button>
    </div>
    <a id="newsClear" class="btn secondary" href="{{ route('news.index', ['category' => $category]) }}" @if($query === '') hidden @endif>Clear</a>
</form>

<nav class="news-categories" aria-label="News categories">
    @foreach($categories as $slug => $label)
        <a class="news-category {{ $category === $slug ? 'active' : '' }}" href="{{ route('news.index', array_filter(['category' => $slug, 'q' => $query])) }}">{{ $label }}</a>
    @endforeach
</nav>

<div class="news-results-shell">
    <div id="newsLoader" class="news-loader" role="status" aria-live="polite" aria-hidden="true">
        <span class="news-loader-spinner" aria-hidden="true"></span>
        <span><strong>Updating headlines</strong><small>Fetching the latest market news…</small></span>
    </div>
    <div id="newsResults" class="news-results" aria-live="polite">@include('news._results')</div>
</div>

@if(false)
<div class="news-grid">
    @forelse($articles as $article)
        <article class="panel news-card {{ $loop->first && $articles->currentPage() === 1 ? 'featured' : '' }}">
            <div class="news-meta">
                <span class="news-source">{{ $article['source'] }}</span><span>•</span>
                <time datetime="{{ $article['published_at'] }}">{{ \Illuminate\Support\Carbon::parse($article['published_at'])->diffForHumans() }}</time>
            </div>
            <h2>{{ $article['title'] }}</h2>
            @if(($article['sentiment'] ?? null) || !empty($article['tickers'] ?? []))
                <div class="news-signals" aria-label="Article trading signals">
                    @if($article['sentiment'] ?? null)
                        <span class="news-sentiment {{ $article['sentiment'] }}" title="Alpha Vantage article sentiment{{ isset($article['sentiment_score']) ? ': '.number_format($article['sentiment_score'], 3) : '' }}">{{ $article['sentiment'] }}</span>
                    @endif
                    @foreach(($article['tickers'] ?? []) as $ticker)<span class="news-ticker">{{ $ticker }}</span>@endforeach
                </div>
            @endif
            @if($article['description'])<p>{{ $article['description'] }}</p>@endif
            <a class="news-read" href="{{ $article['url'] }}" target="_blank" rel="noopener noreferrer"><span>Read full story</span><span aria-hidden="true">↗</span></a>
        </article>
    @empty
        <div class="panel news-empty">
            <svg class="icon"><use href="#icon-news"></use></svg>
            <h2>No headlines found</h2>
            <p class="muted">{{ $query ? 'Try a broader search or another category.' : 'Live headlines could not be loaded right now. Please try again.' }}</p>
        </div>
    @endforelse
</div>

@if($articles->hasPages())
    <nav class="news-pagination" aria-label="News pages">
        <a class="btn secondary {{ $articles->onFirstPage() ? 'disabled' : '' }}" href="{{ $articles->previousPageUrl() ?: '#' }}">Previous</a>
        <span class="news-page-state">Page {{ $articles->currentPage() }} of {{ $articles->lastPage() }}</span>
        <a class="btn secondary {{ $articles->hasMorePages() ? '' : 'disabled' }}" href="{{ $articles->nextPageUrl() ?: '#' }}">Next</a>
    </nav>
@endif
@endif

<script>
(() => {
    const form = document.querySelector('.news-filter');
    const results = document.getElementById('newsResults');
    const loader = document.getElementById('newsLoader');
    const count = document.getElementById('newsCount');
    const clear = document.getElementById('newsClear');
    const search = form?.querySelector('input[name="q"]');
    const categoryInput = form?.querySelector('input[name="category"]');
    let activeRequest;

    if (!form || !results) return;

    const syncControls = (url, data) => {
        const params = new URL(url).searchParams;
        const category = data.category || params.get('category') || 'all';
        categoryInput.value = category;
        search.value = data.query ?? params.get('q') ?? '';
        clear.hidden = search.value === '';
        clear.href = `{{ route('news.index') }}?category=${encodeURIComponent(category)}`;
        count.textContent = new Intl.NumberFormat().format(data.total || 0);
        document.querySelectorAll('.news-category').forEach(link => {
            link.classList.toggle('active', (new URL(link.href).searchParams.get('category') || 'all') === category);
        });
    };

    const loadNews = async (target, push = true) => {
        activeRequest?.abort();
        activeRequest = new AbortController();
        const url = new URL(target, window.location.href);
        results.classList.add('loading');
        results.setAttribute('aria-busy', 'true');
        loader.classList.add('active');
        loader.setAttribute('aria-hidden', 'false');

        try {
            const response = await fetch(url, {
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                signal: activeRequest.signal,
            });
            if (!response.ok) throw new Error('Unable to load market news.');
            const data = await response.json();
            results.innerHTML = data.error ? `<div class="news-ajax-error">${data.error}</div>${data.html}` : data.html;
            syncControls(url, data);
            if (push) history.pushState({}, '', url);
        } catch (error) {
            if (error.name !== 'AbortError') {
                results.insertAdjacentHTML('afterbegin', '<div class="news-ajax-error">Could not refresh the headlines. Please try again.</div>');
            }
        } finally {
            results.classList.remove('loading');
            results.removeAttribute('aria-busy');
            loader.classList.remove('active');
            loader.setAttribute('aria-hidden', 'true');
        }
    };

    form.addEventListener('submit', event => {
        event.preventDefault();
        const url = new URL(form.action);
        new FormData(form).forEach((value, key) => { if (value) url.searchParams.set(key, value); });
        loadNews(url);
    });

    document.addEventListener('click', event => {
        const link = event.target.closest('.news-category, #newsClear, .news-pagination a:not(.disabled)');
        if (!link) return;
        event.preventDefault();
        loadNews(link.href);
    }, { signal: window.tradeYatraNavigationSignal });

    window.addEventListener('popstate', () => loadNews(window.location.href, false), { signal: window.tradeYatraNavigationSignal });
})();
</script>
@endsection
