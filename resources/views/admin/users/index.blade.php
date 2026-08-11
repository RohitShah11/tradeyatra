@extends('layouts.admin')
@section('title','Users')
@section('content')
<style>.presence{display:inline-flex;align-items:center;gap:6px;padding:4px 8px;border-radius:999px;font-size:10px;font-weight:800;text-transform:capitalize}.presence:before{content:"";width:7px;height:7px;border-radius:50%}.presence.active{color:#86efac;background:rgba(34,197,94,.1)}.presence.active:before{background:#22c55e;box-shadow:0 0 0 3px rgba(34,197,94,.13)}.presence.idle{color:#fde68a;background:rgba(245,158,11,.1)}.presence.idle:before{background:#f59e0b}.presence.offline{color:#a8b6bd;background:rgba(148,163,184,.08)}.presence.offline:before{background:#64748b}.activity-page{display:block;max-width:210px;overflow:hidden;text-overflow:ellipsis}.activity-page small{display:block;color:var(--muted)}</style>
<div class="page-head"><div><h1>Users</h1><p>Review accounts, live presence, and platform activity.</p></div></div>
<form class="filters" method="GET"><input class="input" name="search" value="{{ $search }}" placeholder="Search name or email"><button class="btn" type="submit">Search</button>@if($search)<a class="btn secondary" href="{{ route('admin.users.index') }}">Clear</a>@endif</form>
<section class="panel"><div class="table-wrap"><table><thead><tr><th>User</th><th>Status</th><th>Current page</th><th>Session activity</th><th>Joined</th><th></th></tr></thead><tbody>
@forelse($users as $user)
@php($activity = $user->latestActivitySession)
@php($status = $activity?->presenceStatus() ?? 'offline')
<tr>
    <td><strong>{{ $user->name }}</strong><br><span class="muted">{{ $user->email }}</span></td>
    <td><span class="presence {{ $status }}">{{ $status === 'active' ? 'Active now' : $status }}</span>@if($activity)<br><small class="muted">{{ $activity->last_seen_at->diffForHumans() }}</small>@endif</td>
    <td><span class="activity-page">{{ $status === 'offline' ? '—' : ($activity?->current_path ?? '—') }}@if($status !== 'offline' && $activity?->current_route)<small>{{ str_replace(['.','-'],' ', $activity->current_route) }}</small>@endif</span></td>
    <td>{{ $activity ? gmdate($activity->active_seconds >= 3600 ? 'H\h i\m' : 'i\m s\s', $activity->active_seconds) : '—' }}</td>
    <td>{{ $user->created_at->format('d M Y') }}</td>
    <td><div style="display:flex;gap:7px"><a class="btn secondary" href="{{ route('admin.users.show',$user) }}">View</a><a class="btn" href="{{ route('admin.users.chat',$user) }}">Chat</a></div></td>
</tr>
@empty<tr><td class="empty" colspan="6">No matching users.</td></tr>@endforelse
</tbody></table></div><div class="pagination">{{ $users->links() }}</div></section>
@endsection
