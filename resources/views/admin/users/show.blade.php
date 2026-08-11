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
<style>.presence{display:inline-flex;align-items:center;gap:7px;padding:6px 10px;border-radius:999px;font-size:11px;font-weight:800;text-transform:capitalize}.presence:before{content:"";width:8px;height:8px;border-radius:50%}.presence.active{color:#86efac;background:rgba(34,197,94,.1)}.presence.active:before{background:#22c55e;box-shadow:0 0 0 4px rgba(34,197,94,.12)}.presence.idle{color:#fde68a;background:rgba(245,158,11,.1)}.presence.idle:before{background:#f59e0b}.presence.offline{color:#a8b6bd;background:rgba(148,163,184,.08)}.presence.offline:before{background:#64748b}.live-card{padding:18px}.live-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px;margin-top:16px}.live-item{padding:12px;border:1px solid var(--line);border-radius:9px;background:rgba(255,255,255,.025)}.live-item small,.live-item strong{display:block}.live-item small{color:var(--muted);font-size:9px;text-transform:uppercase}.live-item strong{margin-top:5px;overflow:hidden;text-overflow:ellipsis}.usage-layout{grid-template-columns:minmax(0,1.15fr) minmax(0,.85fr)}@media(max-width:850px){.live-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.usage-layout{grid-template-columns:1fr}}@media(max-width:520px){.live-grid{grid-template-columns:1fr}}</style>
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

<div class="grid usage-layout">
    <section class="panel"><div class="panel-head"><h2>Page time · Last 30 days</h2></div><div class="table-wrap"><table><thead><tr><th>Page</th><th>Visits</th><th>Active time</th></tr></thead><tbody>@forelse($pageUsage as $page)<tr><td>{{ $page->path }}</td><td>{{ number_format($page->visits) }}</td><td>{{ $duration((int) $page->active_seconds) }}</td></tr>@empty<tr><td class="empty" colspan="3">No activity recorded yet.</td></tr>@endforelse</tbody></table></div></section>
    <section class="panel"><div class="panel-head"><h2>Recent sessions</h2></div><div class="table-wrap"><table><thead><tr><th>Started</th><th>Active</th><th>Last seen</th></tr></thead><tbody>@forelse($recentActivitySessions as $session)<tr><td>{{ $session->started_at->format('d M, h:i A') }}</td><td>{{ $duration($session->active_seconds) }}</td><td>{{ $session->last_seen_at->diffForHumans() }}</td></tr>@empty<tr><td class="empty" colspan="3">No sessions recorded yet.</td></tr>@endforelse</tbody></table></div></section>
</div>

<section class="panel" style="margin-top:16px"><div class="panel-head"><h2>Account details</h2></div><div class="panel-body"><dl class="detail-list"><dt>User ID</dt><dd>#{{ $user->id }}</dd><dt>Email</dt><dd>{{ $user->email }}</dd><dt>Country / currency</dt><dd>{{ $user->country ?? '—' }} / {{ $user->currency ?? '—' }}</dd><dt>Timezone</dt><dd>{{ $user->timezone ?? '—' }}</dd><dt>Registered</dt><dd>{{ $user->created_at->format('d M Y, h:i A') }}</dd><dt>Last updated</dt><dd>{{ $user->updated_at->format('d M Y, h:i A') }}</dd></dl></div></section>
@endsection
