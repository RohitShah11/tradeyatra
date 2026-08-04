<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Meet Rohit Shah, the founder and builder of TradeYatra, and learn why he created a private trading journal for Shark Exchange and Delta Exchange India traders.">
    <meta name="robots" content="index,follow,max-image-preview:large">
    <meta name="theme-color" content="#071014">
    <link rel="canonical" href="{{ route('founder') }}">
    <meta property="og:type" content="profile">
    <meta property="og:title" content="Meet Rohit Shah, Founder of TradeYatra">
    <meta property="og:description" content="The story, purpose, and product principles behind TradeYatra.">
    <meta property="og:url" content="{{ route('founder') }}">
    <meta property="og:image" content="{{ asset('images/founder/rohit-shah-original.jpeg') }}">
    <meta name="twitter:card" content="summary_large_image">
    <title>Meet Rohit Shah, Founder of TradeYatra</title>
    <link rel="icon" type="image/png" href="{{ asset('images/branding/tradeyatra-icon-v2.png') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=manrope:400,500,600,700,800" rel="stylesheet">
    <style>
        :root { --bg:#071014; --panel:#0f1c22; --line:rgba(255,122,26,.2); --text:#f7fbfc; --muted:#94aeb5; --orange:#ff7a1a; --orange-2:#ffad68; --cyan:#18c7ff; --green:#20e6a4; }
        * { box-sizing:border-box; }
        body { margin:0; color:var(--text); background:radial-gradient(circle at 12% 5%,rgba(255,122,26,.22),transparent 34rem),radial-gradient(circle at 88% 10%,rgba(24,199,255,.14),transparent 30rem),linear-gradient(155deg,#071014 0%,#0b171c 52%,#050c0f 100%); font:15px/1.7 "Manrope",ui-sans-serif,system-ui,sans-serif; }
        a { color:inherit; text-decoration:none; }
        .wrap { width:min(1120px,calc(100% - 36px)); margin:0 auto; }
        .hero { position:relative; display:grid; grid-template-columns:280px minmax(0,1fr); gap:clamp(36px,7vw,76px); align-items:center; margin-top:42px; padding:44px; overflow:hidden; border:1px solid rgba(255,255,255,.09); border-radius:28px; background:linear-gradient(135deg,rgba(255,255,255,.065),rgba(24,199,255,.025) 58%,rgba(255,122,26,.05)); box-shadow:0 34px 90px rgba(0,0,0,.24); }
        .hero::before { content:""; position:absolute; width:330px; height:330px; right:-130px; top:-170px; border:1px solid rgba(24,199,255,.15); border-radius:50%; box-shadow:0 0 0 52px rgba(24,199,255,.025),0 0 0 104px rgba(255,122,26,.018); pointer-events:none; }
        .portrait-shell { position:relative; z-index:1; width:280px; height:373px; padding:8px; border:1px solid rgba(24,199,255,.3); border-radius:22px; background:linear-gradient(145deg,rgba(255,122,26,.2),rgba(24,199,255,.13)); box-shadow:0 28px 70px rgba(0,0,0,.35),-12px 12px 0 rgba(255,122,26,.05); }
        .portrait-shell::after { content:"Founder & builder"; position:absolute; right:-16px; bottom:26px; padding:9px 13px; border:1px solid rgba(255,255,255,.13); border-radius:10px; color:#fff; background:rgba(7,16,20,.91); box-shadow:0 15px 38px rgba(0,0,0,.3); font-size:12px; font-weight:800; letter-spacing:.04em; }
        .portrait { width:100%; height:100%; display:block; border-radius:15px; object-fit:contain; }
        .eyebrow { display:inline-flex; align-items:center; gap:9px; padding:7px 11px; border:1px solid rgba(255,122,26,.3); border-radius:999px; color:var(--orange-2); background:rgba(255,122,26,.1); font-size:12px; font-weight:800; letter-spacing:.08em; text-transform:uppercase; }
        .eyebrow::before { content:""; width:8px; height:8px; border-radius:50%; background:var(--green); box-shadow:0 0 14px var(--green); }
        h1 { margin:22px 0 18px; font-size:clamp(34px,4.5vw,54px); line-height:1.05; letter-spacing:-.035em; }
        h1 .founder-first-name { color:var(--orange); }
        h1 .founder-last-name { color:var(--cyan); }
        .hero-copy { position:relative; z-index:1; }
        .lead { max-width:680px; margin:0; color:#bfd0d7; font-size:17px; }
        .roles { display:flex; flex-wrap:wrap; gap:9px; margin-top:22px; }
        .role { display:inline-flex; align-items:center; gap:7px; padding:7px 11px; border:1px solid rgba(255,255,255,.1); border-radius:999px; color:#c9d9df; background:rgba(0,0,0,.14); font-size:12px; font-weight:700; }
        .role::before { content:""; width:6px; height:6px; border-radius:50%; background:var(--orange); }
        .role:nth-child(2)::before { background:var(--cyan); }
        .role:nth-child(3)::before { background:var(--green); }
        .signature { margin-top:24px; }
        .signature strong,.signature span { display:block; }
        .signature strong { font-size:20px; }
        .signature span { color:var(--muted); font-size:13px; }
        .story { padding:58px 0 72px; }
        .story-heading { display:flex; align-items:end; justify-content:space-between; gap:30px; margin-bottom:22px; }
        .story-heading h2 { max-width:620px; margin:11px 0 0; }
        .story-heading p { max-width:420px; margin:0; color:var(--muted); }
        .story-grid { display:grid; grid-template-columns:minmax(0,1.15fr) minmax(280px,.85fr); gap:22px; }
        .card { position:relative; padding:clamp(24px,4vw,38px); overflow:hidden; border:1px solid var(--line); border-radius:18px; background:linear-gradient(145deg,rgba(255,255,255,.06),rgba(255,122,26,.025)); box-shadow:0 20px 55px rgba(0,0,0,.13); }
        .card::before { content:""; position:absolute; inset:0 auto auto 0; width:72px; height:2px; background:linear-gradient(90deg,var(--orange),var(--cyan)); }
        h2 { margin:0 0 18px; font-size:clamp(27px,4vw,40px); line-height:1.1; }
        .card p { margin:0 0 18px; color:#b5c8cf; }
        .card p:last-child { margin-bottom:0; }
        .principles { display:grid; gap:13px; }
        .principle { display:grid; grid-template-columns:34px 1fr; gap:12px; align-items:start; padding:15px; border:1px solid rgba(24,199,255,.16); border-radius:12px; background:rgba(24,199,255,.035); }
        .principle-mark { width:34px; height:34px; display:grid; place-items:center; border-radius:9px; color:var(--orange-2); background:rgba(255,122,26,.1); font-size:11px; font-weight:900; }
        .principle:nth-child(even) .principle-mark { color:var(--cyan); background:rgba(24,199,255,.09); }
        .principle strong { display:block; margin-bottom:3px; color:var(--text); }
        .principle span { color:var(--muted); font-size:13px; }
        .note { margin-top:22px; padding:20px 22px; border-left:3px solid var(--orange); border-radius:0 12px 12px 0; color:#c9d7dc; background:rgba(255,122,26,.07); }
        .cta { display:flex; align-items:center; justify-content:space-between; gap:24px; margin-top:22px; padding:30px; border:1px solid rgba(24,199,255,.24); border-radius:18px; background:linear-gradient(130deg,rgba(255,122,26,.13),rgba(24,199,255,.09)); box-shadow:0 20px 60px rgba(0,0,0,.14); }
        .cta h2 { margin-bottom:6px; font-size:25px; }
        .cta p { margin:0; color:var(--muted); }
        .btn { min-height:45px; display:inline-flex; align-items:center; justify-content:center; gap:9px; padding:11px 18px; border-radius:9px; color:#fff; background:linear-gradient(115deg,#ff4c00 0%,#bf4911 40%,#12a5ba 72%,#11a5bd 100%); box-shadow:0 14px 34px rgba(12,157,180,.2); font-weight:800; white-space:nowrap; transition:transform .2s ease,box-shadow .2s ease; }
        .btn::after { content:"→"; }
        .btn:hover { transform:translateY(-2px); box-shadow:0 18px 40px rgba(255,122,26,.18),0 14px 34px rgba(12,157,180,.2); }
        html[data-public-theme="light"] body { color:#13252d; background:radial-gradient(circle at 12% 5%,rgba(255,122,26,.15),transparent 34rem),radial-gradient(circle at 88% 10%,rgba(24,199,255,.1),transparent 30rem),#f5fafb; }
        html[data-public-theme="light"] .lead,html[data-public-theme="light"] .card p,html[data-public-theme="light"] .note { color:#49636d; }
        html[data-public-theme="light"] .hero,html[data-public-theme="light"] .card,html[data-public-theme="light"] .principle,html[data-public-theme="light"] .cta { background:rgba(255,255,255,.78); box-shadow:0 20px 55px rgba(35,71,82,.08); }
        html[data-public-theme="light"] .role { color:#49636d; background:rgba(238,247,249,.86); }
        @media(max-width:820px){ .hero,.story-grid{grid-template-columns:1fr}.hero{padding:34px}.portrait-shell{width:260px;height:347px;margin:0 auto}.hero-copy{text-align:center}.eyebrow{justify-content:center}.lead{margin-inline:auto}.roles{justify-content:center}.story-heading{align-items:flex-start;flex-direction:column;gap:10px}.cta{align-items:flex-start;flex-direction:column}.portrait-shell::after{right:10px} }
        @media(max-width:520px){ .wrap{width:min(100% - 24px,1120px)}.hero{margin-top:24px;padding:26px 18px}.portrait-shell{width:230px;height:307px}.story{padding-top:38px}.cta{padding:24px}.cta .btn{width:100%} }
    </style>
    <script type="application/ld+json">{!! json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'Person',
        'name' => 'Rohit Shah',
        'jobTitle' => 'Founder and Builder',
        'url' => route('founder'),
        'image' => asset('images/founder/rohit-shah-original.jpeg'),
        'worksFor' => [
            '@type' => 'Organization',
            'name' => 'TradeYatra',
            'url' => route('home'),
        ],
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
    @livewireStyles
</head>
<body>
    @include('partials.public-header')
    <main>
        <section class="wrap hero">
            <div class="portrait-shell">
                <img class="portrait" src="{{ asset('images/founder/rohit-shah-original.jpeg') }}" alt="Rohit Shah, founder and builder of TradeYatra" width="1200" height="1600" fetchpriority="high">
            </div>
            <div class="hero-copy">
                <span class="eyebrow">Meet the founder</span>
                <h1>Hi, I’m <span class="founder-first-name">Rohit</span> <span class="founder-last-name">Shah</span>.</h1>
                <p class="lead">I’m a developer and trader who saw how difficult it was to manage scattered trading data. I built TradeYatra to bring trades, notes, screenshots, and performance insights into one private journal.</p>
                <div class="roles" aria-label="Founder roles"><span class="role">Developer</span><span class="role">Trader</span><span class="role">Independent builder</span></div>
                <div class="signature"><strong>Rohit Shah</strong><span>Founder &amp; builder, TradeYatra</span></div>
            </div>
        </section>

        <section class="wrap story">
            <div class="story-heading">
                <div><span class="eyebrow">From a real problem to a product</span><h2>Building the journal I wanted to use.</h2></div>
                <p>TradeYatra is shaped around a simple idea: better trading decisions begin with better records and honest review.</p>
            </div>
            <div class="story-grid">
                <article class="card">
                    <h2>Why I started TradeYatra</h2>
                    <p>Trading platforms are designed for placing trades. They are not always designed for helping a trader stop, reflect, and learn. Important context often ends up spread across broker screens, screenshots, notebooks, and spreadsheets.</p>
                    <p>I wanted one private place where Shark Exchange and Delta Exchange India activity could become a useful review process: what happened, why it happened, whether the plan was followed, and what should change next time.</p>
                    <p>That idea became TradeYatra. It is still an independently built, evolving product. I am sharing the person behind it because trust should begin with transparency—not an anonymous logo or exaggerated promises.</p>
                    <div class="note">TradeYatra does not place trades, predict markets, or provide financial advice. It exists to help you keep records and review your own decisions.</div>
                </article>
                <aside class="card">
                    <h2>What guides the product</h2>
                    <div class="principles">
                        <div class="principle"><span class="principle-mark">01</span><div><strong>Private by default</strong><span>Your journal and connected account data belong to your account.</span></div></div>
                        <div class="principle"><span class="principle-mark">02</span><div><strong>Read and review</strong><span>Exchange connections support journaling workflows—not trade execution.</span></div></div>
                        <div class="principle"><span class="principle-mark">03</span><div><strong>Useful over flashy</strong><span>Reports should help reveal patterns you can actually review.</span></div></div>
                        <div class="principle"><span class="principle-mark">04</span><div><strong>Built with users</strong><span>Early trader feedback will shape what TradeYatra becomes next.</span></div></div>
                    </div>
                </aside>
            </div>
            <div class="cta">
                <div><h2>Help shape TradeYatra</h2><p>Create your private journal and share honest feedback directly with the founder.</p></div>
                <a class="btn" href="{{ route('register') }}" data-analytics-event="registration_cta_clicked" data-analytics-placement="founder_page">Start your journal</a>
            </div>
        </section>
    </main>
    @include('partials.public-footer')
</body>
</html>
