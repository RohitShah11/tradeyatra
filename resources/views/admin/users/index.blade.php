@extends('layouts.admin')
@section('title','Users')
@section('content')
<div class="page-head"><div><h1>Users</h1><p>Review registered accounts and their platform activity.</p></div></div>
<form class="filters" method="GET"><input class="input" name="search" value="{{ $search }}" placeholder="Search name or email"><button class="btn" type="submit">Search</button>@if($search)<a class="btn secondary" href="{{ route('admin.users.index') }}">Clear</a>@endif</form>
<section class="panel"><div class="table-wrap"><table><thead><tr><th>User</th><th>Country</th><th>Trades</th><th>Shark accounts</th><th>Joined</th><th></th></tr></thead><tbody>@forelse($users as $user)<tr><td><strong>{{ $user->name }}</strong><br><span class="muted">{{ $user->email }}</span></td><td>{{ $user->country ?? '—' }}</td><td>{{ number_format($user->trades_count) }}</td><td>{{ number_format($user->shark_accounts_count) }}</td><td>{{ $user->created_at->format('d M Y') }}</td><td><a class="btn secondary" href="{{ route('admin.users.show',$user) }}">View</a></td></tr>@empty<tr><td class="empty" colspan="6">No matching users.</td></tr>@endforelse</tbody></table></div><div class="pagination">{{ $users->links() }}</div></section>
@endsection
