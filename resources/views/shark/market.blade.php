@extends('layouts.app')

@section('page_title', 'Latest API Fetch Responses')
@section('page_subtitle', 'View the direct raw response saved by the latest successful SharkExchange and Delta Exchange sync.')

@push('styles')
<style>
    .api-response-grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:16px; }
    .api-response-card { min-width:0; overflow:hidden; }
    .api-response-head { display:flex; align-items:flex-start; justify-content:space-between; gap:14px; margin-bottom:14px; }
    .api-response-head h2 { margin:0 0 4px; }
    .api-response-head p { margin:0; }
    .api-status { display:inline-flex; align-items:center; gap:6px; flex:0 0 auto; padding:5px 9px; border-radius:999px; font-size:11px; font-weight:800; }
    .api-status.ok { color:#087f5b; background:rgba(18,184,134,.12); }
    .api-status.error { color:#c92a2a; background:rgba(250,82,82,.12); }
    .api-endpoint { margin:12px 0 8px; color:var(--muted); font-size:12px; overflow-wrap:anywhere; }
    .api-json { max-height:560px; overflow:auto; margin:0; padding:16px; border-radius:12px; background:#0d1117; color:#d7e0ea; font:12px/1.6 ui-monospace,SFMono-Regular,Menlo,Consolas,monospace; white-space:pre-wrap; overflow-wrap:anywhere; }
    .api-message { padding:18px; border:1px dashed var(--line); border-radius:12px; color:var(--muted); }
    @media (max-width:900px) { .api-response-grid { grid-template-columns:1fr; } }
</style>
@endpush

@section('content')
<div class="grid">
    <div class="panel toolbar">
        <div>
            <strong>Raw sync responses</strong>
            <div class="muted">This page does not make a new API request. Run an exchange sync to replace these responses with the newest fetch.</div>
        </div>
        <a class="btn" href="{{ route('shark.market') }}">Reload latest</a>
    </div>

    <div class="api-response-grid">
        <section class="panel api-response-card">
            <div class="api-response-head">
                <div>
                    <h2>SharkExchange response</h2>
                    <p class="muted">{{ $sharkLog ? 'Fetched '.$sharkLog->created_at->diffForHumans().' · '.$sharkLog->created_at->format('d M Y, h:i:s A') : 'No successful fetch saved yet' }}</p>
                </div>
                <span class="api-status {{ $sharkLog ? 'ok' : 'error' }}">{{ $sharkLog ? 'Latest response' : 'Not available' }}</span>
            </div>
            @if(!$sharkLog)
                <div class="api-message">No SharkExchange API response has been saved. Run Shark sync once, then reload this page.</div>
            @else
                <div class="api-endpoint">Source: latest sync_logs.raw_payload · Status: {{ $sharkLog->status }} · Imported: {{ $sharkLog->imported_count }}</div>
                <pre class="api-json">{{ json_encode($sharkLog->raw_payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}</pre>
            @endif
        </section>

        <section class="panel api-response-card">
            <div class="api-response-head">
                <div>
                    <h2>Delta Exchange response</h2>
                    <p class="muted">{{ $deltaLog ? 'Fetched '.$deltaLog->created_at->diffForHumans().' · '.$deltaLog->created_at->format('d M Y, h:i:s A') : 'No successful fetch saved yet' }}</p>
                </div>
                <span class="api-status {{ $deltaLog ? 'ok' : 'error' }}">{{ $deltaLog ? 'Latest response' : 'Not available' }}</span>
            </div>
            @if(!$deltaLog)
                <div class="api-message">No Delta Exchange API response has been saved. Run Delta sync once, then reload this page.</div>
            @else
                <div class="api-endpoint">Source: latest sync_logs.raw_payload · Status: {{ $deltaLog->status }} · Imported: {{ $deltaLog->imported_count }}</div>
                <pre class="api-json">{{ json_encode($deltaLog->raw_payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}</pre>
            @endif
        </section>
    </div>
</div>
@endsection
