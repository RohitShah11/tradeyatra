<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Read the TradeYatra trading risk disclaimer covering market losses, leverage, performance reports, exchange API data, and independent trading decisions.">
    <meta name="robots" content="index,follow">
    <meta name="theme-color" content="#071014">
    <link rel="canonical" href="{{ route('legal.risk') }}">
    <meta property="og:type" content="article">
    <meta property="og:title" content="Trading Risk Disclaimer | TradeYatra">
    <meta property="og:description" content="Understand the risks and limitations of trading, performance reports, and exchange-connected journal data.">
    <meta property="og:url" content="{{ route('legal.risk') }}">
    <meta property="og:image" content="{{ asset('images/branding/tradeyatra-logo.png') }}">
    <meta name="twitter:card" content="summary_large_image">
    <title>Trading Risk Disclaimer | TradeYatra</title>
    <link rel="icon" type="image/png" href="{{ asset('images/branding/tradeyatra-icon-v2.png') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=manrope:400,500,600,700,800" rel="stylesheet">
    <style>
        :root { --bg:#071014; --panel:#0f1c22; --line:rgba(255,122,26,.2); --text:#f7fbfc; --muted:#94aeb5; --orange:#ff7a1a; --orange-2:#ffad68; --cyan:#18c7ff; --red:#fb7185; --amber:#fbbf24; }
        * { box-sizing:border-box; }
        html { scroll-behavior:smooth; }
        body { margin:0; color:var(--text); background:radial-gradient(circle at 14% 4%,rgba(255,122,26,.23),transparent 36rem),radial-gradient(circle at 86% 8%,rgba(24,199,255,.13),transparent 30rem),linear-gradient(155deg,#071014 0%,#0b171c 50%,#050c0f 100%); font:15px/1.65 "Manrope",ui-sans-serif,system-ui,sans-serif; }
        a { color:inherit; text-decoration:none; }
        .wrap { width:min(1180px,calc(100% - 36px)); margin:0 auto; }
        .nav { position:sticky; top:0; z-index:20; border-bottom:1px solid rgba(255,255,255,.08); background:rgba(7,16,20,.84); backdrop-filter:blur(18px); }
        .nav-inner { min-height:76px; display:flex; align-items:center; justify-content:space-between; gap:18px; }
        .brand { display:inline-flex; align-items:center; gap:0; font-size:18px; font-weight:800; }
        .brand-mark { width:42px; height:42px; display:grid; place-items:center; color:inherit; background:transparent; box-shadow:none; }
        .nav-links { display:flex; align-items:center; gap:24px; color:var(--muted); font-size:14px; font-weight:700; }
        .nav-links a:hover { color:var(--text); }
        .nav-actions { display:flex; align-items:center; gap:10px; }
        .btn { min-height:42px; display:inline-flex; align-items:center; justify-content:center; padding:11px 16px; border:1px solid rgba(255,255,255,.12); border-radius:8px; color:var(--text); background:rgba(255,255,255,.06); font-weight:800; }
        .btn.primary { border-color:transparent; background:linear-gradient(115deg,#ff4c00 0%,#bf4911 40%,#12a5ba 72%,#11a5bd 100%); box-shadow:0 16px 44px rgba(0,184,217,.2); }
        .hero { padding:78px 0 38px; }
        .eyebrow { display:inline-flex; align-items:center; gap:9px; padding:7px 11px; border:1px solid rgba(251,191,36,.3); border-radius:999px; color:#f8d36e; background:rgba(251,191,36,.08); font-size:12px; font-weight:800; letter-spacing:.08em; text-transform:uppercase; }
        .eyebrow::before { content:"!"; width:18px; height:18px; display:grid; place-items:center; border-radius:50%; color:#071014; background:var(--amber); font-size:11px; font-weight:900; }
        h1 { max-width:860px; margin:22px 0 16px; font-size:clamp(42px,6vw,68px); line-height:.98; }
        .lead { max-width:790px; margin:0; color:#bfd0d7; font-size:18px; }
        .updated { display:inline-block; margin-top:20px; color:var(--muted); font-size:13px; }
        main section { padding:32px 0; }
        .notice { display:grid; grid-template-columns:auto 1fr; gap:15px; align-items:start; padding:21px; border:1px solid rgba(251,113,133,.34); border-radius:14px; background:linear-gradient(135deg,rgba(251,113,133,.12),rgba(255,122,26,.05)); }
        .notice-mark { width:42px; height:42px; display:grid; place-items:center; border-radius:11px; color:#fff; background:rgba(251,113,133,.22); font-size:20px; font-weight:900; }
        .notice h2 { margin:0 0 5px; font-size:21px; }
        .notice p { margin:0; color:#d8c4c8; }
        .legal-layout { display:grid; grid-template-columns:250px minmax(0,1fr); gap:22px; align-items:start; }
        .toc { position:sticky; top:98px; display:grid; gap:5px; padding:14px; border:1px solid var(--line); border-radius:13px; background:rgba(255,255,255,.035); }
        .toc strong { padding:7px 9px; color:var(--orange-2); font-size:11px; letter-spacing:.1em; text-transform:uppercase; }
        .toc a { padding:8px 9px; border-radius:8px; color:var(--muted); font-size:13px; font-weight:700; }
        .toc a:hover { color:var(--text); background:rgba(255,255,255,.05); }
        .sections { display:grid; gap:13px; }
        .risk-card { scroll-margin-top:98px; padding:23px; border:1px solid var(--line); border-radius:14px; background:linear-gradient(145deg,rgba(255,255,255,.055),rgba(255,122,26,.025)); }
        .risk-card h2 { display:flex; align-items:center; gap:10px; margin:0 0 9px; font-size:22px; }
        .risk-card h2 span { width:29px; height:29px; display:grid; place-items:center; border-radius:8px; color:var(--orange-2); background:rgba(255,122,26,.11); font-size:11px; }
        .risk-card p { margin:0; color:var(--muted); }
        .risk-card p + p { margin-top:10px; }
        .closing { padding:26px; border:1px solid rgba(24,199,255,.24); border-radius:14px; background:linear-gradient(135deg,rgba(255,122,26,.11),rgba(24,199,255,.07)); }
        .closing h2 { margin:0 0 8px; }
        .closing p { margin:0; color:#bfd0d7; }
        footer { margin-top:35px; padding:30px 0; border-top:1px solid rgba(255,255,255,.08); color:var(--muted); }
        .footer-inner { display:flex; justify-content:space-between; gap:20px; flex-wrap:wrap; }
        @media(max-width:980px){ .nav-links{display:none}.legal-layout{grid-template-columns:1fr}.toc{position:static;grid-template-columns:repeat(3,minmax(0,1fr))}.toc strong{grid-column:1/-1} }
        @media(max-width:640px){ .wrap{width:min(100% - 24px,1180px)}.nav-inner{min-height:68px}.nav-actions .btn:first-child{display:none}.hero{padding-top:52px}.notice{grid-template-columns:1fr}.toc{grid-template-columns:1fr}.risk-card{padding:19px} }
    </style>
    @livewireStyles
</head>
<body>
    @include('partials.public-header')

    <main>
        <header class="hero"><div class="wrap"><span class="eyebrow">Important information</span><h1>Trading Risk Disclaimer</h1><p class="lead">Understand the risks of trading and the limits of journal reports, exchange data, and performance metrics before using TradeYatra.</p><span class="updated">Last updated: 19 July 2026</span></div></header>

        <section><div class="wrap notice"><span class="notice-mark">!</span><div><h2>Trading can result in substantial financial loss</h2><p>Do not trade money you cannot afford to lose. Leverage can magnify both gains and losses, and market conditions can change rapidly.</p></div></div></section>

        <section><div class="wrap legal-layout">
            <nav class="toc" aria-label="Disclaimer sections"><strong>On this page</strong><a href="#market-risk">Market risk</a><a href="#not-advice">Not financial advice</a><a href="#past-performance">Past performance</a><a href="#data-accuracy">Data accuracy</a><a href="#api-risk">API connections</a><a href="#availability">Platform availability</a><a href="#responsibility">Your responsibility</a><a href="#professional-advice">Professional advice</a></nav>
            <div class="sections">
                <article class="risk-card" id="market-risk"><h2><span>01</span>Market and leverage risk</h2><p>Crypto assets, derivatives, stocks, commodities, currencies, futures, and options can be highly volatile. Prices may move against your position without warning, and you may lose part or all of the capital committed to a trade.</p><p>Leveraged products can create losses greater than the initial margin used and may be liquidated according to exchange rules.</p></article>
                <article class="risk-card" id="not-advice"><h2><span>02</span>Not financial or investment advice</h2><p>TradeYatra is a record-keeping and performance-review tool. Nothing displayed in the journal, dashboard, calendar, notes, reports, AI features, or news features should be treated as a recommendation to buy, sell, hold, or trade any instrument.</p></article>
                <article class="risk-card" id="past-performance"><h2><span>03</span>Past performance is not predictive</h2><p>Historical P&amp;L, win rate, average trade, profitable days, setup labels, and weekly or monthly reports describe past activity only. They do not guarantee similar outcomes in future market conditions.</p></article>
                <article class="risk-card" id="data-accuracy"><h2><span>04</span>Journal and exchange data may be incomplete</h2><p>Imported trades and wallet balances depend on information returned by Shark Exchange, Delta Exchange, and their APIs. Delays, outages, permission limits, mapping errors, missing transactions, duplicate prevention, or manual-entry mistakes may affect displayed values.</p><p>Always verify balances, orders, positions, fees, taxes, and realized P&amp;L against the official exchange account before making decisions.</p></article>
                <article class="risk-card" id="api-risk"><h2><span>05</span>Exchange API connection risk</h2><p>Use dedicated credentials with the minimum permissions needed for journal imports and keep withdrawal permissions disabled. You are responsible for managing keys, IP restrictions, account security, and revoking access when it is no longer needed.</p></article>
                <article class="risk-card" id="availability"><h2><span>06</span>Platform and synchronization availability</h2><p>TradeYatra, exchange APIs, automatic synchronization, reports, and third-party infrastructure may occasionally be unavailable, delayed, or inaccurate. The journal should not be your only source for monitoring live positions, margin, liquidation risk, or account balances.</p></article>
                <article class="risk-card" id="responsibility"><h2><span>07</span>Your independent responsibility</h2><p>You remain solely responsible for trade selection, position sizing, risk limits, order execution, account security, record accuracy, tax reporting, and compliance with applicable laws and exchange rules.</p></article>
                <article class="risk-card" id="professional-advice"><h2><span>08</span>Seek qualified professional advice</h2><p>For financial, investment, tax, accounting, legal, or regulatory guidance, consult a suitably qualified professional who understands your circumstances and jurisdiction.</p></article>
                <div class="closing"><h2>Use the journal as a review tool</h2><p>TradeYatra can help organize trading activity and reflection, but it cannot remove market risk or replace independent judgment, official exchange records, or professional advice.</p></div>
            </div>
        </div></section>
    </main>

    @include('partials.public-footer')
</body>
</html>
