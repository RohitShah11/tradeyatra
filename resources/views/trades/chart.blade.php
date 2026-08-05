@extends('layouts.app')

@section('page_title', $trade->pair.' Trade Chart')
@section('page_subtitle', 'Review the real market around your entry, planned risk, target, and exit.')

@push('styles')
<style>
    .trade-chart-page { display:grid; gap:14px; }
    .trade-chart-actions { display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap; }
    .trade-chart-actions > div { display:flex; gap:8px; flex-wrap:wrap; }
    .trade-chart-summary { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:10px; }
    .trade-chart-stat { padding:14px 16px; border:1px solid var(--line); border-radius:13px; background:var(--panel-bg); }
    .trade-chart-stat span,.trade-chart-stat strong,.trade-chart-stat small { display:block; }
    .trade-chart-stat span { color:var(--muted); font-size:9px; font-weight:800; letter-spacing:.1em; text-transform:uppercase; }
    .trade-chart-stat strong { margin-top:5px; font-size:19px; }
    .trade-chart-stat small { margin-top:3px; color:var(--muted); font-size:10px; }
    .trade-chart-panel { padding:0; overflow:hidden; }
    .trade-chart-toolbar { display:flex; justify-content:space-between; align-items:center; gap:14px; padding:13px 16px; border-bottom:1px solid var(--line); }
    .trade-chart-identity strong,.trade-chart-identity small { display:block; }
    .trade-chart-identity small { margin-top:2px; color:var(--muted); font-size:10px; }
    .trade-chart-controls { display:flex; align-items:center; gap:9px; }
    .trade-chart-controls label { margin:0; color:var(--muted); font-size:10px; font-weight:800; text-transform:uppercase; }
    .trade-chart-controls select { width:auto; min-width:84px; padding:8px 31px 8px 10px; }
    .trade-chart-stage { position:relative; min-height:540px; background:color-mix(in srgb,var(--panel) 92%,var(--bg)); }
    #tradeReviewChart { width:100%; height:540px; }
    .trade-chart-state { position:absolute; z-index:4; inset:0; display:grid; place-items:center; padding:28px; color:var(--muted); background:color-mix(in srgb,var(--panel) 86%,transparent); text-align:center; }
    .trade-chart-state[hidden] { display:none; }
    .trade-chart-state strong,.trade-chart-state span { display:block; }
    .trade-chart-state strong { color:var(--ink); margin-bottom:5px; }
    .trade-chart-spinner { width:34px; height:34px; margin:0 auto 12px; border:3px solid color-mix(in srgb,var(--accent) 20%,transparent); border-top-color:var(--accent); border-right-color:var(--accent-2); border-radius:50%; animation:trade-chart-spin .7s linear infinite; }
    @keyframes trade-chart-spin { to { transform:rotate(360deg); } }
    .trade-chart-legend { display:flex; align-items:center; gap:14px; flex-wrap:wrap; padding:11px 16px; border-top:1px solid var(--line); color:var(--muted); font-size:10px; }
    .trade-chart-legend span { display:inline-flex; align-items:center; gap:6px; }
    .trade-chart-swatch { width:18px; height:3px; border-radius:4px; background:var(--accent); }
    .trade-chart-swatch.stop { background:var(--bad); }.trade-chart-swatch.target{background:var(--good)}.trade-chart-swatch.exit{background:#b889ff}
    .trade-chart-source { margin-left:auto; }
    .trade-chart-notice { display:flex; justify-content:space-between; align-items:center; gap:14px; padding:13px 15px; border:1px solid color-mix(in srgb,var(--warn) 35%,var(--line)); border-radius:12px; background:color-mix(in srgb,var(--warn) 8%,var(--panel)); }
    .trade-chart-notice strong,.trade-chart-notice span { display:block; }.trade-chart-notice span{margin-top:2px;color:var(--muted);font-size:11px}
    .trade-chart-warning { padding:9px 16px; color:var(--warn); border-top:1px solid color-mix(in srgb,var(--warn) 24%,var(--line)); font-size:10px; }
    .trade-chart-warning:empty { display:none; }
    @media(max-width:950px){.trade-chart-summary{grid-template-columns:repeat(2,minmax(0,1fr))}}
    @media(max-width:620px){.trade-chart-summary{grid-template-columns:1fr}.trade-chart-toolbar{align-items:flex-start;flex-direction:column}.trade-chart-stage,#tradeReviewChart{min-height:430px;height:430px}.trade-chart-source{width:100%;margin-left:0}.trade-chart-notice{align-items:flex-start;flex-direction:column}}
</style>
@endpush

@section('content')
@php
    $entry = $trade->entry_price !== null ? (float) $trade->entry_price : null;
    $stop = $trade->planned_stop_loss !== null ? (float) $trade->planned_stop_loss : null;
    $target = $trade->planned_take_profit !== null ? (float) $trade->planned_take_profit : null;
    $risk = $entry !== null && $stop !== null ? abs($entry - $stop) : null;
    $reward = $entry !== null && $target !== null ? abs($target - $entry) : null;
    $plannedRr = $risk && $reward !== null ? $reward / $risk : null;
    $netPnl = $trade->net_pnl;
    $resultLabel = $netPnl > 0 ? 'Profitable trade' : ($netPnl < 0 ? 'Loss trade' : 'Break-even');
    $missingPlan = $entry === null || $stop === null || $target === null || ! $trade->time;
@endphp

<div class="trade-chart-page">
    <div class="trade-chart-actions">
        <a class="btn secondary" href="{{ route('trades.index') }}">Back to journal</a>
        <div><a class="btn secondary" href="{{ route('trades.edit', $trade) }}">Edit trade levels</a></div>
    </div>

    @if($missingPlan)
        <section class="trade-chart-notice" role="status">
            <div><strong>Complete the trade plan to see every marker and level</strong><span>Add the entry time and price, original stop-loss, and original take-profit. Existing values will never be changed by this chart.</span></div>
            <a class="btn secondary" href="{{ route('trades.edit', $trade) }}">Add missing levels</a>
        </section>
    @endif

    <section class="trade-chart-summary" aria-label="Trade result summary">
        <div class="trade-chart-stat"><span>Direction</span><strong class="{{ $trade->trade_type === 'Long' ? 'positive' : 'negative' }}">{{ strtoupper($trade->trade_type) }}</strong><small>{{ $trade->broker ?: 'Manual trade' }}</small></div>
        <div class="trade-chart-stat"><span>Planned R:R</span><strong>{{ $plannedRr !== null ? '1 : '.number_format($plannedRr, 2) : 'Not available' }}</strong><small>From original entry, SL and TP</small></div>
        <div class="trade-chart-stat"><span>Actual result</span><strong class="{{ $netPnl >= 0 ? 'positive' : 'negative' }}">{{ $trade->currency }} {{ number_format($netPnl, 2) }}</strong><small>{{ $resultLabel }} after recorded fees</small></div>
        <div class="trade-chart-stat"><span>Execution</span><strong>{{ $entry !== null ? number_format($entry, 2) : '—' }} → {{ $trade->exit_price !== null ? number_format((float)$trade->exit_price, 2) : '—' }}</strong><small>{{ optional($trade->date)->format('d M Y') }} · {{ $trade->time ? substr($trade->time,0,5) : 'Time missing' }}</small></div>
    </section>

    <section class="panel trade-chart-panel">
        <header class="trade-chart-toolbar">
            <div class="trade-chart-identity"><strong>{{ $trade->pair }} · {{ strtoupper($trade->trade_type) }}</strong><small>Read-only market chart · no order placement</small></div>
            <div class="trade-chart-controls"><label for="tradeChartInterval">Timeframe</label><select id="tradeChartInterval">
                @foreach($chartIntervals as $interval)<option value="{{ $interval }}" @selected($interval === '15m')>{{ $interval }}</option>@endforeach
            </select></div>
        </header>
        <div class="trade-chart-stage">
            <div id="tradeReviewChart" data-candles-url="{{ route('trades.candles', $trade) }}" role="img" aria-label="Candlestick chart showing the saved trade entry, stop-loss, take-profit, and exit"></div>
            <div class="trade-chart-state" id="tradeChartState"><div><span class="trade-chart-spinner"></span><strong>Loading market candles</strong><span>Preparing the trade view…</span></div></div>
        </div>
        <div class="trade-chart-warning" id="tradeChartWarning"></div>
        <footer class="trade-chart-legend">
            <span><i class="trade-chart-swatch"></i>Entry</span><span><i class="trade-chart-swatch stop"></i>Planned SL</span><span><i class="trade-chart-swatch target"></i>Planned TP</span><span><i class="trade-chart-swatch exit"></i>Actual exit</span><span class="trade-chart-source" id="tradeChartSource">Public read-only candles</span>
        </footer>
    </section>
</div>

<script type="application/json" id="tradeChartPayload">{!! json_encode($chartTrade, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}</script>
@vite('resources/js/trade-chart.js')
@endsection
