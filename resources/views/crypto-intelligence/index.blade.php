@extends('layouts.app')

@section('page_title', 'Crypto Intelligence')
@section('page_subtitle', 'Free multi-exchange derivatives signals from Binance, Bybit, and OKX.')

@section('content')
<style>
    .ci-toolbar { display:flex; align-items:center; justify-content:space-between; gap:16px; margin-bottom:16px; padding:14px 16px; }
    .ci-symbols { display:flex; gap:7px; }
    .ci-symbol { padding:9px 16px; border:1px solid var(--line); border-radius:10px; color:var(--muted); font-weight:900; }
    .ci-symbol.active { color:#fff; border-color:transparent; background:linear-gradient(135deg,var(--accent),var(--accent-2)); }
    .ci-live { display:flex; align-items:center; gap:8px; color:var(--muted); font-size:11px; }
    .ci-live:before { content:""; width:8px; height:8px; border-radius:50%; background:var(--good); box-shadow:0 0 0 5px color-mix(in srgb,var(--good) 13%,transparent); }
    .ci-shell { position:relative; min-height:300px; }
    .ci-content { transition:opacity .16s ease; }
    .ci-content.loading { opacity:.35; pointer-events:none; }
    .ci-loader { position:absolute; z-index:5; top:24px; left:50%; display:none; align-items:center; gap:10px; transform:translateX(-50%); padding:12px 17px; border:1px solid var(--line); border-radius:999px; background:var(--panel); box-shadow:0 14px 35px rgba(0,0,0,.28); }
    .ci-loader.active { display:flex; }
    .ci-spinner { width:19px; height:19px; border:2px solid var(--line); border-top-color:var(--accent); border-radius:50%; animation:ci-spin .7s linear infinite; }
    @keyframes ci-spin { to { transform:rotate(360deg); } }
    .ci-stats { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:14px; margin-bottom:16px; }
    .ci-stat { padding:18px; }
    .ci-stat small { display:block; color:var(--muted); font-size:9px; font-weight:900; letter-spacing:.12em; text-transform:uppercase; }
    .ci-stat strong { display:block; margin-top:8px; font-size:22px; }
    .ci-positive { color:var(--good); } .ci-negative { color:var(--bad); }
    .ci-note { margin-top:7px; color:var(--muted); font-size:10px; }
    .ci-table th,.ci-table td { white-space:nowrap; }
    .ci-charts { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:14px; margin-bottom:16px; }
    .ci-chart-card { padding:18px; min-width:0; }
    .ci-chart-card h2 { margin-bottom:3px; }
    .ci-chart-card p { margin:0; color:var(--muted); font-size:10px; }
    .ci-chart-wrap { position:relative; height:260px; margin-top:14px; }
    .ci-exchange { font-weight:900; }
    .ci-warning { margin-bottom:12px; padding:11px 14px; border:1px solid color-mix(in srgb,var(--warn) 28%,transparent); border-radius:10px; color:var(--warn); background:color-mix(in srgb,var(--warn) 8%,transparent); font-size:11px; }
    .ci-disclaimer { margin-top:14px; color:var(--muted); font-size:10px; }
    @media(max-width:900px) { .ci-stats { grid-template-columns:repeat(2,minmax(0,1fr)); } .ci-charts { grid-template-columns:1fr; } }
    @media(max-width:600px) { .ci-toolbar { align-items:flex-start; flex-direction:column; } .ci-stats { grid-template-columns:1fr; } }
</style>

<div class="panel ci-toolbar">
    <nav class="ci-symbols" aria-label="Crypto assets">
        @foreach($symbols as $item)<a class="ci-symbol {{ $symbol === $item ? 'active' : '' }}" href="{{ route('crypto-intelligence.index', ['symbol' => $item]) }}">{{ $item }}</a>@endforeach
    </nav>
    <div class="ci-live">Public market data · refreshed every 2 minutes</div>
</div>

<div class="ci-shell">
    <div id="ciLoader" class="ci-loader"><span class="ci-spinner"></span><strong>Updating market data…</strong></div>
    <div id="ciContent" class="ci-content" aria-live="polite">@include('crypto-intelligence._dashboard')</div>
</div>

<script>
(() => {
    const content = document.getElementById('ciContent');
    const loader = document.getElementById('ciLoader');
    const chartInstances = {};

    const chartColors = () => {
        const styles = getComputedStyle(document.documentElement);
        return {
            ink: styles.getPropertyValue('--ink').trim(),
            muted: styles.getPropertyValue('--muted').trim(),
            line: styles.getPropertyValue('--line').trim(),
            accent: styles.getPropertyValue('--accent').trim(),
            accent2: styles.getPropertyValue('--accent-2').trim(),
            good: styles.getPropertyValue('--good').trim(),
            bad: styles.getPropertyValue('--bad').trim(),
        };
    };

    const renderCharts = data => {
        Object.values(chartInstances).forEach(chart => chart?.destroy());
        const colors = chartColors();
        const common = {
            responsive:true,
            maintainAspectRatio:false,
            plugins:{ legend:{ labels:{ color:colors.muted, boxWidth:10, usePointStyle:true } } },
        };
        const oiCanvas = document.getElementById('ciOpenInterestChart');
        const fundingCanvas = document.getElementById('ciFundingChart');
        if (!oiCanvas || !fundingCanvas || typeof Chart === 'undefined') return;

        chartInstances.oi = new Chart(oiCanvas, {
            type:'doughnut',
            data:{ labels:data.labels, datasets:[{ data:data.openInterest, backgroundColor:[colors.accent,colors.accent2,'#8b5cf6'], borderColor:'transparent', hoverOffset:7 }] },
            options:{ ...common, cutout:'68%', plugins:{ ...common.plugins, tooltip:{ callbacks:{ label:context => ` ${context.label}: $${new Intl.NumberFormat().format(context.raw)}M` } } } },
        });
        chartInstances.funding = new Chart(fundingCanvas, {
            type:'bar',
            data:{ labels:data.labels, datasets:[{ label:'Funding %', data:data.funding, backgroundColor:data.funding.map(value => value >= 0 ? colors.good : colors.bad), borderRadius:7, borderSkipped:false }] },
            options:{ ...common, indexAxis:'y', scales:{ x:{ grid:{ color:colors.line }, ticks:{ color:colors.muted, callback:value => `${value}%` } }, y:{ grid:{ display:false }, ticks:{ color:colors.ink } } }, plugins:{ ...common.plugins, legend:{ display:false }, tooltip:{ callbacks:{ label:context => ` ${context.raw.toFixed(4)}%` } } } },
        });
    };

    renderCharts({{ \Illuminate\Support\Js::from([
        'labels' => collect($market['venues'])->pluck('exchange')->values(),
        'openInterest' => collect($market['venues'])->pluck('open_interest_usd')->map(fn ($value) => round($value / 1000000, 2))->values(),
        'funding' => collect($market['venues'])->pluck('funding_rate')->map(fn ($value) => round($value * 100, 5))->values(),
    ]) }});
    document.querySelector('.ci-symbols')?.addEventListener('click', async event => {
        const link = event.target.closest('.ci-symbol');
        if (!link) return;
        event.preventDefault();
        content.classList.add('loading'); loader.classList.add('active');
        try {
            const response = await fetch(link.href, { headers:{ Accept:'application/json', 'X-Requested-With':'XMLHttpRequest' } });
            if (!response.ok) throw new Error();
            const data = await response.json();
            content.innerHTML = data.html;
            renderCharts(data.chart);
            document.querySelectorAll('.ci-symbol').forEach(item => item.classList.toggle('active', item === link));
            history.pushState({}, '', link.href);
        } catch (_) { content.insertAdjacentHTML('afterbegin', '<div class="ci-warning">Market data could not be refreshed. Please try again.</div>'); }
        finally { content.classList.remove('loading'); loader.classList.remove('active'); }
    });
})();
</script>
@endsection
