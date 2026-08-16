@php
    $brokerName = strtolower($broker ?? '') === 'delta' ? 'Delta Exchange' : 'Shark Exchange';
    $guideAnchor = strtolower($broker ?? '') === 'delta' ? 'delta-guide' : 'shark-guide';
@endphp
@once
<style>
    .broker-video-help{display:grid;grid-template-columns:minmax(0,1fr) minmax(260px,380px);gap:18px;align-items:center;padding:17px 18px;overflow:hidden}
    .broker-video-copy h2{margin:0 0 5px;font-size:16px}.broker-video-copy p{margin:0;color:var(--muted);font-size:11px}.broker-video-actions{display:flex;gap:8px;flex-wrap:wrap;margin-top:12px}
    .broker-video-frame{position:relative;overflow:hidden;aspect-ratio:16/9;border:1px solid var(--line);border-radius:11px;background:#020608}.broker-video-frame iframe{position:absolute;inset:0;width:100%;height:100%;border:0}
    @media(max-width:760px){.broker-video-help{grid-template-columns:minmax(0,1fr)}.broker-video-frame{width:100%}}
</style>
@endonce
<section class="panel broker-video-help" aria-label="{{ $brokerName }} connection video guide">
    <div class="broker-video-copy">
        <h2>Need help connecting {{ $brokerName }}?</h2>
        <p>Watch the short broker connection walkthrough, then follow the complete written checklist for exchange-specific permissions and IP settings.</p>
        <div class="broker-video-actions">
            <a class="btn secondary" href="{{ route('broker.guide') }}#{{ $guideAnchor }}" target="_blank" rel="noopener">Open complete guide</a>
            <a class="btn secondary" href="https://www.youtube.com/watch?v=8z0kvif4Hlc" target="_blank" rel="noopener noreferrer">Watch on YouTube</a>
        </div>
    </div>
    <div class="broker-video-frame">
        <iframe src="https://www.youtube-nocookie.com/embed/8z0kvif4Hlc" title="How to connect {{ $brokerName }} to TradeYatra" loading="lazy" referrerpolicy="strict-origin-when-cross-origin" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
    </div>
</section>
