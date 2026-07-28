@extends('layouts.app')

@section('page_title', 'Performance Analysis')
@section('page_subtitle', 'Understand your weekly and monthly trading performance at a glance.')

@section('content')
@php($maxWeeklyNet = max(1, collect($weeklyAnalysis)->max(fn ($row) => abs($row['net'])) ?? 1))
@php($maxMonthlyNet = max(1, collect($monthlyAnalysis)->max(fn ($row) => abs($row['net'])) ?? 1))

<style>
    #analysisPageContent { position:relative; }
    #analysisPageContent.is-loading { min-height:280px; pointer-events:none; }
    #analysisPageContent.is-loading:before { content:""; position:absolute; z-index:20; inset:0; border-radius:16px; background:rgba(2,8,11,.62); backdrop-filter:blur(2px); }
    #analysisPageContent.is-loading:after { content:""; position:absolute; z-index:21; top:50%; left:50%; width:42px; height:42px; margin:-21px 0 0 -21px; border-radius:50%; background:conic-gradient(#ff7a1a 0 38%,#18c7ff 50% 82%,rgba(255,255,255,.12) 90% 100%); -webkit-mask:radial-gradient(farthest-side,transparent calc(100% - 5px),#000 0); mask:radial-gradient(farthest-side,transparent calc(100% - 5px),#000 0); filter:drop-shadow(0 0 11px rgba(24,199,255,.35)); animation:analysis-spin .7s linear infinite; }
    html[data-theme="light"] #analysisPageContent.is-loading:before { background:rgba(255,255,255,.7); }
    @keyframes analysis-spin { to { transform:rotate(360deg); } }
    .analysis-ajax-loader { position:fixed; z-index:85; top:82px; left:50%; display:flex; align-items:center; gap:10px; padding:10px 15px; border:1px solid color-mix(in srgb,var(--accent) 35%,var(--line)); border-radius:999px; color:var(--ink); background:color-mix(in srgb,var(--panel) 95%,transparent); box-shadow:0 14px 38px rgba(0,0,0,.25); opacity:0; visibility:hidden; transform:translate(-50%,-7px); transition:.15s; pointer-events:none; backdrop-filter:blur(12px); }
    .analysis-ajax-loader.active { opacity:1; visibility:visible; transform:translate(-50%,0); }
    .analysis-ajax-loader-spinner { width:17px; height:17px; border:2px solid color-mix(in srgb,var(--accent) 22%,transparent); border-top-color:var(--accent); border-right-color:var(--accent-2); border-radius:50%; animation:analysis-spin .65s linear infinite; }
    .analysis-shell { overflow: hidden; padding: 0; }
    .analysis-hero { display: flex; align-items: center; justify-content: space-between; gap: 20px; padding: 24px; border-bottom: 1px solid var(--line); background: radial-gradient(circle at 5% 0%, rgba(255,139,31,.16), transparent 38%), radial-gradient(circle at 96% 12%, rgba(0,190,230,.13), transparent 34%); }
    .analysis-heading { display: flex; align-items: center; gap: 14px; }
    .analysis-icon { width: 50px; height: 50px; display: grid; place-items: center; flex: 0 0 auto; border-radius: 15px; color: #07131d; background: linear-gradient(135deg, #ff9d2e, #ff7418); box-shadow: 0 12px 30px rgba(255,126,24,.22); }
    .analysis-icon svg { width: 25px; height: 25px; }
    .report-tabs { display: inline-flex; gap: 5px; padding: 5px; border: 1px solid var(--line); border-radius: 12px; background: rgba(3,12,21,.36); }
    .report-tab { min-width: 118px; padding: 10px 14px; border: 0; border-radius: 9px; color: var(--muted); background: transparent; font: inherit; font-size: 13px; font-weight: 900; cursor: pointer; }
    .report-tab.active { color: #07131d; background: linear-gradient(135deg, #ff9d2e, #18c7ff); box-shadow: 0 7px 20px rgba(24,199,255,.15); }
    .analysis-summary { display: grid; grid-template-columns: 1.35fr repeat(4, 1fr); gap: 10px; padding: 20px 24px; }
    .summary-card { min-height: 90px; padding: 15px 16px; border: 1px solid var(--line); border-radius: 12px; background: rgba(255,255,255,.035); }
    .summary-value { display: block; margin-top: 8px; font-size: 21px; font-weight: 900; }
    .report-panel { display: none; padding: 0 24px 24px; }
    .report-panel.active { display: block; }
    .report-head { display: flex; align-items: end; justify-content: space-between; gap: 14px; margin: 4px 0 15px; }
    .period-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; }
    .period-card { position: relative; overflow: hidden; padding: 17px; border: 1px solid var(--line); border-radius: 14px; background: rgba(255,255,255,.035); }
    .period-card::before { content: ''; position: absolute; inset: 0 auto 0 0; width: 3px; background: var(--period-color, #18c7ff); }
    .period-card.profit { --period-color: #00e6a8; }
    .period-card.loss { --period-color: #ff3b4f; }
    .period-top { display: flex; align-items: flex-start; justify-content: space-between; gap: 14px; }
    .period-label { font-size: 15px; font-weight: 900; }
    .period-net { font-size: 19px; font-weight: 900; text-align: right; }
    .period-bar { height: 7px; overflow: hidden; margin: 15px 0; border-radius: 999px; background: rgba(255,255,255,.06); }
    .period-bar span { display: block; height: 100%; min-width: 4px; border-radius: inherit; background: var(--period-color, #18c7ff); box-shadow: 0 0 14px color-mix(in srgb, var(--period-color, #18c7ff) 35%, transparent); }
    .period-metrics { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 10px; }
    .period-metric { color: var(--muted); font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: .05em; }
    .period-metric strong { display: block; margin-top: 4px; color: var(--ink); font-size: 13px; letter-spacing: 0; text-transform: none; }
    .period-money { display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; margin-top: 13px; padding-top: 13px; border-top: 1px solid var(--line); }
    .empty-report { padding: 48px 20px; border: 1px dashed var(--line); border-radius: 14px; text-align: center; color: var(--muted); }
    .empty-report strong { display: block; margin-bottom: 6px; color: var(--ink); font-size: 17px; }
    @media (max-width: 980px) {
        .analysis-summary { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .summary-card:first-child { grid-column: span 2; }
        .period-grid { grid-template-columns: 1fr; }
    }
    @media (max-width: 650px) {
        .analysis-hero { align-items: flex-start; flex-direction: column; padding: 18px 16px; }
        .report-tabs { width: 100%; }
        .report-tab { min-width: 0; flex: 1; }
        .analysis-summary { padding: 16px; }
        .report-panel { padding: 0 16px 18px; }
        .period-metrics { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .period-money { grid-template-columns: 1fr; }
    }
</style>

<div class="analysis-ajax-loader" id="analysisAjaxLoader" role="status" aria-live="polite" aria-hidden="true"><span class="analysis-ajax-loader-spinner"></span><strong>Updating analysis…</strong></div>
<div class="grid" id="analysisPageContent" aria-live="polite">
    @include('trades._exchange_tabs', ['routeName' => 'trades.analysis'])

    <section class="panel analysis-shell">
        <div class="analysis-hero">
            <div class="analysis-heading">
                <span class="analysis-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 3v18h18"/><path d="m7 15 4-4 3 3 5-7"/></svg></span>
                <div><h2>Performance report</h2><div class="muted">{{ request('broker') ?: 'All exchanges' }} · Latest 16 weeks and 12 months</div></div>
            </div>
            <div class="report-tabs" role="tablist" aria-label="Analysis period">
                <button class="report-tab active" type="button" role="tab" aria-selected="true" data-report="weekly">Weekly</button>
                <button class="report-tab" type="button" role="tab" aria-selected="false" data-report="monthly">Monthly</button>
            </div>
        </div>

        <div class="analysis-summary">
            <div class="summary-card"><span class="label">Total net P&amp;L</span>@forelse($currencyStats as $row)<span class="summary-value {{ $row['net'] >= 0 ? 'positive' : 'negative' }}">{{ $row['currency'] }} {{ number_format($row['net'], 2) }}</span>@empty<span class="summary-value">—</span>@endforelse</div>
            <div class="summary-card"><span class="label">Total trades</span><span class="summary-value">{{ $stats['total'] }}</span></div>
            <div class="summary-card"><span class="label">Win rate</span><span class="summary-value">{{ number_format($stats['win_rate'], 1) }}%</span></div>
            <div class="summary-card"><span class="label">Best trade</span><span class="summary-value positive">{{ $displayCurrency }} {{ number_format($stats['best'], 2) }}</span></div>
            <div class="summary-card"><span class="label">Worst trade</span><span class="summary-value negative">{{ $displayCurrency }} {{ number_format($stats['worst'], 2) }}</span></div>
        </div>

        @foreach(['weekly' => ['title' => 'Weekly performance', 'subtitle' => 'Compare recent trading weeks and quickly spot consistency.', 'rows' => $weeklyAnalysis, 'max' => $maxWeeklyNet], 'monthly' => ['title' => 'Monthly performance', 'subtitle' => 'See the bigger picture across your latest trading months.', 'rows' => $monthlyAnalysis, 'max' => $maxMonthlyNet]] as $key => $report)
            <div id="{{ $key }}-report" class="report-panel {{ $key === 'weekly' ? 'active' : '' }}" role="tabpanel">
                <div class="report-head"><div><h2>{{ $report['title'] }}</h2><div class="muted">{{ $report['subtitle'] }}</div></div><a class="btn secondary" href="{{ route('trades.calendar', array_filter(['broker' => request('broker')])) }}">Open calendar</a></div>
                <div class="period-grid">
                    @forelse($report['rows'] as $row)
                        <article class="period-card {{ $row['net'] >= 0 ? 'profit' : 'loss' }}">
                            <div class="period-top"><div><div class="period-label">{{ $row['label'] }}</div><div class="muted" style="margin-top:4px">{{ $row['trades'] }} {{ $row['trades'] === 1 ? 'trade' : 'trades' }}</div></div><div class="period-net {{ $row['net'] >= 0 ? 'positive' : 'negative' }}">{{ $displayCurrency }} {{ number_format($row['net'], 2) }}</div></div>
                            <div class="period-bar" title="Net P&amp;L relative to other {{ $key }} periods"><span style="width:{{ max(2, (abs($row['net']) / $report['max']) * 100) }}%"></span></div>
                            <div class="period-metrics">
                                <span class="period-metric">Win rate<strong>{{ number_format($row['win_rate'], 1) }}%</strong></span>
                                <span class="period-metric">Avg trade<strong class="{{ $row['avg_trade'] >= 0 ? 'positive' : 'negative' }}">{{ $displayCurrency }} {{ number_format($row['avg_trade'], 2) }}</strong></span>
                                <span class="period-metric">Best<strong class="positive">{{ $displayCurrency }} {{ number_format($row['best'], 2) }}</strong></span>
                                <span class="period-metric">Worst<strong class="negative">{{ $displayCurrency }} {{ number_format($row['worst'], 2) }}</strong></span>
                            </div>
                            <div class="period-money">
                                <span class="period-metric">Gross profit<strong class="positive">{{ $displayCurrency }} {{ number_format($row['profit'], 2) }}</strong></span>
                                <span class="period-metric">Gross loss<strong class="negative">{{ $displayCurrency }} {{ number_format($row['loss'], 2) }}</strong></span>
                                <span class="period-metric">Net result<strong class="{{ $row['net'] >= 0 ? 'positive' : 'negative' }}">{{ $displayCurrency }} {{ number_format($row['net'], 2) }}</strong></span>
                            </div>
                        </article>
                    @empty
                        <div class="empty-report" style="grid-column:1/-1"><strong>No {{ $key }} data yet</strong>Synced closed trades will appear here automatically.</div>
                    @endforelse
                </div>
            </div>
        @endforeach
    </section>
</div>

<script>
(() => {
let analysisRequestController = null;
const analysisPageCache = new Map();
const analysisAjaxLoader = document.getElementById('analysisAjaxLoader');

function activateAnalysisReport(report) {
    const page = document.getElementById('analysisPageContent');
    if (!page) return;
    page.querySelectorAll('.report-tab').forEach((item) => {
        const active = item.dataset.report === report;
        item.classList.toggle('active', active);
        item.setAttribute('aria-selected', active ? 'true' : 'false');
    });
    page.querySelectorAll('.report-panel').forEach((panel) => panel.classList.toggle('active', panel.id === `${report}-report`));
}

function isAnalysisUrl(url) {
    return url.origin === window.location.origin && url.pathname.endsWith('/analysis');
}

function requestAnalysisPage(url, signal = null) {
    const key = new URL(url, window.location.href).toString();
    if (analysisPageCache.has(key)) return analysisPageCache.get(key);

    const request = fetch(key, {
        headers: { 'Accept': 'text/html', 'X-Requested-With': 'XMLHttpRequest' },
        signal,
    }).then((response) => {
        if (!response.ok) throw new Error('The analysis page could not be loaded.');
        return response.text();
    }).catch((error) => {
        analysisPageCache.delete(key);
        throw error;
    });

    analysisPageCache.set(key, request);
    return request;
}

function prefetchAnalysisLinks() {
    const links = [...document.querySelectorAll('#analysisPageContent .analysis-tabs a[href]')];
    const prefetch = () => links.forEach((link) => requestAnalysisPage(link.href).catch(() => {}));
    if ('requestIdleCallback' in window) window.requestIdleCallback(prefetch, { timeout: 1200 });
    else setTimeout(prefetch, 250);
}

async function loadAnalysisPage(url, updateHistory = true) {
    const page = document.getElementById('analysisPageContent');
    if (!page) return;

    analysisRequestController?.abort();
    const requestController = new AbortController();
    analysisRequestController = requestController;
    const activeReport = page.querySelector('.report-tab.active')?.dataset.report || 'weekly';
    const loadingStartedAt = performance.now();
    page.classList.add('is-loading');
    page.setAttribute('aria-busy', 'true');
    analysisAjaxLoader?.classList.add('active');
    analysisAjaxLoader?.setAttribute('aria-hidden', 'false');

    try {
        await new Promise((resolve) => requestAnimationFrame(() => requestAnimationFrame(resolve)));
        const html = await requestAnalysisPage(url, requestController.signal);
        const documentCopy = new DOMParser().parseFromString(html, 'text/html');
        const nextPage = documentCopy.getElementById('analysisPageContent');
        if (!nextPage) throw new Error('The analysis response was incomplete.');

        page.innerHTML = nextPage.innerHTML;
        activateAnalysisReport(activeReport);
        const currentHeading = document.querySelector('.topbar h1');
        const nextHeading = documentCopy.querySelector('.topbar h1');
        const currentSubtitle = document.querySelector('.topbar > div:first-child .muted');
        const nextSubtitle = documentCopy.querySelector('.topbar > div:first-child .muted');
        if (currentHeading && nextHeading) currentHeading.textContent = nextHeading.textContent;
        if (currentSubtitle && nextSubtitle) currentSubtitle.textContent = nextSubtitle.textContent;
        document.title = documentCopy.title || document.title;
        if (updateHistory) history.pushState({ analysis: true }, '', url);
        prefetchAnalysisLinks();
    } catch (error) {
        if (error.name === 'AbortError') return;
        window.showAppToast?.('error', 'Analysis error', error.message || 'The analysis page could not be loaded.');
    } finally {
        if (analysisRequestController === requestController) {
            const remainingLoaderTime = Math.max(0, 450 - (performance.now() - loadingStartedAt));
            if (remainingLoaderTime) await new Promise((resolve) => setTimeout(resolve, remainingLoaderTime));
            page.classList.remove('is-loading');
            page.removeAttribute('aria-busy');
            analysisAjaxLoader?.classList.remove('active');
            analysisAjaxLoader?.setAttribute('aria-hidden', 'true');
        }
    }
}

document.addEventListener('click', (event) => {
    const reportTab = event.target.closest('#analysisPageContent .report-tab');
    if (reportTab) {
        activateAnalysisReport(reportTab.dataset.report);
        return;
    }

    const link = event.target.closest('#analysisPageContent a[href]');
    if (!link || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey || link.target === '_blank') return;
    const url = new URL(link.href, window.location.href);
    if (!isAnalysisUrl(url)) return;
    event.preventDefault();
    loadAnalysisPage(url.toString());
}, { signal: window.tradeYatraNavigationSignal });

window.addEventListener('popstate', () => {
    if (window.location.pathname.endsWith('/analysis')) loadAnalysisPage(window.location.href, false);
}, { signal: window.tradeYatraNavigationSignal });

prefetchAnalysisLinks();
})();
</script>
@endsection
