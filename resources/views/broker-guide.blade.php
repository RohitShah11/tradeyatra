<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Connect Shark Exchange and Delta Exchange India to TradeYatra with secure API keys. Follow exchange-specific permissions, endpoints, testing, and sync steps.">
    <meta name="robots" content="index,follow,max-image-preview:large">
    <meta name="theme-color" content="#071014">
    <link rel="canonical" href="{{ route('broker.guide') }}">
    <meta property="og:type" content="article">
    <meta property="og:title" content="Connect Shark and Delta Exchange to TradeYatra">
    <meta property="og:description" content="A clear, secure API connection guide for Shark Exchange and Delta Exchange India.">
    <meta property="og:url" content="{{ route('broker.guide') }}">
    <meta property="og:image" content="{{ asset('images/branding/tradeyatra-logo.png') }}">
    <meta name="twitter:card" content="summary_large_image">
    <title>Connect Shark &amp; Delta Exchange | TradeYatra Guide</title>
    <link rel="icon" type="image/png" href="{{ asset('images/branding/tradeyatra-icon-v2.png') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=manrope:400,500,600,700,800" rel="stylesheet">
    <style>
        :root { --bg:#071014; --panel:#0f1c22; --panel-2:#14272e; --line:rgba(255,122,26,.2); --text:#f7fbfc; --muted:#94aeb5; --orange:#ff7a1a; --orange-2:#ffad68; --cyan:#18c7ff; --cyan-2:#00dce8; --green:#20e6a4; --red:#fb7185; }
        * { box-sizing:border-box; }
        html { scroll-behavior:smooth; }
        body { margin:0; color:var(--text); background:radial-gradient(circle at 14% 4%,rgba(255,122,26,.23),transparent 36rem),radial-gradient(circle at 86% 8%,rgba(24,199,255,.14),transparent 30rem),linear-gradient(155deg,#071014 0%,#0b171c 50%,#050c0f 100%); font:15px/1.55 "Manrope",ui-sans-serif,system-ui,sans-serif; }
        a { color:inherit; text-decoration:none; }
        .wrap { width:min(1180px,calc(100% - 36px)); margin:0 auto; }
        .nav { position:sticky; top:0; z-index:20; border-bottom:1px solid rgba(255,255,255,.08); background:rgba(7,16,20,.84); backdrop-filter:blur(18px); }
        .nav-inner { min-height:76px; display:flex; align-items:center; justify-content:space-between; gap:18px; }
        .brand { display:inline-flex; align-items:center; gap:0; font-size:18px; font-weight:800; }
        .brand-mark { width:42px; height:42px; display:grid; place-items:center; color:inherit; background:transparent; box-shadow:none; }
        .nav-links { display:flex; align-items:center; gap:24px; color:var(--muted); font-size:14px; font-weight:700; }
        .nav-links a:hover { color:var(--text); }
        .nav-actions,.hero-actions { display:flex; align-items:center; gap:10px; flex-wrap:wrap; }
        .btn { min-height:42px; display:inline-flex; align-items:center; justify-content:center; gap:8px; padding:11px 16px; border:1px solid rgba(255,255,255,.12); border-radius:8px; color:var(--text); background:rgba(255,255,255,.06); font-weight:800; }
        .btn.primary { border-color:transparent; color:#fff; background:linear-gradient(115deg,#ff4c00 0%,#bf4911 40%,#12a5ba 72%,#11a5bd 100%); box-shadow:0 16px 44px rgba(0,184,217,.2); }
        .hero { padding:82px 0 42px; }
        .eyebrow { display:inline-flex; align-items:center; gap:9px; padding:7px 11px; border:1px solid rgba(255,122,26,.3); border-radius:999px; color:var(--orange-2); background:rgba(255,122,26,.1); font-size:12px; font-weight:800; letter-spacing:.08em; text-transform:uppercase; }
        .pulse { width:8px; height:8px; border-radius:50%; background:var(--green); box-shadow:0 0 16px var(--green); }
        h1 { max-width:900px; margin:22px 0 17px; font-size:clamp(42px,6vw,70px); line-height:.98; }
        h2 { margin:0 0 10px; font-size:clamp(27px,4vw,42px); line-height:1.08; }
        h3 { margin:0 0 7px; font-size:19px; }
        p { margin:0; }
        .lead { max-width:780px; color:#bfd0d7; font-size:18px; }
        .hero-actions { margin-top:26px; }
        section { padding:38px 0; scroll-margin-top:92px; }
        .security-grid,.exchange-grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:16px; }
        .security-grid { grid-template-columns:repeat(3,minmax(0,1fr)); }
        .card { border:1px solid var(--line); border-radius:14px; padding:22px; background:linear-gradient(145deg,rgba(255,255,255,.055),rgba(255,122,26,.03)); }
        .security-card strong { display:block; margin-bottom:6px; font-size:17px; }
        .security-card p,.muted { color:var(--muted); }
        .exchange-card { position:relative; overflow:hidden; padding:0; }
        .exchange-card::before { content:""; position:absolute; inset:0 auto 0 0; width:4px; background:var(--exchange); }
        .exchange-head { display:flex; align-items:center; justify-content:space-between; gap:14px; padding:22px; border-bottom:1px solid var(--line); }
        .exchange-brand { display:flex; align-items:center; gap:12px; }
        .exchange-mark { width:44px; height:44px; display:grid; place-items:center; border-radius:12px; color:#071014; background:var(--exchange); font-weight:900; }
        .shark { --exchange:var(--cyan); }
        .delta { --exchange:var(--orange); }
        .exchange-body { padding:22px; }
        .video-section { padding-bottom:0; }
        .video-guide { padding:24px; border-color:rgba(24,199,255,.24); background:linear-gradient(135deg,rgba(255,122,26,.1),rgba(24,199,255,.07)); }
        .video-guide h3 { font-size:clamp(23px,3vw,32px); }
        .video-guide > p { margin-bottom:13px; color:var(--muted); font-size:14px; }
        .video-frame { position:relative; overflow:hidden; aspect-ratio:16/9; border:1px solid var(--line); border-radius:12px; background:#020608; box-shadow:0 18px 44px rgba(0,0,0,.28); }
        .video-frame iframe { position:absolute; inset:0; width:100%; height:100%; border:0; }
        .video-link { display:inline-flex; margin-top:10px; color:var(--cyan); font-size:13px; font-weight:800; }
        .video-link:hover { text-decoration:underline; }
        .steps { display:grid; gap:12px; }
        .step { display:grid; grid-template-columns:34px 1fr; gap:12px; align-items:start; }
        .step-num { width:34px; height:34px; display:grid; place-items:center; border-radius:9px; color:var(--exchange); background:color-mix(in srgb,var(--exchange) 12%,transparent); font-size:12px; font-weight:900; }
        .step p { color:var(--muted); font-size:14px; }
        .endpoint { display:block; margin-top:7px; padding:8px 10px; border:1px solid var(--line); border-radius:8px; color:var(--exchange); background:rgba(255,255,255,.035); font:700 12px/1.45 ui-monospace,SFMono-Regular,Consolas,monospace; word-break:break-all; }
        .warning { margin-top:18px; padding:14px; border:1px solid rgba(251,191,36,.27); border-radius:10px; color:#f7d992; background:rgba(251,191,36,.07); font-size:13px; }
        .troubleshoot { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:14px; }
        .trouble strong { display:block; margin-bottom:5px; }
        .trouble p { color:var(--muted); }
        .cta { display:flex; align-items:center; justify-content:space-between; gap:22px; padding:30px; border:1px solid rgba(24,199,255,.24); border-radius:14px; background:linear-gradient(135deg,rgba(255,122,26,.13),rgba(24,199,255,.08)); }
        footer { margin-top:34px; padding:30px 0; border-top:1px solid rgba(255,255,255,.08); color:var(--muted); }
        .footer-inner { display:flex; justify-content:space-between; gap:20px; flex-wrap:wrap; }
        @media(max-width:980px){ .nav-links{display:none}.security-grid{grid-template-columns:1fr}.exchange-grid,.troubleshoot{grid-template-columns:1fr} }
        @media(max-width:640px){ .wrap{width:min(100% - 24px,1180px)}.nav-inner{min-height:68px}.nav-actions .btn:first-child{display:none}.hero{padding-top:54px}.exchange-head,.cta{align-items:flex-start;flex-direction:column}.exchange-head .btn{width:100%} }
    </style>
    @livewireStyles
</head>
<body>
    @include('partials.public-header')

    <main>
        <header class="hero">
            <div class="wrap">
                <span class="eyebrow"><span class="pulse"></span> Secure exchange setup</span>
                <h1>Connect Shark and Delta Exchange with confidence.</h1>
                <p class="lead">Follow the complete process for creating a dedicated broker API key, applying safe permissions, restricting it to TradeYatra's sync server, testing the connection, and importing your trading history.</p>
                <div class="hero-actions"><a class="btn primary" href="#shark-guide">Shark guide</a><a class="btn" href="#delta-guide">Delta guide</a></div>
            </div>
        </header>

        <section aria-labelledby="security-title">
            <div class="wrap">
                <h2 id="security-title">Before connecting either exchange</h2>
                <div class="security-grid">
                    <article class="card security-card"><strong>Use a dedicated API key</strong><p>Create a separate key named TradeYatra so access can be reviewed or revoked without affecting other services.</p></article>
                    <article class="card security-card"><strong>Do not grant trading access</strong><p>TradeYatra reads journal data; it does not need to place, edit, or cancel orders. Keep trading and withdrawals disabled.</p></article>
                    <article class="card security-card"><strong>Protect the secret</strong><p>Do not share API secrets in chat, email, screenshots, or support tickets. Store them only through the secure settings form.</p></article>
                </div>
            </div>
        </section>

        <section class="video-section" aria-labelledby="connection-video-title">
            <div class="wrap">
                <article class="card video-guide">
                    <h3 id="connection-video-title">Watch the broker connection walkthrough</h3>
                    <p>This video covers the TradeYatra broker connection process. Watch it first, then follow the Shark or Delta checklist below for the settings specific to your exchange.</p>
                    <div class="video-frame">
                        <iframe src="https://www.youtube-nocookie.com/embed/8z0kvif4Hlc" title="How to connect a broker account to TradeYatra" loading="lazy" referrerpolicy="strict-origin-when-cross-origin" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
                    </div>
                    <a class="video-link" href="https://www.youtube.com/watch?v=8z0kvif4Hlc" target="_blank" rel="noopener noreferrer">Watch directly on YouTube →</a>
                </article>
            </div>
        </section>

        <section>
            <div class="wrap exchange-grid">
                <article class="card exchange-card shark" id="shark-guide">
                    <div class="exchange-head"><div class="exchange-brand"><span class="exchange-mark">S</span><div><h2>Shark Exchange</h2><p class="muted">Trade history, orders, positions, and INR wallet data</p></div></div><a class="btn" href="{{ route('shark.settings') }}">Open Shark settings</a></div>
                    <div class="exchange-body">
                        <div class="steps">
                        <div class="step"><span class="step-num">01</span><div><h3>Open the correct Shark account</h3><p>Sign in to the Shark Exchange account whose futures trades you want to import. Complete any security verification requested by Shark.</p></div></div>
                        <div class="step"><span class="step-num">02</span><div><h3>Open API Management</h3><p>From your profile or account settings, open the API-key management screen and choose the option to create a new API key.</p></div></div>
                        <div class="step"><span class="step-num">03</span><div><h3>Create a dedicated key</h3><p>Name the key <strong>TradeYatra</strong>. Copy the API key and secret immediately and keep the creation screen open until setup is complete; Shark documents that the secret is shown only once.</p></div></div>
                        <div class="step"><span class="step-num">04</span><div><h3>Keep the key read-only</h3><p>Allow the account-data access needed for open orders, order history, positions, trade history, transaction history, and futures wallet details. Do not grant permission to place, edit, or cancel orders.</p></div></div>
                        <div class="step"><span class="step-num">05</span><div><h3>Apply IP restriction when available</h3><p>If Shark's key screen provides an IP whitelist, open the protected Shark Settings page in TradeYatra and copy the displayed server IPv4 and IPv6 entries into Shark. Do not enter your home, phone, or browser IP.</p></div></div>
                        <div class="step"><span class="step-num">06</span><div><h3>Enter credentials in TradeYatra</h3><p>Open Shark Settings, paste the key and secret without leading or trailing spaces, and leave both API endpoints on Shark's documented production host.</p><code class="endpoint">https://api.sharkexchange.in</code></div></div>
                        <div class="step"><span class="step-num">07</span><div><h3>Save the connection</h3><p>Confirm the default symbol and margin asset match your Shark account, enable automatic sync only if desired, and select <strong>Save connection</strong>.</p></div></div>
                        <div class="step"><span class="step-num">08</span><div><h3>Run and verify the first sync</h3><p>Open the Shark Sync Center, run a manual sync, and check the activity log. Confirm that realized trades and account information appear before relying on automatic updates.</p></div></div>
                        </div>
                        <div class="warning"><strong>Shark-specific:</strong> TradeYatra uses authenticated read endpoints documented by Shark. It does not require Shark's trade-permission endpoints.</div>
                    </div>
                </article>

                <article class="card exchange-card delta" id="delta-guide">
                    <div class="exchange-head"><div class="exchange-brand"><span class="exchange-mark">D</span><div><h2>Delta Exchange India</h2><p class="muted">Fills, realized P&amp;L, positions, and USD wallet activity</p></div></div><a class="btn" href="{{ route('delta.settings') }}">Open Delta settings</a></div>
                    <div class="exchange-body">
                        <div class="steps">
                        <div class="step"><span class="step-num">01</span><div><h3>Use Delta Exchange India</h3><p>Sign in to the Delta Exchange India production account whose history you want to import. India, Global, and testnet API keys are not interchangeable.</p></div></div>
                        <div class="step"><span class="step-num">02</span><div><h3>Open API Management</h3><p>From the Delta account menu, open API Management and choose to create a new API key. Name the key <strong>TradeYatra</strong>.</p></div></div>
                        <div class="step"><span class="step-num">03</span><div><h3>Select minimum permissions</h3><p>TradeYatra needs authenticated read access for your profile, fills, orders, positions, balances, and wallet transactions. Do not enable Trading permission or withdrawals.</p></div></div>
                        <div class="step"><span class="step-num">04</span><div><h3>Add the TradeYatra server addresses</h3><p>Open the protected Delta Settings page in TradeYatra and copy the displayed IPv4 and IPv6 values into Delta's IP whitelist. Enter only the address values—no protocol, port, spaces, or CIDR suffix.</p></div></div>
                        <div class="step"><span class="step-num">05</span><div><h3>Create and securely copy the key</h3><p>Finish creating the key, then copy both the API key and API secret. Store the secret only in TradeYatra's secure settings form.</p></div></div>
                        <div class="step"><span class="step-num">06</span><div><h3>Use the India production endpoint</h3><p>Paste the credentials into Delta Settings and keep the API base URL on Delta's documented India production endpoint.</p><code class="endpoint">https://api.india.delta.exchange</code></div></div>
                        <div class="step"><span class="step-num">07</span><div><h3>Save and test the connection</h3><p>Select <strong>Save connection</strong>, then run <strong>Test connection</strong>. If Delta reports that the IP is not whitelisted, reopen the key and verify both displayed server addresses were copied correctly.</p></div></div>
                        <div class="step"><span class="step-num">08</span><div><h3>Run the first Delta sync</h3><p>Open the Delta Sync Center, run a manual sync, and confirm the activity log succeeds and realized wallet transactions are imported before enabling automatic sync.</p></div></div>
                        </div>
                        <div class="warning"><strong>Delta-specific:</strong> Delta validates the request's source IP. The required addresses are shown only inside the authenticated Delta Settings page and are intentionally not published here.</div>
                    </div>
                </article>
            </div>
        </section>

        <section aria-labelledby="troubleshooting-title">
            <div class="wrap"><h2 id="troubleshooting-title">Connection troubleshooting</h2><div class="troubleshoot">
                <article class="card trouble"><strong>Authentication or signature failed</strong><p>Re-enter the key and secret without leading or trailing spaces. Create a new key if the original secret is no longer available.</p></article>
                <article class="card trouble"><strong>IP not whitelisted</strong><p>Return to the protected broker settings page, copy the displayed server addresses again, update the broker key's whitelist, and save the key before retesting.</p></article>
                <article class="card trouble"><strong>Connected but no trades</strong><p>Confirm the account contains realized activity and the key can read trade history, fills, and wallet transactions.</p></article>
                <article class="card trouble"><strong>Wrong exchange environment</strong><p>Confirm Shark uses its production host and Delta credentials were created specifically on Delta Exchange India production—not Global or testnet.</p></article>
            </div></div>
        </section>

        <section><div class="wrap cta"><div><h2>Ready to connect?</h2><p class="muted">Create your journal, connect one exchange at a time, and verify the first sync before enabling automatic updates.</p></div><a class="btn primary" href="{{ route('register') }}" data-analytics-event="registration_cta_clicked" data-analytics-placement="broker_guide">Create Journal</a></div></section>
    </main>

    @include('partials.public-footer')
</body>
</html>
