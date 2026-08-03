@extends('layouts.app')

@section('page_title', 'Dashboard')
@section('page_subtitle', 'Your performance, risk, and exchange activity in one focused view.')

@section('content')
@php
    $currency = auth()->user()->currency ?: 'INR';
    $todayNet = $todayTrades->sum(fn($trade) => $trade->net_pnl);
    $todayFees = $todayTrades->sum('trading_fees');
    $todayWins = $todayTrades->filter(fn($trade) => $trade->net_pnl > 0)->count();
    $todayWinRate = $todayTrades->count() ? round(($todayWins / $todayTrades->count()) * 100) : 0;
    $openRisk = $openTrades->sum('risk_amount');
    $planTagged = $trades->whereNotNull('plan_followed');
    $planScore = $planTagged->count() ? round(($planTagged->where('plan_followed', true)->count() / $planTagged->count()) * 100) : 0;
    $bestStrategy = $trades->whereNotNull('strategy')->groupBy('strategy')->map(fn($items) => $items->sum(fn($trade) => $trade->net_pnl))->sortDesc();
    $bestStrategyName = $bestStrategy->keys()->first() ?: 'Not enough data';
    $bestStrategyNet = $bestStrategy->first() ?: 0;
    $sharkTrades = $trades->where('broker', 'SharkExchange')->count();
    $deltaTrades = $trades->where('broker', 'Delta Exchange')->count();
    $manualTrades = $trades->whereNull('imported_at')->count();
    $sharkPrimaryBalance = collect($sharkWallet['balances'])->first(fn($balance) => strtoupper($balance['currency']) === 'USD') ?? collect($sharkWallet['balances'])->first();
    $deltaUsdBalance = collect($deltaWallet['balances'])->first(fn($balance) => strtoupper($balance['currency']) === 'USD');
    $dashboardQuery = fn (array $period) => array_filter(array_merge(['broker' => request('broker')], $period));
    $previousWeek = $dashboardWeekStart->copy()->subWeek()->toDateString();
    $nextWeek = $dashboardWeekStart->copy()->addWeek()->toDateString();
    $previousMonth = $dashboardMonth->copy()->subMonth()->format('Y-m');
    $nextMonth = $dashboardMonth->copy()->addMonth()->format('Y-m');
@endphp

<style>
    .dashboard-grid { display:grid; gap:16px; }
    .briefing { position:relative; overflow:hidden; display:grid; grid-template-columns:minmax(0,1.45fr) minmax(320px,.55fr); gap:22px; padding:24px; border:1px solid rgba(255,122,26,.22); border-radius:18px; background:linear-gradient(135deg,rgba(255,122,26,.12),rgba(0,184,217,.065) 58%,rgba(255,255,255,.025)); }
    .briefing:after { content:""; position:absolute; width:260px; height:260px; right:-110px; top:-160px; border:1px solid rgba(0,184,217,.18); border-radius:50%; box-shadow:0 0 0 38px rgba(255,122,26,.025); pointer-events:none; }
    .briefing-kicker { display:inline-flex; align-items:center; gap:8px; color:#ffad68; text-transform:uppercase; letter-spacing:.13em; font-size:10px; font-weight:900; }
    .briefing-kicker:before { content:""; width:7px; height:7px; border-radius:50%; background:#19c7b5; box-shadow:0 0 12px #19c7b5; }
    .briefing-balance { display:inline-flex; margin-left:9px; padding-left:10px; border-left:1px solid var(--line); color:var(--muted); font-size:10px; font-weight:800; }
    .briefing h2 { margin:8px 0 7px; font-size:clamp(25px,3vw,36px); letter-spacing:-.04em; }
    .briefing-copy { margin:0; color:var(--muted); max-width:680px; font-size:15px; }
    .briefing-actions { display:flex; gap:9px; flex-wrap:wrap; margin-top:18px; }
    .exchange-health { display:grid; gap:9px; align-content:center; position:relative; z-index:1; }
    .health-row { display:grid; grid-template-columns:36px minmax(0,1fr) auto; gap:10px; align-items:center; padding:11px; border:1px solid var(--line); border-radius:12px; background:rgba(5,14,18,.38); }
    .health-icon { width:36px; height:36px; border-radius:10px; display:grid; place-items:center; color:#fff; font-size:12px; font-weight:900; }
    .health-icon.shark { background:linear-gradient(135deg,#00b8d9,#087e9a); }
    .health-icon.delta { background:linear-gradient(135deg,#ff7a1a,#dc5507); }
    .health-copy strong,.health-copy small { display:block; }
    .health-copy small { color:var(--muted); font-size:10px; }
    .health-value { text-align:right; min-width:105px; }
    .health-value strong { display:block; color:var(--ink); font-size:15px; letter-spacing:-.02em; }
    .health-value small { display:block; color:var(--muted); font-size:9px; margin-top:1px; }
    .health-value .health-state { display:inline-flex; margin-top:4px; }
    .health-state { font-size:10px; font-weight:900; padding:4px 7px; border-radius:999px; color:var(--good); background:rgba(52,211,153,.09); }
    .health-state.off { color:var(--warn); background:rgba(251,191,36,.09); }
    .kpi-grid { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:12px; }
    .kpi { position:relative; overflow:hidden; min-height:128px; padding:17px; border:1px solid var(--line); border-radius:15px; background:var(--panel-bg); }
    .kpi-top { display:flex; justify-content:space-between; align-items:center; gap:10px; color:var(--muted); font-size:10px; font-weight:900; text-transform:uppercase; letter-spacing:.1em; }
    .kpi-icon { width:30px; height:30px; border-radius:9px; display:grid; place-items:center; color:var(--accent); background:var(--soft); }
    .kpi-value { margin-top:13px; font-size:clamp(23px,2.5vw,31px); font-weight:900; letter-spacing:-.045em; }
    .kpi-foot { color:var(--muted); font-size:11px; margin-top:3px; }
    .main-insights { display:grid; grid-template-columns:minmax(0,1.6fr) minmax(300px,.7fr); gap:16px; }
    .report-section { padding:18px; border:1px solid var(--line); border-radius:17px; background:var(--panel-bg); }
    .daily-plan { display:grid; grid-template-columns:minmax(0,.38fr) minmax(0,.62fr); gap:18px; align-items:stretch; padding:18px; border:1px solid rgba(0,184,217,.2); border-radius:17px; background:linear-gradient(135deg,rgba(0,184,217,.075),rgba(255,122,26,.045)); }
    .daily-plan-copy { padding:6px 2px; }
    .daily-plan-copy h2 { margin:6px 0 7px; font-size:21px; }
    .daily-plan-date { display:inline-flex; padding:5px 8px; border-radius:999px; color:#67dff1; background:rgba(0,184,217,.09); font-size:9px; font-weight:900; text-transform:uppercase; letter-spacing:.11em; }
    .daily-plan-navigation { display:flex; align-items:center; gap:7px; margin-bottom:6px; }
    .plan-nav-button { width:30px; height:30px; display:grid; place-items:center; border:1px solid var(--line); border-radius:8px; color:var(--ink); background:rgba(255,255,255,.045); font-size:16px; cursor:pointer; transition:.18s ease; }
    .plan-nav-button:hover:not(:disabled) { border-color:rgba(24,199,255,.45); color:#18c7ff; background:rgba(24,199,255,.08); }
    .plan-nav-button:disabled { opacity:.3; cursor:not-allowed; }
    .daily-plan-copy p { color:var(--muted); margin:0; font-size:12px; max-width:360px; }
    .plan-prompts { display:flex; gap:6px; flex-wrap:wrap; margin-top:13px; }
    .plan-prompts span { padding:5px 7px; border:1px solid var(--line); border-radius:8px; color:var(--muted); font-size:9px; font-weight:800; background:rgba(255,255,255,.025); }
    .daily-plan-form { display:grid; gap:9px; }
    .daily-plan-form button.is-loading { cursor:wait; opacity:.82; }
    .plan-submit-spinner { display:none; width:16px; height:16px; margin-right:7px; vertical-align:-3px; border:2px solid rgba(255,255,255,.4); border-top-color:#fff; border-radius:50%; animation:plan-spin .7s linear infinite; }
    .daily-plan-form button.is-loading .plan-submit-spinner { display:inline-block; }
    @keyframes plan-spin { to { transform:rotate(360deg); } }
    .sr-only { position:absolute; width:1px; height:1px; padding:0; margin:-1px; overflow:hidden; clip:rect(0,0,0,0); white-space:nowrap; border:0; }
    .daily-plan-form textarea { min-height:138px; resize:vertical; border-radius:13px; line-height:1.6; padding:13px 14px; }
    .plan-form-foot { display:flex; align-items:center; justify-content:space-between; gap:12px; }
    .plan-count { color:var(--muted); font-size:10px; }
    .report-toolbar { display:flex; justify-content:space-between; align-items:center; gap:14px; margin-bottom:15px; }
    .report-toolbar h2 { margin:0 0 3px; }
    .exchange-switch { display:inline-flex; gap:4px; padding:4px; border:1px solid var(--line); border-radius:12px; background:rgba(255,255,255,.035); }
    .exchange-switch button { border:0; border-radius:9px; padding:8px 13px; color:var(--muted); background:transparent; font:inherit; font-size:11px; font-weight:900; cursor:pointer; }
    .exchange-switch button.active[data-exchange="shark"] { color:#fff; background:linear-gradient(135deg,#00b8d9,#087e9a); }
    .exchange-switch button.active[data-exchange="delta"] { color:#fff; background:linear-gradient(135deg,#ff7a1a,#dc5507); }
    .exchange-report[hidden] { display:none; }
    .report-grid { display:grid; grid-template-columns:minmax(0,.82fr) minmax(0,1.18fr); gap:12px; }
    .report-card { padding:16px; border:1px solid var(--line); border-radius:14px; background:rgba(255,255,255,.025); }
    .report-card-head { display:flex; justify-content:space-between; gap:12px; align-items:flex-start; margin-bottom:14px; }
    .report-card-heading { display:flex; align-items:flex-start; justify-content:space-between; gap:10px; }
    .report-nav { display:inline-flex; gap:4px; flex-shrink:0; }
    .report-nav-button { width:26px; height:26px; display:grid; place-items:center; border:1px solid var(--line); border-radius:7px; color:var(--ink); background:rgba(255,255,255,.04); font-size:15px; line-height:1; text-decoration:none; transition:.18s ease; }
    .report-nav-button:hover { border-color:rgba(24,199,255,.45); color:#18c7ff; background:rgba(24,199,255,.08); }
    .report-section { position:relative; }
    .report-section.is-loading { pointer-events:none; }
    .report-section.is-loading:before { content:""; position:absolute; z-index:20; inset:0; border-radius:17px; background:rgba(2,8,11,.62); backdrop-filter:blur(2px); }
    html[data-theme="light"] .report-section.is-loading:before { background:rgba(255,255,255,.7); }
    @keyframes dashboard-spin { to { transform:rotate(360deg); } }
    .dashboard-ajax-loader { position:fixed; z-index:85; top:82px; left:50%; display:flex; align-items:center; gap:10px; padding:10px 15px; border:1px solid color-mix(in srgb,var(--accent) 35%,var(--line)); border-radius:999px; color:var(--ink); background:color-mix(in srgb,var(--panel) 95%,transparent); box-shadow:0 14px 38px rgba(0,0,0,.25); opacity:0; visibility:hidden; transform:translate(-50%,-7px); transition:.15s; pointer-events:none; backdrop-filter:blur(12px); }
    .dashboard-ajax-loader.active { opacity:1; visibility:visible; transform:translate(-50%,0); }
    .dashboard-ajax-loader-spinner { width:17px; height:17px; border:2px solid color-mix(in srgb,var(--accent) 22%,transparent); border-top-color:var(--accent); border-right-color:var(--accent-2); border-radius:50%; animation:dashboard-spin .65s linear infinite; }
    .report-card-head strong,.report-card-head small { display:block; }
    .report-card-head strong { font-size:15px; }
    .report-card-head small { color:var(--muted); margin-top:2px; }
    .report-period { color:var(--muted); font-size:10px; font-weight:800; }
    .report-net { font-size:28px; line-height:1; font-weight:900; letter-spacing:-.045em; margin-bottom:14px; }
    .report-stats { display:grid; grid-template-columns:repeat(3,1fr); gap:7px; }
    .report-stat { padding:9px; border-radius:10px; background:rgba(255,255,255,.035); }
    .report-stat span,.report-stat strong { display:block; }
    .report-stat span { color:var(--muted); font-size:9px; text-transform:uppercase; letter-spacing:.08em; }
    .report-stat strong { margin-top:3px; font-size:14px; }
    .month-layout { display:grid; grid-template-columns:minmax(190px,.55fr) minmax(260px,.45fr); gap:16px; align-items:center; }
    .activity-calendar { display:grid; grid-template-columns:repeat(7,1fr); gap:5px; }
    .activity-day { position:relative; aspect-ratio:1; min-width:0; border-radius:5px; display:grid; place-items:center; color:var(--muted); background:rgba(255,255,255,.07); font-size:8px; font-weight:800; }
    .activity-day.win { color:#002d20; background:#00e6a8; box-shadow:0 0 14px rgba(0,230,168,.18); }
    .activity-day.loss { color:#fff; background:#ff3b4f; box-shadow:0 0 14px rgba(255,59,79,.18); }
    .activity-day.flat { color:#392100; background:#ffad33; }
    .weekly-calendar { display:grid; grid-template-columns:repeat(7,minmax(0,1fr)); gap:7px; margin-top:14px; overflow:visible; }
    .weekly-day { position:relative; min-width:0; aspect-ratio:1.3; padding:5px; border-radius:8px; display:flex; flex-direction:column; align-items:center; justify-content:center; gap:2px; text-align:center; color:var(--muted); background:rgba(255,255,255,.07); }
    .weekly-day span { display:block; }
    .weekly-day strong,.weekly-day small { display:none; }
    .weekly-day span { font-size:9px; text-transform:uppercase; letter-spacing:.08em; font-weight:900; }
    .weekly-day .weekly-date { font-size:8px; text-transform:none; letter-spacing:0; font-weight:700; opacity:.78; }
    .weekly-day strong { margin:6px 0 2px; padding:0 2px; font-size:9px; color:var(--ink); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
    .weekly-day small { font-size:8px; opacity:.8; }
    .weekly-day.win { color:#002d20; background:#00e6a8; box-shadow:0 0 16px rgba(0,230,168,.18); }
    .weekly-day.loss { color:#fff; background:#ff3b4f; box-shadow:0 0 16px rgba(255,59,79,.18); }
    .weekly-day.flat { color:#392100; background:#ffad33; }
    .weekly-day.win strong,.weekly-day.loss strong,.weekly-day.flat strong { color:inherit; }
    .weekly-day[data-tooltip]:is(:hover,:focus),.activity-day[data-tooltip]:is(:hover,:focus) { z-index:30; outline:2px solid rgba(24,199,255,.55); outline-offset:2px; }
    .weekly-day[data-tooltip]:is(:hover,:focus):after,.activity-day[data-tooltip]:is(:hover,:focus):after {
        content:attr(data-tooltip);
        position:absolute;
        left:50%;
        bottom:calc(100% + 9px);
        transform:translateX(-50%);
        width:max-content;
        max-width:220px;
        padding:8px 10px;
        border:1px solid rgba(255,122,26,.28);
        border-radius:8px;
        color:#f7fbfc;
        background:#081419;
        box-shadow:0 12px 34px rgba(0,0,0,.36);
        font-size:10px;
        font-weight:700;
        line-height:1.35;
        white-space:pre-line;
        text-align:left;
        pointer-events:none;
    }
    .weekly-day:nth-child(7n+1):is(:hover,:focus):after,.activity-day:nth-child(7n+1):is(:hover,:focus):after { left:0; transform:none; }
    .weekly-day:nth-child(7n):is(:hover,:focus):after,.activity-day:nth-child(7n):is(:hover,:focus):after { left:auto; right:0; transform:none; }
    .calendar-legend { display:flex; gap:10px; flex-wrap:wrap; color:var(--muted); font-size:9px; margin-top:9px; }
    .calendar-legend i { width:7px; height:7px; border-radius:2px; display:inline-block; margin-right:4px; }
    .panel-head { display:flex; justify-content:space-between; align-items:flex-start; gap:14px; margin-bottom:15px; }
    .panel-head h2 { margin:0 0 3px; }
    .period-pill { padding:5px 8px; border-radius:999px; color:var(--muted); background:var(--soft); font-size:10px; font-weight:800; }
    .chart-wrap { height:275px; position:relative; }
    .chart-wrap canvas { width:100% !important; height:100% !important; }
    .process-list { display:grid; gap:13px; }
    .process-item { padding-bottom:13px; border-bottom:1px solid var(--line); }
    .process-item:last-child { border-bottom:0; padding-bottom:0; }
    .process-meta { display:flex; justify-content:space-between; gap:12px; align-items:center; }
    .process-meta span { color:var(--muted); font-size:11px; font-weight:800; }
    .process-meta strong { font-size:13px; }
    .progress { height:7px; border-radius:999px; background:rgba(255,255,255,.06); margin-top:8px; overflow:hidden; }
    .progress span { display:block; height:100%; border-radius:inherit; background:linear-gradient(90deg,#ff7a1a,#00b8d9); }
    .source-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:7px; margin-top:8px; }
    .source { padding:9px; border-radius:10px; background:rgba(255,255,255,.035); text-align:center; }
    .source strong,.source small { display:block; }
    .source strong { font-size:17px; }
    .source small { color:var(--muted); font-size:9px; text-transform:uppercase; letter-spacing:.08em; }
    .tables-grid { display:grid; grid-template-columns:minmax(0,1.3fr) minmax(330px,.7fr); gap:16px; }
    .trade-symbol { display:flex; align-items:center; gap:9px; }
    .symbol-dot { width:30px; height:30px; border-radius:9px; display:grid; place-items:center; color:#fff; background:linear-gradient(135deg,rgba(255,122,26,.9),rgba(0,184,217,.8)); font-size:10px; font-weight:900; }
    .table-link { color:var(--accent-2); font-size:11px; font-weight:800; }
    .empty-state { padding:28px 12px !important; text-align:center !important; color:var(--muted); }
    @media(max-width:1150px){ .briefing,.main-insights,.tables-grid{grid-template-columns:1fr;} .kpi-grid{grid-template-columns:repeat(2,1fr);} .report-grid{grid-template-columns:1fr;} }
    @media(max-width:760px){ .report-toolbar{align-items:flex-start;flex-direction:column;} .month-layout,.daily-plan{grid-template-columns:1fr;} }
    @media(max-width:620px){ .kpi-grid{grid-template-columns:1fr;} .briefing{padding:18px;} .source-grid{grid-template-columns:1fr;} }
</style>

<div class="dashboard-ajax-loader" id="dashboardReportsLoader" role="status" aria-live="polite" aria-hidden="true"><span class="dashboard-ajax-loader-spinner"></span><strong>Updating reports</strong></div>
<div class="dashboard-grid">
    <section class="briefing">
        <div>
            <span class="briefing-kicker">Trading desk live</span><span class="briefing-balance">Journal balance {{ $currency }} {{ number_format($stats['balance'], 2) }}</span>
            <h2>Good {{ now()->hour < 12 ? 'morning' : (now()->hour < 17 ? 'afternoon' : 'evening') }}, {{ explode(' ', auth()->user()->name)[0] }}.</h2>
            <p class="briefing-copy">
                @if($todayTrades->count())
                    You have closed {{ $todayTrades->count() }} {{ Str::plural('trade', $todayTrades->count()) }} today with a <strong class="{{ $todayNet >= 0 ? 'positive' : 'negative' }}">{{ $todayNet >= 0 ? 'positive' : 'negative' }} {{ $currency }} {{ number_format(abs($todayNet), 2) }}</strong> result.
                @else
                    No trades recorded today. Review your plan before the next setup and protect your risk budget.
                @endif
            </p>
            <div class="briefing-actions">
                <a class="btn secondary" href="{{ route('trades.analysis') }}"><svg class="icon"><use href="#icon-week"></use></svg>Review reports</a>
            </div>
        </div>
        <div class="exchange-health">
            <div class="health-row">
                <span class="health-icon shark">S</span>
                <span class="health-copy"><strong>Shark Exchange</strong><small>{{ $sharkTrades }} journal trades · {{ $sharkWallet['synced_at'] ? 'Synced '.$sharkWallet['synced_at']->diffForHumans() : 'Not synced' }}</small></span>
                <span class="health-value"><strong>{{ $sharkPrimaryBalance ? $sharkPrimaryBalance['currency'].' '.number_format($sharkPrimaryBalance['balance'], 2) : '—' }}</strong><span class="health-state {{ $account && $account->api_key ? '' : 'off' }}">{{ $account && $account->api_key ? 'Connected' : 'Setup' }}</span></span>
            </div>
            <div class="health-row">
                <span class="health-icon delta">D</span>
                <span class="health-copy"><strong>Delta Exchange</strong><small>{{ $deltaTrades }} journal trades · {{ $deltaWallet['synced_at'] ? 'Synced '.$deltaWallet['synced_at']->diffForHumans() : 'Not synced' }}</small></span>
                <span class="health-value"><strong>{{ $deltaUsdBalance ? 'USD '.number_format($deltaUsdBalance['balance'], 2) : '—' }}</strong>@if($deltaUsdBalance && $deltaUsdBalance['available'] !== null)<small>Available {{ number_format($deltaUsdBalance['available'], 2) }}</small>@endif<span class="health-state {{ $deltaAccount && $deltaAccount->api_key ? '' : 'off' }}">{{ $deltaAccount && $deltaAccount->api_key ? 'Connected' : 'Setup' }}</span></span>
            </div>
        </div>
    </section>

    <section class="daily-plan" aria-labelledby="dailyPlanTitle">
        <div class="daily-plan-copy">
            <div class="daily-plan-navigation"><button class="plan-nav-button" id="dailyPlanPrevious" type="button" aria-label="Load previous day's trading plan">←</button><span class="daily-plan-date" id="dailyPlanDate">{{ now()->format('l, d F Y') }}</span><button class="plan-nav-button" id="dailyPlanNext" type="button" aria-label="Load next day's trading plan" disabled>→</button></div>
            <h2 id="dailyPlanTitle">Today's trading plan</h2>
            <p id="dailyPlanDescription">Write the rules and intentions you want visible before every decision today.</p>
            <div class="plan-prompts"><span>Priority setups</span><span>Maximum risk</span><span>No-trade conditions</span><span>Mindset reminder</span></div>
        </div>
        <form class="daily-plan-form" id="dailyPlanForm" method="POST" action="{{ route('dashboard.daily-plan.save') }}" data-load-url="{{ route('dashboard.daily-plan.show') }}">
            @csrf
            <input id="dailyPlanSelectedDate" name="plan_date" type="hidden" value="{{ now()->toDateString() }}">
            <label class="sr-only" for="dailyPlanContent">Today's trading plan</label>
            <textarea id="dailyPlanContent" name="content" maxlength="5000" placeholder="Example: Wait for confirmation at key levels. Risk no more than 1% per setup. Stop after two consecutive losses...">{{ old('content', $dailyPlan?->content) }}</textarea>
            <div class="plan-form-foot"><span class="plan-count"><span id="dailyPlanCount">0</span> / 5000 characters</span><button class="btn" type="submit"><span class="plan-submit-spinner" aria-hidden="true"></span><span class="plan-submit-label">Save plan</span></button></div>
        </form>
    </section>

    <section class="report-section" id="exchangeReportsPanel" aria-labelledby="exchangeReportTitle">
        <div class="report-toolbar">
            <div><h2 id="exchangeReportTitle">Exchange reports</h2><span class="muted">Switch exchange to compare this week and month</span></div>
            <div class="exchange-switch" role="group" aria-label="Select exchange">
                <button type="button" class="active" data-exchange="shark" aria-pressed="true">Shark Exchange</button>
                <button type="button" data-exchange="delta" aria-pressed="false">Delta Exchange</button>
            </div>
        </div>
        @foreach(['shark' => 'Shark Exchange', 'delta' => 'Delta Exchange'] as $exchangeKey => $exchangeName)
            @php($report = $exchangeReports[$exchangeKey])
            <div class="exchange-report" data-report="{{ $exchangeKey }}" {{ $exchangeKey === 'delta' ? 'hidden' : '' }}>
                <div class="report-grid">
                    <article class="report-card">
                        <div class="report-card-head"><span><strong>Weekly report</strong><small>{{ $exchangeName }}</small></span><span class="report-card-heading"><span class="report-period">{{ $report['week']['label'] }}</span><span class="report-nav" aria-label="Weekly report navigation"><a class="report-nav-button" href="{{ route('dashboard', $dashboardQuery(['dashboard_week' => $previousWeek, 'dashboard_month' => $dashboardMonth->format('Y-m')])) }}" aria-label="Previous week">&#8592;</a><a class="report-nav-button" href="{{ route('dashboard', $dashboardQuery(['dashboard_week' => $nextWeek, 'dashboard_month' => $dashboardMonth->format('Y-m')])) }}" aria-label="Next week">&#8594;</a></span></span></div>
                        <div class="report-net {{ $report['week']['net'] >= 0 ? 'positive' : 'negative' }}">{{ $report['currency'] }} {{ number_format($report['week']['net'], 2) }}</div>
                        <div class="report-stats">
                            <div class="report-stat"><span>Trades</span><strong>{{ $report['week']['total'] }}</strong></div>
                            <div class="report-stat"><span>Win rate</span><strong>{{ number_format($report['week']['win_rate'], 1) }}%</strong></div>
                            <div class="report-stat"><span>Fees</span><strong>{{ number_format($report['week']['fees'], 2) }}</strong></div>
                        </div>
                        <div class="weekly-calendar" aria-label="{{ $exchangeName }} weekly daily activity">
                            @foreach($report['week']['days'] as $day)
                                <span class="weekly-day {{ $day['trades'] ? ($day['net'] > 0 ? 'win' : ($day['net'] < 0 ? 'loss' : 'flat')) : '' }}" data-tooltip="{{ $day['date'] }} · {{ $day['trades'] }} {{ Str::plural('trade', $day['trades']) }} · {{ $day['net'] >= 0 ? 'Profit' : 'Loss' }} {{ $report['currency'] }} {{ number_format(abs($day['net']), 2) }}" aria-label="{{ $day['date'] }}: {{ $day['trades'] }} trades, {{ $report['currency'] }} {{ number_format($day['net'], 2) }}">
                                    <span>{{ $day['label'] }}</span><span class="weekly-date">{{ $day['date'] }}</span><strong>{{ $day['trades'] ? $report['currency'].' '.number_format(abs($day['net']), 2) : '—' }}</strong><small>{{ $day['date'] }} · {{ $day['trades'] }} {{ Str::plural('trade', $day['trades']) }}</small>
                                </span>
                            @endforeach
                        </div>
                    </article>
                    <article class="report-card">
                        <div class="report-card-head"><span><strong>Monthly report</strong><small>{{ $exchangeName }}</small></span><span class="report-card-heading"><span class="report-period">{{ $report['month']['label'] }}</span><span class="report-nav" aria-label="Monthly report navigation"><a class="report-nav-button" href="{{ route('dashboard', $dashboardQuery(['dashboard_week' => $dashboardWeekStart->toDateString(), 'dashboard_month' => $previousMonth])) }}" aria-label="Previous month">&#8592;</a><a class="report-nav-button" href="{{ route('dashboard', $dashboardQuery(['dashboard_week' => $dashboardWeekStart->toDateString(), 'dashboard_month' => $nextMonth])) }}" aria-label="Next month">&#8594;</a></span></span></div>
                        <div class="month-layout">
                            <div>
                                <div class="report-net {{ $report['month']['net'] >= 0 ? 'positive' : 'negative' }}">{{ $report['currency'] }} {{ number_format($report['month']['net'], 2) }}</div>
                                <div class="report-stats">
                                    <div class="report-stat"><span>Trades</span><strong>{{ $report['month']['total'] }}</strong></div>
                                    <div class="report-stat"><span>Win rate</span><strong>{{ number_format($report['month']['win_rate'], 1) }}%</strong></div>
                                    <div class="report-stat"><span>Fees</span><strong>{{ number_format($report['month']['fees'], 2) }}</strong></div>
                                </div>
                            </div>
                            <div>
                                <div class="activity-calendar" aria-label="{{ $exchangeName }} daily monthly activity">
                                    @foreach($report['month']['days'] as $day)
                                        <span class="activity-day {{ $day['trades'] ? ($day['net'] > 0 ? 'win' : ($day['net'] < 0 ? 'loss' : 'flat')) : '' }}" @if($day['trades']) tabindex="0" data-tooltip="Date: {{ $day['date'] }}&#10;{{ $day['net'] > 0 ? 'Profit' : ($day['net'] < 0 ? 'Loss' : 'Breakeven') }}: {{ $report['currency'] }} {{ number_format(abs($day['net']), 2) }}&#10;Fees: {{ $report['currency'] }} {{ number_format($day['fees'], 2) }}&#10;Trades: {{ $day['trades'] }}" @endif aria-label="{{ $day['date'] }}: {{ $day['trades'] }} trades, {{ $report['currency'] }} {{ number_format($day['net'], 2) }}, fees {{ $report['currency'] }} {{ number_format($day['fees'], 2) }}">{{ $day['day'] }}</span>
                                    @endforeach
                                </div>
                                <div class="calendar-legend"><span><i style="background:#00e6a8"></i>Profit</span><span><i style="background:#ff3b4f"></i>Loss</span><span><i style="background:rgba(255,255,255,.12)"></i>No trades</span></div>
                            </div>
                        </div>
                    </article>
                </div>
            </div>
        @endforeach
    </section>

</div>

<script>
(() => {
const dailyPlanContent = document.getElementById('dailyPlanContent');
const dailyPlanCount = document.getElementById('dailyPlanCount');
const dailyPlanForm = document.getElementById('dailyPlanForm');
const dailyPlanDate = document.getElementById('dailyPlanDate');
const dailyPlanTitle = document.getElementById('dailyPlanTitle');
const dailyPlanDescription = document.getElementById('dailyPlanDescription');
const dailyPlanSelectedDate = document.getElementById('dailyPlanSelectedDate');
const dailyPlanPrevious = document.getElementById('dailyPlanPrevious');
const dailyPlanNext = document.getElementById('dailyPlanNext');
const updateDailyPlanCount = () => { if (dailyPlanContent && dailyPlanCount) dailyPlanCount.textContent = dailyPlanContent.value.length; };
dailyPlanContent?.addEventListener('input', updateDailyPlanCount);
updateDailyPlanCount();
const loadDailyPlan = async (date) => {
    if (!dailyPlanForm || !date) return;
    const controls = [dailyPlanPrevious, dailyPlanNext].filter(Boolean);
    controls.forEach(control => control.disabled = true);
    dailyPlanContent.disabled = true;
    dailyPlanContent.placeholder = 'Loading trading plan...';

    try {
        const url = new URL(dailyPlanForm.dataset.loadUrl, window.location.href);
        url.searchParams.set('date', date);
        const response = await fetch(url, { headers: { 'Accept':'application/json', 'X-Requested-With':'XMLHttpRequest' } });
        const data = await response.json();
        if (!response.ok) throw new Error(data.message || 'The trading plan could not be loaded.');
        dailyPlanSelectedDate.value = data.date;
        dailyPlanDate.textContent = data.date_label;
        dailyPlanTitle.textContent = data.title;
        dailyPlanDescription.textContent = data.has_plan ? 'Review or update the plan saved for this day.' : 'No trading plan was recorded for this day. You can add one now.';
        dailyPlanContent.value = data.content;
        dailyPlanPrevious.dataset.date = data.previous_date;
        dailyPlanNext.dataset.date = data.next_date || '';
        dailyPlanPrevious.disabled = false;
        dailyPlanNext.disabled = !data.next_date;
        updateDailyPlanCount();
    } catch (error) {
        window.showAppToast?.('error', 'Plan unavailable', error.message || 'The trading plan could not be loaded.');
    } finally {
        dailyPlanContent.disabled = false;
        dailyPlanContent.placeholder = 'Example: Wait for confirmation at key levels. Risk no more than 1% per setup. Stop after two consecutive losses...';
    }
};
dailyPlanPrevious?.addEventListener('click', () => loadDailyPlan(dailyPlanPrevious.dataset.date));
dailyPlanNext?.addEventListener('click', () => loadDailyPlan(dailyPlanNext.dataset.date));
if (dailyPlanPrevious && dailyPlanSelectedDate) {
    const initialDate = new Date(`${dailyPlanSelectedDate.value}T12:00:00`);
    initialDate.setDate(initialDate.getDate() - 1);
    dailyPlanPrevious.dataset.date = initialDate.toISOString().slice(0, 10);
}
dailyPlanForm?.addEventListener('submit', async (event) => {
    event.preventDefault();
    const button = dailyPlanForm.querySelector('button[type="submit"]');
    const label = button?.querySelector('.plan-submit-label');
    if (!button || button.disabled) return;

    button.disabled = true;
    button.classList.add('is-loading');
    button.setAttribute('aria-busy', 'true');
    if (label) label.textContent = 'Saving...';

    try {
        const response = await fetch(dailyPlanForm.action, {
            method: 'POST',
            body: new FormData(dailyPlanForm),
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        });
        const data = await response.json();
        if (!response.ok) {
            const validationMessage = data.errors ? Object.values(data.errors).flat()[0] : null;
            throw new Error(validationMessage || data.message || 'The trading plan could not be saved.');
        }
        window.showAppToast?.('success', 'Plan saved', data.message || "Today's trading plan has been saved.");
    } catch (error) {
        window.showAppToast?.('error', 'Save failed', error.message || 'The trading plan could not be saved.');
    } finally {
        button.disabled = false;
        button.classList.remove('is-loading');
        button.removeAttribute('aria-busy');
        if (label) label.textContent = 'Save plan';
    }
});
let exchangeReportRequestController = null;
const dashboardReportsLoader = document.getElementById('dashboardReportsLoader');
const setActiveExchange = (exchange) => {
    const panel = document.getElementById('exchangeReportsPanel');
    if (!panel) return;
    panel.querySelectorAll('.exchange-switch button').forEach(button => {
        const selected = button.dataset.exchange === exchange;
        button.classList.toggle('active', selected);
        button.setAttribute('aria-pressed', selected ? 'true' : 'false');
    });
    panel.querySelectorAll('.exchange-report').forEach(report => {
        report.hidden = report.dataset.report !== exchange;
    });
};
const activeExchange = () => document.querySelector('#exchangeReportsPanel .exchange-switch button.active')?.dataset.exchange || 'shark';
const loadExchangeReports = async (url, updateHistory = true) => {
    const panel = document.getElementById('exchangeReportsPanel');
    if (!panel) return;
    const exchange = activeExchange();
    exchangeReportRequestController?.abort();
    const requestController = new AbortController();
    exchangeReportRequestController = requestController;
    const loadingStartedAt = performance.now();
    panel.classList.add('is-loading');
    panel.setAttribute('aria-busy', 'true');
    dashboardReportsLoader?.classList.add('active');
    dashboardReportsLoader?.setAttribute('aria-hidden', 'false');

    try {
        await new Promise((resolve) => requestAnimationFrame(() => requestAnimationFrame(resolve)));
        const response = await fetch(url, {
            headers: { 'Accept': 'text/html', 'X-Requested-With': 'XMLHttpRequest' },
            signal: requestController.signal,
        });
        if (!response.ok) throw new Error('The exchange reports could not be loaded.');
        const page = new DOMParser().parseFromString(await response.text(), 'text/html');
        const nextPanel = page.getElementById('exchangeReportsPanel');
        if (!nextPanel) throw new Error('The exchange reports response was incomplete.');
        panel.innerHTML = nextPanel.innerHTML;
        setActiveExchange(exchange);
        if (updateHistory) history.pushState({ exchangeReports: true }, '', url);
    } catch (error) {
        if (error.name !== 'AbortError') window.showAppToast?.('error', 'Reports unavailable', error.message || 'The exchange reports could not be loaded.');
    } finally {
        if (exchangeReportRequestController === requestController) {
            const remainingLoaderTime = Math.max(0, 450 - (performance.now() - loadingStartedAt));
            if (remainingLoaderTime) await new Promise((resolve) => setTimeout(resolve, remainingLoaderTime));
            panel.classList.remove('is-loading');
            panel.removeAttribute('aria-busy');
            dashboardReportsLoader?.classList.remove('active');
            dashboardReportsLoader?.setAttribute('aria-hidden', 'true');
        }
    }
};
document.addEventListener('click', (event) => {
    const exchangeButton = event.target.closest('#exchangeReportsPanel .exchange-switch button');
    if (exchangeButton) {
        setActiveExchange(exchangeButton.dataset.exchange);
        return;
    }
    const link = event.target.closest('#exchangeReportsPanel .report-nav-button[href]');
    if (!link || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;
    event.preventDefault();
    loadExchangeReports(link.href);
}, { signal: window.tradeYatraNavigationSignal });
window.addEventListener('popstate', () => {
    if (window.location.pathname.endsWith('/dashboard')) loadExchangeReports(window.location.href, false);
}, { signal: window.tradeYatraNavigationSignal });
})();
</script>
@endsection
