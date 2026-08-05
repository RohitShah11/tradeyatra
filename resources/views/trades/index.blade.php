@extends('layouts.app')

@section('page_title', $pageTitle ?? 'Trade Journal')
@section('page_subtitle', $pageSubtitle ?? 'A fast, focused record of every imported and manual trade.')

@section('content')
<style>
    .journal-page { position:relative; display:grid; gap:14px; }
    .journal-page.is-loading { min-height:280px; pointer-events:none; }
    .journal-page.is-loading:before { content:""; position:absolute; z-index:20; inset:0; border-radius:16px; background:rgba(2,8,11,.62); backdrop-filter:blur(2px); }
    .journal-page.is-loading:after { content:""; position:absolute; z-index:21; top:50%; left:50%; width:42px; height:42px; margin:-21px 0 0 -21px; border-radius:50%; background:conic-gradient(#ff7a1a 0 38%,#18c7ff 50% 82%,rgba(255,255,255,.12) 90% 100%); -webkit-mask:radial-gradient(farthest-side,transparent calc(100% - 5px),#000 0); mask:radial-gradient(farthest-side,transparent calc(100% - 5px),#000 0); filter:drop-shadow(0 0 11px rgba(24,199,255,.35)); animation:journal-spin .7s linear infinite; }
    html[data-theme="light"] .journal-page.is-loading:before { background:rgba(255,255,255,.7); }
    @keyframes journal-spin { to { transform:rotate(360deg); } }
    .journal-ajax-loader { position:fixed; z-index:85; top:82px; left:50%; display:flex; align-items:center; gap:10px; padding:10px 15px; border:1px solid color-mix(in srgb,var(--accent) 35%,var(--line)); border-radius:999px; color:var(--ink); background:color-mix(in srgb,var(--panel) 95%,transparent); box-shadow:0 14px 38px rgba(0,0,0,.25); opacity:0; visibility:hidden; transform:translate(-50%,-7px); transition:.15s; pointer-events:none; backdrop-filter:blur(12px); }
    .journal-ajax-loader.active { opacity:1; visibility:visible; transform:translate(-50%,0); }
    .journal-ajax-loader-spinner { width:17px; height:17px; border:2px solid color-mix(in srgb,var(--accent) 22%,transparent); border-top-color:var(--accent); border-right-color:var(--accent-2); border-radius:50%; animation:journal-spin .65s linear infinite; }
    .journal-head { display:flex; justify-content:space-between; align-items:center; gap:16px; padding:17px; border:1px solid var(--line); border-radius:16px; background:var(--panel-bg); }
    .journal-tabs { display:flex; gap:6px; flex-wrap:wrap; }
    .journal-tab { display:inline-flex; align-items:center; gap:7px; padding:8px 11px; border:1px solid transparent; border-radius:9px; color:var(--muted); font-size:11px; font-weight:900; }
    .journal-tab:hover { color:var(--ink); background:var(--soft); }
    .journal-tab.active { color:#fff; background:linear-gradient(135deg,#ff7a1a,#00b8d9); box-shadow:0 8px 22px rgba(0,184,217,.14); }
    .journal-tab.shark { border-color:rgba(0,184,217,.2); color:#45c9e8; background:rgba(0,184,217,.055); }
    .journal-tab.shark:hover { color:#fff; background:rgba(0,184,217,.16); }
    .journal-tab.shark.active { color:#fff; border-color:transparent; background:linear-gradient(135deg,#00b8d9,#087e9a); box-shadow:0 8px 24px rgba(0,184,217,.2); }
    .journal-tab.delta { border-color:rgba(255,122,26,.2); color:#ff9a47; background:rgba(255,122,26,.055); }
    .journal-tab.delta:hover { color:#fff; background:rgba(255,122,26,.16); }
    .journal-tab.delta.active { color:#fff; border-color:transparent; background:linear-gradient(135deg,#ff7a1a,#dc5507); box-shadow:0 8px 24px rgba(255,122,26,.2); }
    .journal-tab i { width:7px; height:7px; border-radius:50%; background:currentColor; }
    .journal-actions { display:flex; gap:8px; flex-wrap:wrap; }
    .trade-filter-panel { border:1px solid var(--line); border-radius:15px; background:var(--panel-bg); overflow:hidden; }
    .trade-filter-panel summary { display:flex; justify-content:space-between; align-items:center; gap:14px; padding:13px 16px; cursor:pointer; list-style:none; color:var(--ink); font-size:12px; font-weight:900; }
    .trade-filter-panel summary::-webkit-details-marker { display:none; }
    .trade-filter-panel summary > span:first-child { display:flex; align-items:center; gap:8px; }
    .filter-summary-copy { color:var(--muted); font-size:10px; font-weight:700; }
    .trade-filter-panel[open] summary { border-bottom:1px solid var(--line); }
    .trade-filter-form { display:grid; grid-template-columns:repeat(5,minmax(0,1fr)); gap:11px; padding:15px; }
    .filter-actions { grid-column:1/-1; display:flex; gap:8px; flex-wrap:wrap; }
    .result-strip { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:9px; }
    .result-metric { padding:12px 14px; border:1px solid var(--line); border-radius:12px; background:rgba(255,255,255,.025); }
    .result-metric span,.result-metric strong { display:block; }
    .result-metric span { color:var(--muted); font-size:9px; font-weight:900; text-transform:uppercase; letter-spacing:.09em; }
    .result-metric strong { margin-top:4px; font-size:18px; letter-spacing:-.03em; }
    .journal-table-panel { padding:0; overflow:hidden; }
    .table-toolbar { display:flex; justify-content:space-between; align-items:center; gap:14px; padding:13px 16px; border-bottom:1px solid var(--line); }
    .table-toolbar strong { display:block; }.table-toolbar small{display:block;color:var(--muted);margin-top:2px}
    .fast-badge { padding:5px 8px; border-radius:999px; color:#56d9ef; background:rgba(0,184,217,.09); font-size:9px; font-weight:900; }
    .trade-table { min-width:920px; }
    .trade-table th { padding:10px 14px; background:rgba(255,255,255,.018); }
    .trade-table td { padding:12px 14px; }
    .trade-table tbody tr { transition:background .15s ease; }
    .trade-table tbody tr:hover { background:rgba(0,184,217,.035); }
    .market-cell { display:flex; gap:10px; align-items:center; }
    .market-icon { width:34px; height:34px; flex:0 0 34px; border-radius:10px; display:grid; place-items:center; color:#fff; background:linear-gradient(135deg,#ff7a1a,#00b8d9); font-size:10px; font-weight:900; }
    .market-copy strong,.market-copy small { display:block; }.market-copy small{color:var(--muted);font-size:9px;margin-top:2px}
    .exchange-label { display:inline-flex; align-items:center; gap:5px; color:var(--muted); font-size:9px; font-weight:800; }
    .exchange-label:before { content:""; width:6px; height:6px; border-radius:50%; background:#7d8f95; }
    .exchange-label.shark:before { background:#00b8d9; }.exchange-label.delta:before{background:#ff7a1a}
    .side-badge { display:inline-flex; padding:4px 7px; border-radius:7px; font-size:9px; font-weight:900; }
    .side-badge.long { color:#003b2a; background:#00e6a8; }.side-badge.short{color:#fff;background:#ff3b4f}
    .execution strong,.execution small { display:block; }.execution strong{font-size:11px}.execution small{color:var(--muted);font-size:9px;margin-top:2px}
    .pnl-value { font-size:13px; font-weight:900; white-space:nowrap; }
    .source-label { padding:4px 7px; border:1px solid var(--line); border-radius:7px; color:var(--muted); font-size:9px; font-weight:800; }
    .media-preview { position:relative; display:inline-flex; width:54px; height:40px; border:1px solid var(--line); border-radius:9px; overflow:hidden; background:rgba(0,184,217,.055); box-shadow:0 7px 18px rgba(0,0,0,.2); }
    .media-preview img { width:100%; height:100%; object-fit:cover; transition:transform .18s ease,filter .18s ease; }
    .media-preview:hover img,.media-preview:focus img { transform:scale(1.08); filter:brightness(1.08); }
    .media-preview:focus { outline:2px solid var(--accent-2); outline-offset:2px; }
    .media-count { position:absolute; right:3px; bottom:3px; min-width:18px; height:18px; padding:0 5px; display:grid; place-items:center; border-radius:999px; color:#fff; background:rgba(2,8,11,.86); border:1px solid rgba(255,255,255,.28); font-size:9px; font-weight:900; }
    .row-actions { display:flex; justify-content:flex-end; gap:6px; }
    .row-action { min-width:31px; height:31px; padding:0 8px; border:1px solid var(--line); border-radius:8px; display:inline-flex; align-items:center; justify-content:center; color:var(--muted); background:rgba(255,255,255,.035); font-size:9px; font-weight:900; cursor:pointer; }
    .row-action:hover { color:var(--ink); border-color:rgba(0,184,217,.25); }.row-action.delete:hover{color:#fff;background:#ff3b4f;border-color:#ff3b4f}
    .empty-journal { padding:48px 18px !important; text-align:center !important; color:var(--muted); }
    .journal-pagination { padding:13px 16px; border-top:1px solid var(--line); }
    @media(max-width:1100px){.trade-filter-form{grid-template-columns:repeat(3,1fr)}.result-strip{grid-template-columns:repeat(2,1fr)}}
    @media(max-width:700px){.journal-head{align-items:flex-start;flex-direction:column}.trade-filter-form{grid-template-columns:1fr}.result-strip{grid-template-columns:1fr}.filter-summary-copy{display:none}}
</style>

@php
    $currentRoute = request()->route()?->getName();
    $pageTrades = $trades->getCollection();
    $pageNet = $pageTrades->sum(fn($trade) => $trade->net_pnl);
    $pageWins = $pageTrades->filter(fn($trade) => $trade->net_pnl > 0)->count();
    $pageWinRate = $pageTrades->count() ? round(($pageWins / $pageTrades->count()) * 100, 1) : 0;
    $pageFees = $pageTrades->sum('trading_fees');
    $displayCurrency = $pageTrades->pluck('currency')->filter()->unique()->count() === 1 ? $pageTrades->pluck('currency')->filter()->first() : (auth()->user()->currency ?: 'INR');
@endphp

<div class="journal-ajax-loader" id="tradeJournalLoader" role="status" aria-live="polite" aria-hidden="true"><span class="journal-ajax-loader-spinner"></span><strong>Updating trades…</strong></div>
<div class="journal-page" id="tradeJournalContent" aria-live="polite">
    <section class="journal-head">
        <nav class="journal-tabs" aria-label="Trade journal views">
            <a class="journal-tab {{ $currentRoute === 'trades.index' ? 'active' : '' }}" href="{{ route('trades.index') }}"><i></i>All trades</a>
            <a class="journal-tab shark {{ $currentRoute === 'trades.shark' ? 'active' : '' }}" href="{{ route('trades.shark') }}"><i></i>Shark</a>
            <a class="journal-tab delta {{ $currentRoute === 'trades.delta' ? 'active' : '' }}" href="{{ route('trades.delta') }}"><i></i>Delta</a>
        </nav>
        <div class="journal-actions"><a class="btn secondary" href="{{ route('trades.export', request()->query()) }}">Export</a></div>
    </section>

    @include('trades._filters', ['action' => $filterAction ?? route('trades.index'), 'fixedQuery' => $fixedQuery ?? []])

    <section class="result-strip" aria-label="Visible results summary">
        <div class="result-metric"><span>Visible trades</span><strong>{{ $pageTrades->count() }}</strong></div>
        <div class="result-metric"><span>Net result</span><strong class="{{ $pageNet >= 0 ? 'positive' : 'negative' }}">{{ $displayCurrency }} {{ number_format($pageNet,2) }}</strong></div>
        <div class="result-metric"><span>Win rate</span><strong>{{ $pageWinRate }}%</strong></div>
        <div class="result-metric"><span>Fees</span><strong>{{ $displayCurrency }} {{ number_format($pageFees,2) }}</strong></div>
    </section>

    <section class="panel journal-table-panel">
        <div class="table-toolbar"><div><strong>Journal records</strong><small>Newest trades first</small></div><span class="fast-badge">Server-paginated · {{ request('per_page',25) }} rows</span></div>
        <div class="table-wrap"><table class="trade-table"><thead><tr><th>Date</th><th>Market</th><th>Direction</th><th>Execution</th><th>Net P&amp;L</th><th>Fees</th><th>Source</th><th>Media</th><th></th></tr></thead><tbody>
            @forelse($trades as $trade)
                @php($decodedScreenshots = json_decode($trade->screenshot, true))
                @php($screenshots = is_array($decodedScreenshots) ? $decodedScreenshots : ($trade->screenshot ? [$decodedScreenshots ?: $trade->screenshot] : []))
                <tr>
                    <td><strong>{{ optional($trade->date)->format('d M Y') }}</strong><br><span class="muted">{{ $trade->time ? substr($trade->time,0,5) : '—' }}</span></td>
                    <td><div class="market-cell"><span class="market-icon">{{ strtoupper(substr($trade->pair,0,2)) }}</span><span class="market-copy"><strong>{{ $trade->pair }}</strong><small>{{ $trade->asset_class ?: 'Market' }} · {{ $trade->market_segment ?: 'General' }}</small><span class="exchange-label {{ $trade->broker === 'SharkExchange' ? 'shark' : ($trade->broker === 'Delta Exchange' ? 'delta' : '') }}">{{ $trade->broker ?: 'Manual' }}</span></span></div></td>
                    <td><span class="side-badge {{ strtolower($trade->trade_type) }}">{{ $trade->trade_type }}</span><br><span class="muted" style="font-size:9px">{{ $trade->status }}</span></td>
                    <td><span class="execution"><strong>{{ $trade->entry_price ?: '—' }} → {{ $trade->exit_price ?: '—' }}</strong><small>Qty {{ $trade->quantity ?: ($trade->lot_size ?: '—') }}</small></span></td>
                    <td><span class="pnl-value {{ $trade->net_pnl >= 0 ? 'positive' : 'negative' }}">{{ $trade->currency ?: $displayCurrency }} {{ number_format($trade->net_pnl,2) }}</span></td>
                    <td>{{ $trade->currency ?: $displayCurrency }} {{ number_format((float)$trade->trading_fees,2) }}</td>
                    <td><span class="source-label">{{ $trade->imported_at ? 'Imported' : 'Manual' }}</span></td>
                    <td>
                        @if(count($screenshots))
                            <a class="media-preview" href="{{ route('trades.screenshot', [$trade, 'filename' => $screenshots[0]]) }}" target="_blank" rel="noopener" title="Open trade screenshot" aria-label="Open trade screenshot{{ count($screenshots) > 1 ? ' — '.count($screenshots).' images available' : '' }}">
                                <img src="{{ route('trades.screenshot', [$trade, 'filename' => $screenshots[0]]) }}" alt="{{ $trade->pair }} trade screenshot" loading="lazy" decoding="async">
                                @if(count($screenshots) > 1)<span class="media-count">+{{ count($screenshots) - 1 }}</span>@endif
                            </a>
                        @else
                            <span class="muted">—</span>
                        @endif
                    </td>
                    <td><div class="row-actions"><a class="row-action" href="{{ route('trades.chart',$trade) }}">Chart</a><a class="row-action" href="{{ route('trades.edit',$trade) }}">Edit</a><form method="POST" action="{{ route('trades.destroy',$trade) }}">@csrf @method('DELETE')<button class="row-action delete" type="submit" onclick="return confirm('Delete this trade?')">Delete</button></form></div></td>
                </tr>
            @empty <tr><td colspan="9" class="empty-journal">{{ $emptyMessage ?? 'No trades match this view. Adjust the filters or add a new trade.' }}</td></tr> @endforelse
        </tbody></table></div>
        <div class="journal-pagination">{{ $trades->links() }}</div>
    </section>
</div>

<script>
(() => {
let tradeJournalRequest = null;
const tradeJournalCache = new Map();
const tradeJournalLoader = document.getElementById('tradeJournalLoader');

function isTradeJournalUrl(url) {
    return url.origin === window.location.origin && /\/trades(?:\/(?:shark|delta))?\/?$/.test(url.pathname);
}

function requestTradeJournal(url, signal = null) {
    const key = new URL(url, window.location.href).toString();
    if (tradeJournalCache.has(key)) return tradeJournalCache.get(key);

    const request = fetch(key, {
        headers: { 'Accept': 'text/html', 'X-Requested-With': 'XMLHttpRequest' },
        signal,
    }).then((response) => {
        if (!response.ok) throw new Error('The trade journal could not be loaded.');
        return response.text();
    }).catch((error) => {
        tradeJournalCache.delete(key);
        throw error;
    });

    tradeJournalCache.set(key, request);
    return request;
}

async function loadTradeJournal(url, updateHistory = true) {
    const journal = document.getElementById('tradeJournalContent');
    if (!journal) return;

    tradeJournalRequest?.abort();
    const requestController = new AbortController();
    tradeJournalRequest = requestController;
    const loadingStartedAt = performance.now();
    journal.classList.add('is-loading');
    journal.setAttribute('aria-busy', 'true');
    tradeJournalLoader?.classList.add('active');
    tradeJournalLoader?.setAttribute('aria-hidden', 'false');

    try {
        await new Promise((resolve) => requestAnimationFrame(() => requestAnimationFrame(resolve)));
        const html = await requestTradeJournal(url, requestController.signal);
        const documentCopy = new DOMParser().parseFromString(html, 'text/html');
        const nextJournal = documentCopy.getElementById('tradeJournalContent');
        if (!nextJournal) throw new Error('The trade journal response was incomplete.');

        journal.innerHTML = nextJournal.innerHTML;
        const currentHeading = document.querySelector('.topbar h1');
        const nextHeading = documentCopy.querySelector('.topbar h1');
        const currentSubtitle = document.querySelector('.topbar > div:first-child .muted');
        const nextSubtitle = documentCopy.querySelector('.topbar > div:first-child .muted');
        if (currentHeading && nextHeading) currentHeading.textContent = nextHeading.textContent;
        if (currentSubtitle && nextSubtitle) currentSubtitle.textContent = nextSubtitle.textContent;
        document.title = documentCopy.title || document.title;
        if (updateHistory) history.pushState({ tradeJournal: true }, '', url);
    } catch (error) {
        if (error.name === 'AbortError') return;
        window.showAppToast?.('error', 'Journal error', error.message || 'The trade journal could not be loaded.');
    } finally {
        if (tradeJournalRequest === requestController) {
            const remainingLoaderTime = Math.max(0, 450 - (performance.now() - loadingStartedAt));
            if (remainingLoaderTime) await new Promise((resolve) => setTimeout(resolve, remainingLoaderTime));
            journal.classList.remove('is-loading');
            journal.removeAttribute('aria-busy');
            tradeJournalLoader?.classList.remove('active');
            tradeJournalLoader?.setAttribute('aria-hidden', 'true');
        }
    }
}

document.addEventListener('click', (event) => {
    const link = event.target.closest('#tradeJournalContent a[href]');
    if (!link || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey || link.target === '_blank') return;
    const url = new URL(link.href, window.location.href);
    if (!isTradeJournalUrl(url)) return;
    event.preventDefault();
    loadTradeJournal(url.toString());
}, { signal: window.tradeYatraNavigationSignal });

document.addEventListener('submit', (event) => {
    const form = event.target.closest('#tradeJournalContent .trade-filter-form');
    if (!form) return;
    event.preventDefault();
    const url = new URL(form.action, window.location.href);
    new FormData(form).forEach((value, key) => {
        const normalized = String(value).trim();
        if (normalized) url.searchParams.set(key, normalized);
    });
    loadTradeJournal(url.toString());
}, { signal: window.tradeYatraNavigationSignal });

document.addEventListener('change', (event) => {
    if (!event.target.matches('#tradeJournalContent #filterPageSize')) return;
    event.target.form?.requestSubmit();
}, { signal: window.tradeYatraNavigationSignal });

window.addEventListener('popstate', () => {
    const url = new URL(window.location.href);
    if (isTradeJournalUrl(url)) loadTradeJournal(url.toString(), false);
}, { signal: window.tradeYatraNavigationSignal });
})();
</script>
@endsection
