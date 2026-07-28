<style>
    .public-footer { margin-top:54px; padding:48px 0 24px; border-top:1px solid rgba(255,255,255,.09); color:var(--muted,#94aeb5); background:linear-gradient(180deg,rgba(4,12,15,.08),rgba(4,12,15,.42)); }
    .public-footer-wrap { width:min(1180px,calc(100% - 36px)); margin:0 auto; }
    .public-footer-grid { display:grid; grid-template-columns:minmax(250px,1.45fr) repeat(3,minmax(130px,.65fr)); gap:38px; padding-bottom:36px; }
    .public-footer-brand { display:inline-flex; align-items:center; gap:0; color:var(--text,var(--ink,#f7fbfc)); font-size:18px; font-weight:900; }
    .public-footer-mark { width:42px; height:42px; display:grid; place-items:center; color:inherit; background:transparent; box-shadow:none; }
    .public-footer-about { max-width:390px; margin:15px 0 0; color:var(--muted,#94aeb5); font-size:13px; line-height:1.65; }
    .public-footer-column strong { display:block; margin-bottom:13px; color:var(--text,var(--ink,#f7fbfc)); font-size:12px; letter-spacing:.08em; text-transform:uppercase; }
    .public-footer-links { display:grid; gap:9px; }
    .public-footer-links a { color:var(--muted,#94aeb5); font-size:13px; font-weight:650; text-decoration:none; }
    .public-footer-links a:hover { color:#18c7ff; }
    .public-footer-bottom { display:flex; align-items:center; justify-content:space-between; gap:18px; padding-top:22px; border-top:1px solid rgba(255,255,255,.08); font-size:12px; }
    .public-footer-risk { max-width:600px; text-align:right; }
    html[data-public-theme="light"] .public-footer { border-color:rgba(22,139,216,.14); background:linear-gradient(180deg,rgba(255,255,255,.15),rgba(230,239,243,.72)); }
    html[data-public-theme="light"] .public-footer-bottom { border-color:rgba(22,139,216,.14); }
    @media(max-width:820px){ .public-footer-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.public-footer-intro{grid-column:1/-1}.public-footer-bottom{align-items:flex-start;flex-direction:column}.public-footer-risk{text-align:left} }
    @media(max-width:520px){ .public-footer{padding-top:36px}.public-footer-wrap{width:min(100% - 24px,1180px)}.public-footer-grid{grid-template-columns:1fr;gap:26px}.public-footer-intro{grid-column:1}.public-footer-bottom{padding-bottom:4px} }
</style>
<footer class="public-footer">
    <div class="public-footer-wrap">
        <div class="public-footer-grid">
            <div class="public-footer-intro">
                <a class="public-footer-brand" href="{{ route('home') }}" wire:navigate.hover><span class="public-footer-mark"><img src="{{ asset('images/branding/tradeyatra-icon-v2.png') }}" alt="" style="width:100%;height:100%;object-fit:contain"></span><span>TradeYatra</span></a>
                <p class="public-footer-about">A private trading journal for connecting Shark Exchange and Delta Exchange, reviewing P&amp;L, and building a more consistent trading process.</p>
            </div>
            <div class="public-footer-column"><strong>Product</strong><div class="public-footer-links"><a href="{{ route('home') }}#features">Features</a><a href="{{ route('home') }}#reports">Reports</a><a href="{{ route('home') }}#workflow">Workflow</a><a href="{{ route('home') }}#faq">FAQ</a></div></div>
            <div class="public-footer-column"><strong>Connections</strong><div class="public-footer-links"><a href="{{ route('broker.guide') }}#shark-guide" wire:navigate.hover>Connect Shark</a><a href="{{ route('broker.guide') }}#delta-guide" wire:navigate.hover>Connect Delta</a><a href="{{ route('broker.guide') }}" wire:navigate.hover>API setup guide</a></div></div>
            <div class="public-footer-column"><strong>Account &amp; legal</strong><div class="public-footer-links"><a href="{{ route('login') }}">Log in</a><a href="{{ route('register') }}">Create account</a><a href="{{ route('support-fund.index') }}" wire:navigate.hover>Support TradeYatra</a><a href="{{ route('home') }}#contact">Contact us</a><a href="{{ route('legal.terms') }}" wire:navigate.hover>Terms of Use</a><a href="{{ route('legal.privacy') }}" wire:navigate.hover>Privacy Policy</a><a href="{{ route('legal.risk') }}" wire:navigate.hover>Risk Disclaimer</a></div></div>
        </div>
        <div class="public-footer-bottom"><span>© {{ now()->year }} TradeYatra. All rights reserved.</span><span class="public-footer-risk">Trading involves risk. TradeYatra is a record-keeping and review tool, not financial advice.</span></div>
    </div>
</footer>
@livewireScripts
