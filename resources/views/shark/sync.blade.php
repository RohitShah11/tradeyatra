@extends('layouts.app')

@section('page_title', 'Shark Sync Center')
@section('page_subtitle', 'Import Shark trade history and monitor automatic journal updates.')

@section('content')
@php
    $credentialsReady = $account && filled($account->api_key) && filled($account->api_secret);
    $autoSyncReady = $credentialsReady && $account->is_active && $account->auto_sync_enabled;
@endphp
<style>
    .sync-page{display:grid;gap:16px}.sync-overview{display:grid;grid-template-columns:minmax(0,1.25fr) minmax(280px,.75fr);gap:16px}.sync-card{min-width:0;padding:20px}.sync-card h2{margin:0 0 5px}.sync-card p{margin:0;color:var(--muted)}.sync-status{display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin-top:14px}.sync-pill{display:inline-flex;align-items:center;gap:6px;padding:6px 9px;border-radius:999px;color:var(--good);background:rgba(52,211,153,.09);font-size:10px;font-weight:900}.sync-pill:before{content:"";width:6px;height:6px;border-radius:50%;background:currentColor}.sync-pill.off{color:var(--warn);background:rgba(251,191,36,.09)}.sync-note{padding:16px 18px;border:1px solid rgba(0,184,217,.22);border-radius:14px;background:linear-gradient(135deg,rgba(0,184,217,.08),rgba(255,255,255,.02))}.sync-note strong{display:block;margin-bottom:4px;color:var(--ink)}.sync-note p{color:var(--muted);font-size:11px}.setup-steps{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px;margin-top:15px}.setup-step{min-width:0;padding:13px;border:1px solid var(--line);border-radius:11px;background:rgba(255,255,255,.025)}.setup-step b{display:grid;place-items:center;width:24px;height:24px;margin-bottom:8px;border-radius:7px;color:#72ddf1;background:rgba(0,184,217,.1);font-size:9px}.setup-step strong,.setup-step span{display:block}.setup-step span{margin-top:3px;color:var(--muted);font-size:10px}.ip-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px;margin-top:13px}.ip-item{min-width:0}.ip-item label{display:block;margin-bottom:5px;font-size:10px;font-weight:900}.ip-row{display:grid;grid-template-columns:minmax(0,1fr) auto;gap:7px}.ip-row input{min-width:0;font:800 11px/1.4 ui-monospace,monospace;color:var(--accent)}.sync-history-table{min-width:760px;table-layout:auto}.sync-history-table th,.sync-history-table td{white-space:nowrap}.sync-history-table th:last-child,.sync-history-table td:last-child{min-width:220px;white-space:normal;overflow-wrap:anywhere}.sync-scroll-hint{display:none;margin:-4px 0 12px;color:var(--muted);font-size:11px}@media(max-width:900px){.sync-overview{grid-template-columns:minmax(0,1fr)}.setup-steps{grid-template-columns:repeat(2,minmax(0,1fr))}}@media(max-width:760px){.sync-scroll-hint{display:block}.ip-grid,.setup-steps{grid-template-columns:minmax(0,1fr)}}@media(max-width:420px){.ip-row{grid-template-columns:minmax(0,1fr)}}
</style>

<div class="sync-page">
    <section class="sync-overview">
        <div class="panel sync-card">
            <h2>Automatic sync every 5 minutes</h2>
            <p>After valid Shark API credentials are saved and Auto Sync is enabled, TradeYatra queues a fresh account sync every five minutes. The update may complete shortly after the scheduled time while the queue processes it.</p>
            <div class="sync-status">
                <span class="sync-pill {{ $autoSyncReady ? '' : 'off' }}">{{ $autoSyncReady ? 'Auto Sync ready' : 'Setup incomplete' }}</span>
                @if($account?->last_synced_at)<span class="muted">Last successful sync: {{ $account->last_synced_at->format('d M Y, H:i') }}</span>@else<span class="muted">No successful sync recorded yet</span>@endif
            </div>
        </div>
        <div class="panel sync-card">
            <h2>Connection status</h2>
            <p>{{ $credentialsReady ? 'API key and secret are saved.' : 'Save your Shark API key and secret before syncing.' }}</p>
            <div class="actions" style="margin-top:14px"><a class="btn secondary" href="{{ route('shark.settings') }}">Open Shark settings</a><a class="btn secondary" href="{{ route('broker.guide') }}#shark-guide" target="_blank" rel="noopener">Connection guide</a></div>
        </div>
    </section>

    <form id="sharkSyncForm" class="panel toolbar" method="POST" action="{{ route('shark.sync.run') }}">@csrf
        <div><label>Symbol filter</label><input name="symbol" value="{{ old('symbol') }}" placeholder="Leave blank for all pairs"></div>
        <div><label>Page size</label><input type="number" name="pageSize" value="100" min="1" max="500"></div>
        <button id="sharkSyncButton" class="btn">Sync Now</button><a class="btn secondary" href="{{ route('shark.settings') }}">Settings</a>
    </form>

    <div class="sync-note"><strong>What Shark Sync imports</strong><p>TradeYatra reads trade history, order history, open orders, open positions, and a futures-wallet snapshot. Only new realized trade records are added to your journal; existing records are not duplicated.</p></div>

    <section class="panel table-wrap sync-card">
        <h2>Sync history</h2><p class="sync-scroll-hint">Swipe sideways to view all sync details.</p>
        <table class="sync-history-table"><thead><tr><th>Time</th><th>Status</th><th>Imported</th><th>Orders</th><th>Positions</th><th>Wallet</th><th>Message</th></tr></thead><tbody>
        @forelse($logs as $log)<tr><td>{{ $log->created_at->format('d M Y H:i') }}</td><td><span class="badge">{{ $log->status }}</span></td><td>{{ $log->imported_count }}</td><td>{{ $log->orders_count }}</td><td>{{ $log->positions_count }}</td><td>@if(is_array($log->wallet_snapshot) && isset($log->wallet_snapshot['walletBalance'])){{ $log->wallet_snapshot['walletBalance'] }} {{ $log->wallet_snapshot['marginAsset'] ?? '' }}@else<span class="muted">—</span>@endif</td><td>{{ $log->message }}</td></tr>
        @empty<tr><td colspan="7" class="muted">No sync history yet. Complete the setup above and run Sync Now.</td></tr>@endforelse
        </tbody></table>
    </section>
</div>
<script>
document.getElementById('sharkSyncForm')?.addEventListener('submit', () => {
    const button = document.getElementById('sharkSyncButton');
    button.disabled = true;
    button.textContent = 'Syncing…';
});
</script>
@endsection
