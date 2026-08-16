@extends('layouts.app')
@section('page_title', 'Delta Sync Center')
@section('page_subtitle', 'Import Delta India realized activity and monitor automatic journal updates.')
@section('content')
@php
    $credentialsReady = $account && filled($account->api_key) && filled($account->api_secret);
    $autoSyncReady = $credentialsReady && $account->is_active && $account->auto_sync_enabled;
@endphp
<style>
    .sync-page{display:grid;gap:16px}.sync-overview{display:grid;grid-template-columns:minmax(0,1.25fr) minmax(280px,.75fr);gap:16px}.sync-card{min-width:0;padding:20px}.sync-card h2{margin:0 0 5px}.sync-card p{margin:0;color:var(--muted)}.sync-status{display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin-top:14px}.sync-pill{display:inline-flex;align-items:center;gap:6px;padding:6px 9px;border-radius:999px;color:var(--good);background:rgba(52,211,153,.09);font-size:10px;font-weight:900}.sync-pill:before{content:"";width:6px;height:6px;border-radius:50%;background:currentColor}.sync-pill.off{color:var(--warn);background:rgba(251,191,36,.09)}.setup-steps{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px;margin-top:15px}.setup-step{min-width:0;padding:13px;border:1px solid var(--line);border-radius:11px;background:rgba(255,255,255,.025)}.setup-step b{display:grid;place-items:center;width:24px;height:24px;margin-bottom:8px;border-radius:7px;color:#ffad68;background:rgba(255,122,26,.1);font-size:9px}.setup-step strong,.setup-step span{display:block}.setup-step span{margin-top:3px;color:var(--muted);font-size:10px}.sync-note{padding:16px 18px;border:1px solid rgba(255,122,26,.22);border-radius:14px;background:linear-gradient(135deg,rgba(255,122,26,.08),rgba(255,255,255,.02))}.sync-note strong{display:block;margin-bottom:4px}.sync-note p{color:var(--muted);font-size:11px}.sync-history-table{min-width:700px}.sync-history-table th:last-child,.sync-history-table td:last-child{min-width:220px;white-space:normal;overflow-wrap:anywhere}.sync-scroll-hint{display:none;color:var(--muted);font-size:11px}@media(max-width:900px){.sync-overview{grid-template-columns:minmax(0,1fr)}.setup-steps{grid-template-columns:repeat(2,minmax(0,1fr))}}@media(max-width:760px){.setup-steps{grid-template-columns:minmax(0,1fr)}.sync-scroll-hint{display:block}}
</style>
<div class="sync-page">
    <section class="sync-overview">
        <div class="panel sync-card"><h2>Automatic sync every 5 minutes</h2><p>After valid Delta API credentials are saved and Auto Sync is enabled, TradeYatra queues a fresh Delta sync every five minutes. The update may complete shortly after the scheduled time while the queue processes it.</p><div class="sync-status"><span class="sync-pill {{ $autoSyncReady ? '' : 'off' }}">{{ $autoSyncReady ? 'Auto Sync ready' : 'Setup incomplete' }}</span>@if($account?->last_synced_at)<span class="muted">Last successful sync: {{ $account->last_synced_at->format('d M Y, H:i') }}</span>@else<span class="muted">No successful sync recorded yet</span>@endif</div></div>
        <div class="panel sync-card"><h2>Connection status</h2><p>{{ $credentialsReady ? 'API key and secret are saved.' : 'Save your Delta API key and secret before syncing.' }}</p><div class="actions" style="margin-top:14px"><a class="btn secondary" href="{{ route('delta.settings') }}">Open Delta settings</a><a class="btn secondary" href="{{ route('broker.guide') }}#delta-guide" target="_blank" rel="noopener">Connection guide</a></div></div>
    </section>

    @include('partials.broker-connection-video', ['broker' => 'delta'])

    <form class="panel toolbar" method="POST" action="{{ route('delta.sync.run') }}">@csrf
        <div><label>Product IDs</label><input name="product_ids" placeholder="Optional, e.g. 27,139"></div>
        <div><label>Page size</label><input type="number" name="page_size" value="50" min="1" max="50"></div>
        <button class="btn">Sync Now</button><a class="btn secondary" href="{{ route('delta.settings') }}">Settings</a>
    </form>

    <div class="sync-note"><strong>What Delta Sync imports</strong><p>TradeYatra syncs perpetual-futures activity and uses Delta wallet cashflow and settlement transactions for realized journal results. Fills, orders, and positions are supporting data. Option symbols beginning with C- or P- are excluded.</p></div>

    <section class="panel table-wrap sync-card"><h2>Delta sync history</h2><p class="sync-scroll-hint">Swipe sideways to view all sync details.</p><table class="sync-history-table"><thead><tr><th>Time</th><th>Status</th><th>Realized imported</th><th>Orders</th><th>Positions</th><th>Message</th></tr></thead><tbody>
        @forelse($logs as $log)<tr><td>{{ $log->created_at->format('d M Y H:i') }}</td><td><span class="badge">{{ $log->status }}</span></td><td>{{ $log->imported_count }}</td><td>{{ $log->orders_count }}</td><td>{{ $log->positions_count }}</td><td>{{ $log->message }}</td></tr>
        @empty<tr><td colspan="6" class="muted">No Delta sync history yet. Complete the setup above and run Sync Now.</td></tr>@endforelse
    </tbody></table></section>
</div>
@endsection
