@extends('layouts.admin')
@section('title',$user->name)
@section('content')
<div class="page-head"><div><a class="muted" href="{{ route('admin.users.index') }}">← Users</a><h1>{{ $user->name }}</h1><p>{{ $user->email }}</p></div></div>
<div class="grid stats"><div class="stat"><span>Trades</span><strong>{{ number_format($user->trades_count) }}</strong></div><div class="stat"><span>Shark accounts</span><strong>{{ number_format($user->shark_accounts_count) }}</strong></div><div class="stat"><span>Sync logs</span><strong>{{ number_format($user->sync_logs_count) }}</strong></div><div class="stat"><span>AI conversations</span><strong>{{ number_format($user->ai_conversations_count) }}</strong></div></div>
<section class="panel"><div class="panel-head"><h2>Account details</h2></div><div class="panel-body"><dl class="detail-list"><dt>User ID</dt><dd>#{{ $user->id }}</dd><dt>Email</dt><dd>{{ $user->email }}</dd><dt>Country / currency</dt><dd>{{ $user->country ?? '—' }} / {{ $user->currency ?? '—' }}</dd><dt>Timezone</dt><dd>{{ $user->timezone ?? '—' }}</dd><dt>Registered</dt><dd>{{ $user->created_at->format('d M Y, h:i A') }}</dd><dt>Last updated</dt><dd>{{ $user->updated_at->format('d M Y, h:i A') }}</dd></dl></div></section>
@endsection
