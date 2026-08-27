@extends('layouts.admin')
@section('title',$user->name)
@section('content')
@php
$duration = function (int $seconds): string {
    if ($seconds < 60) return $seconds.' sec';
    if ($seconds < 3600) return floor($seconds / 60).' min';
    return floor($seconds / 3600).' hr '.floor(($seconds % 3600) / 60).' min';
};
$activity = $user->latestActivitySession;
$presence = $activity?->presenceStatus() ?? 'offline';
@endphp
<style>.presence{display:inline-flex;align-items:center;gap:7px;padding:6px 10px;border-radius:999px;font-size:11px;font-weight:800;text-transform:capitalize}.presence:before{content:"";width:8px;height:8px;border-radius:50%}.presence.active{color:#86efac;background:rgba(34,197,94,.1)}.presence.active:before{background:#22c55e;box-shadow:0 0 0 4px rgba(34,197,94,.12)}.presence.idle{color:#fde68a;background:rgba(245,158,11,.1)}.presence.idle:before{background:#f59e0b}.presence.offline{color:#a8b6bd;background:rgba(148,163,184,.08)}.presence.offline:before{background:#64748b}.live-card{padding:18px}.live-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px;margin-top:16px}.live-item{padding:12px;border:1px solid var(--line);border-radius:9px;background:rgba(255,255,255,.025)}.live-item small,.live-item strong{display:block}.live-item small{color:var(--muted);font-size:9px;text-transform:uppercase}.live-item strong{margin-top:5px;overflow:hidden;text-overflow:ellipsis}.usage-layout{grid-template-columns:minmax(0,1.15fr) minmax(0,.85fr)}.trade-summary{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:10px;padding:0 18px 16px}.trade-summary-card{padding:13px;border:1px solid var(--line);border-radius:9px;background:rgba(255,255,255,.025)}.trade-summary-card span,.trade-summary-card small{display:block;color:var(--muted);font-size:10px}.trade-summary-card strong{display:block;margin:4px 0;font-size:18px}.trade-summary-card.profit strong{color:#86efac}.trade-summary-card.loss strong{color:#fda4af}.admin-pager{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:16px 18px}.admin-pager-info{color:var(--muted);font-size:12px}.admin-pager-actions{display:flex;gap:8px}.admin-pager .disabled{opacity:.4;pointer-events:none}@media(max-width:850px){.live-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.usage-layout{grid-template-columns:1fr}.trade-summary{grid-template-columns:1fr}}@media(max-width:520px){.live-grid{grid-template-columns:1fr}.admin-pager{align-items:flex-start;flex-direction:column}}</style>
<div class="page-head"><div><a class="muted" href="{{ route('admin.users.index') }}">← Users</a><h1>{{ $user->name }}</h1><p>{{ $user->email }}</p></div><div style="display:flex;gap:8px;align-items:center"><span class="presence {{ $presence }}">{{ $presence === 'active' ? 'Active now' : $presence }}</span><a class="btn" href="{{ route('admin.users.chat',$user) }}">Chat with user</a></div></div>

<section class="panel live-card">
    <div class="panel-head" style="margin:-18px -18px 0"><div><h2>Live activity</h2><span class="muted">Presence is refreshed by a heartbeat every 30 seconds.</span></div></div>
    <div class="live-grid">
        <div class="live-item"><small>Current page</small><strong>{{ $presence === 'offline' ? 'Not online' : ($activity?->current_path ?? 'Unknown') }}</strong></div>
        <div class="live-item"><small>Last seen</small><strong>{{ $activity?->last_seen_at?->diffForHumans() ?? 'Never' }}</strong></div>
        <div class="live-item"><small>Current session active</small><strong>{{ $duration((int) ($activity?->active_seconds ?? 0)) }}</strong></div>
        <div class="live-item"><small>Session started</small><strong>{{ $activity?->started_at?->format('d M, h:i A') ?? '—' }}</strong></div>
    </div>
</section>

<div class="grid stats" style="margin-top:16px"><div class="stat"><span>Active today</span><strong>{{ $duration($activeToday) }}</strong></div><div class="stat"><span>Active last 7 days</span><strong>{{ $duration($activeSevenDays) }}</strong></div><div class="stat"><span>Trades</span><strong>{{ number_format($user->trades_count) }}</strong></div><div class="stat"><span>AI conversations</span><strong>{{ number_format($user->ai_conversations_count) }}</strong></div></div>

<section class="panel" style="margin-bottom:16px">
    <div class="panel-head"><div><h2>Trade history</h2><span class="muted">{{ number_format($matchingTradeCount) }} matching {{ $matchingTradeCount === 1 ? 'trade' : 'trades' }}</span></div></div>
    <div class="trade-summary">
        <div class="trade-summary-card"><span>Account balance</span>@forelse($walletBalances as $balance)<strong>{{ $balance['currency'] }} {{ number_format($balance['balance'], 2) }}</strong><small>{{ $balance['broker'] }} · updated {{ $balance['synced_at']?->diffForHumans() }}</small>@empty<strong>Not available</strong><small>No non-zero wallet balance was returned by the latest sync.</small>@endforelse</div>
        <div class="trade-summary-card profit"><span>Total profit</span>@forelse($tradeTotals as $total)<strong>{{ $total->currency }} {{ number_format((float) $total->total_profit, 2) }}</strong>@empty<strong>—</strong>@endforelse</div>
        <div class="trade-summary-card loss"><span>Total loss</span>@forelse($tradeTotals as $total)<strong>{{ $total->currency }} {{ number_format((float) $total->total_loss, 2) }}</strong>@empty<strong>—</strong>@endforelse</div>
    </div>
    <div class="panel-body" style="padding-bottom:2px">
        <form class="filters" method="GET" action="{{ route('admin.users.show', $user) }}">
            <label class="field"><span class="sr-only">Search trades</span><input class="input" type="search" name="trade_search" value="{{ $tradeSearch }}" placeholder="Pair or strategy"></label>
            <label class="field"><span class="sr-only">Filter by broker</span><select class="input" name="trade_broker"><option value="">All brokers</option>@foreach($tradeBrokers as $broker)<option value="{{ $broker }}" @selected($tradeBroker === $broker)>{{ $broker }}</option>@endforeach</select></label>
            <button class="btn" type="submit">Filter trades</button>
            @if($tradeSearch || $tradeBroker)<a class="btn secondary" href="{{ route('admin.users.show', $user) }}">Clear</a>@endif
        </form>
    </div>
    <div class="table-wrap"><table><thead><tr><th>Date</th><th>Market</th><th>Broker</th><th>Side</th><th>Entry / exit</th><th>Quantity</th><th>Net P&amp;L</th><th>Source</th></tr></thead><tbody>
        @forelse($trades as $trade)
            <tr><td><strong>{{ $trade->date?->format('d M Y') }}</strong><br><span class="muted">{{ $trade->time ? \Illuminate\Support\Carbon::parse($trade->time)->format('h:i A') : '—' }}</span></td><td><strong>{{ $trade->pair }}</strong><br><span class="muted">{{ $trade->strategy ?: ($trade->status ?: '—') }}</span></td><td>{{ $trade->broker ?: ($trade->exchange ?: 'Manual') }}</td><td><span class="badge">{{ $trade->trade_type ?: '—' }}</span></td><td>{{ is_null($trade->entry_price) ? '—' : number_format((float) $trade->entry_price, 4) }} / {{ is_null($trade->exit_price) ? '—' : number_format((float) $trade->exit_price, 4) }}</td><td>{{ $trade->quantity ?? $trade->lot_size ?? '—' }}</td><td style="color:{{ $trade->net_pnl >= 0 ? '#86efac' : '#fda4af' }}">{{ $trade->currency ?: $user->currency }} {{ number_format($trade->net_pnl, 2) }}</td><td>{{ $trade->imported_at ? 'Imported' : 'Manual' }}</td></tr>
        @empty<tr><td class="empty" colspan="8">No trades match this view.</td></tr>@endforelse
    </tbody></table></div>
    <div class="admin-pager"><div class="admin-pager-info">Showing {{ number_format($trades->firstItem() ?? 0) }}–{{ number_format($trades->lastItem() ?? 0) }} of {{ number_format($trades->total()) }} · Page {{ $trades->currentPage() }} of {{ $trades->lastPage() }}</div><div class="admin-pager-actions"><a class="btn secondary {{ $trades->onFirstPage() ? 'disabled' : '' }}" href="{{ $trades->previousPageUrl() ?: '#' }}" @if($trades->onFirstPage()) aria-disabled="true" @endif>← Previous</a><a class="btn secondary {{ $trades->hasMorePages() ? '' : 'disabled' }}" href="{{ $trades->nextPageUrl() ?: '#' }}" @unless($trades->hasMorePages()) aria-disabled="true" @endunless>Next →</a></div></div>
</section>

<div class="grid usage-layout">
    <section class="panel"><div class="panel-head"><h2>Page time · Last 30 days</h2></div><div class="table-wrap"><table><thead><tr><th>Page</th><th>Visits</th><th>Active time</th></tr></thead><tbody>@forelse($pageUsage as $page)<tr><td>{{ $page->path }}</td><td>{{ number_format($page->visits) }}</td><td>{{ $duration((int) $page->active_seconds) }}</td></tr>@empty<tr><td class="empty" colspan="3">No activity recorded yet.</td></tr>@endforelse</tbody></table></div></section>
    <section class="panel"><div class="panel-head"><h2>Recent sessions</h2></div><div class="table-wrap"><table><thead><tr><th>Started</th><th>Active</th><th>Last seen</th></tr></thead><tbody>@forelse($recentActivitySessions as $session)<tr><td>{{ $session->started_at->format('d M, h:i A') }}</td><td>{{ $duration($session->active_seconds) }}</td><td>{{ $session->last_seen_at->diffForHumans() }}</td></tr>@empty<tr><td class="empty" colspan="3">No sessions recorded yet.</td></tr>@endforelse</tbody></table></div></section>
</div>

<section class="panel" style="margin-top:16px"><div class="panel-head"><h2>Account details</h2></div><div class="panel-body"><dl class="detail-list"><dt>User ID</dt><dd>#{{ $user->id }}</dd><dt>Email</dt><dd>{{ $user->email }}</dd><dt>Country / currency</dt><dd>{{ $user->country ?? '—' }} / {{ $user->currency ?? '—' }}</dd><dt>Timezone</dt><dd>{{ $user->timezone ?? '—' }}</dd><dt>Registered</dt><dd>{{ $user->created_at->format('d M Y, h:i A') }}</dd><dt>Last updated</dt><dd>{{ $user->updated_at->format('d M Y, h:i A') }}</dd></dl></div></section>
@endsection
