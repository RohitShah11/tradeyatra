<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Track Shark Exchange and Delta Exchange trades in one private trading journal. Review weekly and monthly performance, P&amp;L calendars, notes, and trading discipline.">
    <meta name="robots" content="index,follow,max-image-preview:large">
    <meta name="theme-color" content="#071014">
    <link rel="canonical" href="{{ route('home') }}">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="TradeYatra">
    <meta property="og:title" content="Trading Journal for Shark &amp; Delta Exchange | TradeYatra">
    <meta property="og:description" content="Connect Shark and Delta Exchange, review P&amp;L, and build a more consistent trading process in one focused journal.">
    <meta property="og:url" content="{{ route('home') }}">
    <meta property="og:image" content="{{ asset('images/branding/tradeyatra-logo.png') }}">
    <meta property="og:image:alt" content="TradeYatra trading journal">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Trading Journal for Shark &amp; Delta Exchange | TradeYatra">
    <meta name="twitter:description" content="Connect Shark and Delta Exchange, review P&amp;L, and build a more consistent trading process.">
    <meta name="twitter:image" content="{{ asset('images/branding/tradeyatra-logo.png') }}">
    <title>Trading Journal for Shark &amp; Delta Exchange | TradeYatra</title>
    <link rel="icon" type="image/png" href="{{ asset('images/branding/tradeyatra-icon-v2.png') }}">
    <link rel="preload" href="{{ asset('images/branding/tradeyatra-icon-v2.png') }}" as="image" fetchpriority="high">
    <style>
        :root {
            --bg: #050b13;
            --bg-2: #071a24;
            --panel: #0b1722;
            --panel-2: #101f2d;
            --line: rgba(120, 214, 255, .18);
            --text: #eef8ff;
            --muted: #8fa8b8;
            --cyan: #18c7ff;
            --cyan-2: #66ecff;
            --green: #20e6a4;
            --orange: #ff9c36;
            --red: #ff6171;
        }
        * { box-sizing: border-box; }
        html { scroll-behavior: smooth; }
        body {
            margin: 0;
            color: var(--text);
            background:
                radial-gradient(circle at 20% 10%, rgba(24, 199, 255, .18), transparent 28rem),
                radial-gradient(circle at 82% 4%, rgba(32, 230, 164, .12), transparent 24rem),
                linear-gradient(180deg, #041019 0%, var(--bg) 48%, #071018 100%);
            font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            line-height: 1.5;
            position:relative;
        }
        body:before { content:""; position:fixed; inset:0; pointer-events:none; z-index:-1; background-image:radial-gradient(circle at 6% 18%,rgba(25,199,181,.9) 0 1px,transparent 2px),radial-gradient(circle at 22% 7%,rgba(255,255,255,.6) 0 1px,transparent 2px),radial-gradient(circle at 42% 26%,rgba(25,199,181,.65) 0 1px,transparent 2px),radial-gradient(circle at 68% 11%,rgba(255,173,104,.8) 0 1px,transparent 2px),radial-gradient(circle at 92% 31%,rgba(25,199,181,.7) 0 1px,transparent 2px),radial-gradient(circle at 82% 76%,rgba(255,255,255,.45) 0 1px,transparent 2px); }
        body:after { content:""; position:absolute; width:680px; height:680px; left:-390px; top:-300px; border:1px solid rgba(255,122,26,.13); border-radius:50%; box-shadow:0 0 0 62px rgba(255,122,26,.035),0 0 0 124px rgba(25,199,181,.025); pointer-events:none; z-index:-1; }
        a { color: inherit; text-decoration: none; }
        .wrap { width: min(1180px, calc(100% - 36px)); margin: 0 auto; }
        .nav {
            position: sticky;
            top: 0;
            z-index: 20;
            border-top: 1px solid rgba(255,255,255,.08);
            border-bottom: 1px solid rgba(24,199,255,.16);
            background: linear-gradient(90deg, rgba(8,15,18,.96), rgba(5,17,21,.93));
            backdrop-filter: blur(18px);
            box-shadow: 0 10px 35px rgba(0,0,0,.2);
        }
        .nav-inner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
            min-height: 64px;
        }
        .brand { display: inline-flex; align-items: center; gap: 0; font-weight: 800; font-size: 18px; transition:opacity .18s ease; }
        .brand:hover { opacity:.86; }
        .brand-mark {
            width: 36px;
            height: 36px;
            border-radius: 0;
            display: grid;
            place-items: center;
            color: #031018;
            background: transparent;
            box-shadow: none;
        }
        .nav-links { display:flex; align-items:center; gap:3px; padding:4px; border:1px solid rgba(255,255,255,.07); border-radius:11px; color:var(--muted); background:rgba(255,255,255,.025); font-weight:600; font-size:13px; }
        .nav-links a { padding:7px 11px; border-radius:8px; transition:color .18s ease,background .18s ease; }
        .nav-links a:hover { color:var(--text); background:rgba(255,122,26,.1); }
        .nav-actions { display: flex; align-items: center; gap: 10px; }
        .nav-actions .public-theme-toggle { width:36px; min-width:36px; height:36px; border-radius:8px; }
        .btn {
            min-height: 36px;
            border-radius: 8px;
            padding: 8px 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            font-weight: 800;
            border: 1px solid rgba(255,255,255,.12);
            color: var(--text);
            background: rgba(255,255,255,.06);
            font-size:13px;
            line-height:1;
        }
        .btn.primary {
            border-color: transparent;
            color: #031018;
            background: linear-gradient(135deg, var(--cyan), var(--green));
            box-shadow: 0 16px 44px rgba(24, 199, 255, .24);
        }
        .btn:hover { transform: translateY(-1px); }
        .hero { padding: 84px 0 54px; overflow: hidden; }
        .hero-grid { display: grid; grid-template-columns: minmax(0, .98fr) minmax(420px, 1.02fr); align-items: center; gap: 48px; }
        .eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 9px;
            color: var(--cyan-2);
            border: 1px solid rgba(24, 199, 255, .24);
            background: rgba(24, 199, 255, .08);
            border-radius: 999px;
            padding: 7px 11px;
            font-weight: 800;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: .08em;
        }
        .pulse { width: 8px; height: 8px; border-radius: 50%; background: var(--green); box-shadow: 0 0 16px var(--green); }
        h1 { margin: 22px 0 18px; font-size: clamp(36px, 5.2vw, 60px); line-height: 1.02; letter-spacing: 0; }
        .lead { max-width: 620px; color: #b5c9d7; font-size: 18px; margin: 0 0 28px; }
        .hero-actions { display: flex; flex-wrap: wrap; gap: 12px; margin-bottom: 28px; }
        .hero-proof { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 12px; max-width: 610px; }
        .proof {
            border: 1px solid var(--line);
            background: rgba(255,255,255,.045);
            border-radius: 8px;
            padding: 14px;
        }
        .proof strong { display: block; font-size: 25px; color: var(--text); }
        .proof span { color: var(--muted); font-size: 13px; }
        .terminal {
            position: relative;
            border: 1px solid rgba(102, 236, 255, .22);
            border-radius: 16px;
            background: linear-gradient(180deg, rgba(15, 33, 47, .96), rgba(7, 17, 27, .96));
            box-shadow:0 38px 90px rgba(0,0,0,.42),0 0 0 1px rgba(255,122,26,.05),0 0 70px rgba(19,189,208,.08),inset 0 1px 0 rgba(255,255,255,.1);
            overflow: hidden;
            transform:perspective(1200px) rotateY(-1.2deg) rotateX(.4deg);
            transition:transform .45s ease,box-shadow .45s ease,border-color .45s ease;
        }
        .terminal:hover { transform:perspective(1200px) rotateY(0) rotateX(0) translateY(-5px); border-color:rgba(25,199,181,.38); box-shadow:0 48px 110px rgba(0,0,0,.48),0 0 85px rgba(19,189,208,.13),inset 0 1px 0 rgba(255,255,255,.11); }
        .terminal-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            padding: 15px 18px;
            border-bottom: 1px solid rgba(255,255,255,.08);
            color: var(--muted);
            font-size: 13px;
        }
        .terminal-live { display:inline-flex; align-items:center; gap:7px; color:#b9d1d8; font-weight:800; }
        .terminal-live:before { content:""; width:7px; height:7px; border-radius:50%; background:var(--green); box-shadow:0 0 12px rgba(32,230,164,.8); animation:livePulse 1.8s ease-in-out infinite; }
        .dots { display: flex; gap: 6px; }
        .dots i { width: 10px; height: 10px; border-radius: 50%; background: var(--red); }
        .dots i:nth-child(2) { background: var(--orange); }
        .dots i:nth-child(3) { background: var(--green); }
        .sync-visual { position:relative; overflow:hidden; padding:26px; background:radial-gradient(circle at 50% 31%,rgba(25,199,181,.17),transparent 29%),radial-gradient(circle at 7% 5%,rgba(19,189,208,.09),transparent 24%),radial-gradient(circle at 93% 8%,rgba(255,122,26,.09),transparent 24%),linear-gradient(rgba(255,255,255,.035) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.035) 1px,transparent 1px); background-size:auto,auto,auto,64px 64px,64px 64px; }
        .sync-visual:after { content:""; position:absolute; inset:0; background:linear-gradient(110deg,transparent 25%,rgba(24,199,255,.045) 48%,transparent 70%); transform:translateX(-100%); animation:syncSheen 5s ease-in-out infinite; pointer-events:none; }
        .sync-network { position:relative; z-index:1; display:grid; grid-template-columns:minmax(0,1fr) 92px minmax(0,1fr); align-items:center; gap:14px; min-height:148px; }
        .broker-node { position:relative; min-width:0; padding:15px; border:1px solid color-mix(in srgb,var(--node-color) 24%,rgba(255,255,255,.08)); border-radius:13px; background:linear-gradient(145deg,color-mix(in srgb,var(--node-color) 7%,rgba(4,17,24,.9)),rgba(4,17,24,.78)); box-shadow:0 16px 35px rgba(0,0,0,.24),inset 0 1px 0 rgba(255,255,255,.045); animation:nodeFloat 4s ease-in-out infinite; transition:border-color .25s ease,box-shadow .25s ease; }
        .broker-node:hover { border-color:color-mix(in srgb,var(--node-color) 48%,transparent); box-shadow:0 20px 42px rgba(0,0,0,.3),0 0 30px color-mix(in srgb,var(--node-color) 10%,transparent); }
        .broker-node.delta { animation-delay:-2s; }
        .broker-node:after { content:""; position:absolute; top:50%; width:30px; height:2px; background:linear-gradient(90deg,var(--node-color),transparent); background-size:200% 100%; animation:dataFlow 1.8s linear infinite; }
        .broker-node.shark:after { left:100%; }
        .broker-node.delta:after { right:100%; transform:rotate(180deg); }
        .broker-node-head { display:flex; align-items:center; gap:9px; }
        .broker-node-mark { width:32px; height:32px; flex:0 0 32px; display:grid; place-items:center; border-radius:9px; color:#fff; background:linear-gradient(145deg,var(--node-color),var(--node-dark)); font-size:11px; font-weight:900; }
        .broker-node strong,.broker-node small { display:block; }
        .broker-node strong { font-size:12px; }
        .broker-node small { margin-top:2px; color:var(--muted); font-size:9px; }
        .broker-node-status { display:flex; align-items:center; gap:6px; margin-top:12px; color:#b9d1d8; font-size:9px; font-weight:800; }
        .broker-node-status:before { content:""; width:6px; height:6px; border-radius:50%; background:var(--green); }
        .broker-node.shark { --node-color:#13bdd0; --node-dark:#087c99; }
        .broker-node.delta { --node-color:#ff7a1a; --node-dark:#d74606; }
        .journal-hub { position:relative; z-index:2; width:100px; height:100px; justify-self:center; border:1px solid rgba(255,122,26,.32); border-radius:50%; background:conic-gradient(from 35deg,rgba(255,122,26,.2),rgba(3,16,24,.94) 24%,rgba(24,199,255,.22) 48%,rgba(3,16,24,.94) 72%,rgba(255,122,26,.2)); box-shadow:0 0 0 7px rgba(24,199,255,.03),0 0 0 14px rgba(255,122,26,.018),-12px 18px 48px rgba(24,199,255,.14),12px 18px 48px rgba(255,122,26,.14); }
        .journal-hub:before { content:""; position:absolute; inset:7px; border-radius:50%; background:repeating-conic-gradient(from 12deg,rgba(24,199,255,.88) 0 1deg,transparent 1deg 9deg,rgba(255,122,26,.88) 9deg 10deg,transparent 10deg 18deg),repeating-radial-gradient(circle,transparent 0 9px,rgba(24,199,255,.18) 10px 11px,transparent 12px 18px,rgba(255,122,26,.16) 19px 20px,transparent 21px 27px); -webkit-mask:radial-gradient(circle,transparent 0 24%,#000 26% 100%); mask:radial-gradient(circle,transparent 0 24%,#000 26% 100%); animation:matrixSpin 18s linear infinite; opacity:.92; }
        .journal-hub:after { content:""; position:absolute; inset:-9px; border:1px solid rgba(24,199,255,.19); border-radius:50%; box-shadow:inset 0 0 0 1px rgba(255,122,26,.08); animation:hubPulse 2.6s ease-out infinite; }
        .hub-core { position:absolute; z-index:3; inset:50% auto auto 50%; width:50px; height:50px; display:grid; place-items:center; border:1px solid rgba(255,255,255,.17); border-radius:50%; background:radial-gradient(circle at 35% 28%,rgba(255,255,255,.09),transparent 30%),linear-gradient(145deg,#102831,#07141a); box-shadow:-7px 0 20px rgba(24,199,255,.28),7px 0 20px rgba(255,122,26,.25),inset 0 1px 0 rgba(255,255,255,.12); transform:translate(-50%,-50%); }
        .hub-core img { display:block; width:34px; height:34px; object-fit:contain; filter:drop-shadow(0 4px 8px rgba(0,0,0,.32)); }
        .hub-orbit { position:absolute; z-index:4; width:6px; height:6px; border:1px solid rgba(255,255,255,.55); border-radius:50%; background:var(--orbit-color); box-shadow:0 0 12px var(--orbit-color); }
        .hub-orbit.one { --orbit-color:#18c7ff; left:8px; top:45px; animation:orbitOne 5s ease-in-out infinite; }
        .hub-orbit.two { --orbit-color:#ff7a1a; right:14px; top:17px; animation:orbitTwo 6s ease-in-out infinite; }
        .hub-orbit.three { --orbit-color:#ff9a47; right:12px; bottom:18px; animation:orbitThree 4.5s ease-in-out infinite; }
        .sync-caption { position:relative; z-index:1; margin:-2px 0 18px; color:var(--muted); font-size:10px; text-align:center; }
        .live-metrics { position:relative; z-index:1; display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:9px; }
        .live-metric { position:relative; overflow:hidden; min-width:0; padding:13px; border:1px solid rgba(255,255,255,.085); border-radius:11px; background:linear-gradient(145deg,rgba(3,14,20,.74),rgba(15,35,41,.48)); transition:transform .22s ease,border-color .22s ease; }
        .live-metric:before { content:""; position:absolute; inset:0 auto 0 0; width:2px; background:var(--metric-color,#19c7b5); box-shadow:0 0 14px var(--metric-color,#19c7b5); }
        .live-metric:hover { transform:translateY(-2px); border-color:rgba(255,255,255,.15); }
        .live-metric small,.live-metric strong { display:block; }
        .live-metric small { color:var(--muted); font-size:8px; font-weight:900; text-transform:uppercase; letter-spacing:.07em; }
        .live-metric strong { margin-top:4px; font-size:16px; }
        .live-metric.primary { --metric-color:#20e6a4; }
        .live-metric.primary strong { color:var(--green); }
        .live-metric.win { --metric-color:#18c7ff; }
        .live-metric.rr { --metric-color:#ff7a1a; }
        .sync-activity { border-top:1px solid rgba(255,255,255,.08); }
        .sync-activity-head { display:flex; align-items:center; justify-content:space-between; gap:12px; padding:12px 16px 8px; color:var(--muted); font-size:9px; font-weight:900; text-transform:uppercase; letter-spacing:.07em; }
        .sync-activity-head span:last-child { color:var(--green); }
        .sync-row { display:grid; grid-template-columns:auto minmax(0,1fr) auto; align-items:center; gap:10px; padding:11px 16px; border-top:1px solid rgba(255,255,255,.055); transition:background .2s ease; }
        .sync-row:hover { background:rgba(255,255,255,.025); }
        .sync-source { width:25px; height:25px; display:grid; place-items:center; border-radius:7px; color:#fff; font-size:9px; font-weight:900; }
        .sync-source.shark { background:linear-gradient(145deg,#13bdd0,#087c99); }
        .sync-source.delta { background:linear-gradient(145deg,#ff7a1a,#d74606); }
        .sync-row strong,.sync-row small { display:block; }
        .sync-row strong { font-size:11px; }
        .sync-row small { color:var(--muted); font-size:9px; }
        .sync-result { color:var(--green); font-size:10px; font-weight:900; }
        .terminal-security { display:flex; align-items:center; justify-content:center; gap:8px; flex-wrap:wrap; padding:10px 14px; border-top:1px solid rgba(255,255,255,.07); color:#8da8b0; background:rgba(2,11,16,.32); font-size:8px; font-weight:900; text-transform:uppercase; letter-spacing:.06em; }
        .terminal-security span { display:inline-flex; align-items:center; gap:5px; }
        .terminal-security span:before { content:""; width:4px; height:4px; border-radius:50%; background:var(--green); box-shadow:0 0 8px rgba(32,230,164,.65); }
        .terminal-security i { color:rgba(255,255,255,.2); font-style:normal; }
        @keyframes livePulse { 50% { opacity:.4; transform:scale(.72); } }
        @keyframes syncSheen { 0%,20% { transform:translateX(-100%); } 65%,100% { transform:translateX(100%); } }
        @keyframes dataFlow { to { background-position:-200% 0; } }
        @keyframes nodeFloat { 50% { transform:translateY(-4px); } }
        @keyframes hubPulse { 0% { opacity:.8; transform:scale(.92); } 80%,100% { opacity:0; transform:scale(1.18); } }
        @keyframes matrixSpin { to { transform:rotate(360deg); } }
        @keyframes orbitOne { 50% { transform:translate(68px,-25px) scale(.7); } }
        @keyframes orbitTwo { 50% { transform:translate(-60px,67px) scale(1.2); } }
        @keyframes orbitThree { 50% { transform:translate(-67px,-48px) scale(.8); } }
        .chart {
            height: 248px;
            padding: 26px 18px 18px;
            background:
                linear-gradient(rgba(255,255,255,.045) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,.045) 1px, transparent 1px);
            background-size: 100% 62px, 78px 100%;
            position: relative;
        }
        .path {
            position: absolute;
            inset: 34px 20px 42px;
            clip-path: polygon(0 68%, 10% 58%, 18% 64%, 29% 38%, 39% 48%, 50% 30%, 60% 38%, 72% 18%, 82% 28%, 100% 10%, 100% 100%, 0 100%);
            background: linear-gradient(180deg, rgba(24,199,255,.68), rgba(24,199,255,.03));
            border-top: 3px solid var(--cyan);
        }
        .trade-card {
            position: absolute;
            right: 24px;
            top: 34px;
            width: 210px;
            border: 1px solid rgba(32,230,164,.32);
            border-radius: 8px;
            background: rgba(3, 16, 24, .86);
            padding: 14px;
            box-shadow: 0 18px 40px rgba(0,0,0,.3);
        }
        .trade-card small, .mini small { color: var(--muted); font-weight: 700; }
        .gain { color: var(--green); font-size: 28px; font-weight: 800; margin-top: 4px; }
        .journal-row {
            display: grid;
            grid-template-columns: 1fr auto auto;
            gap: 12px;
            align-items: center;
            padding: 13px 16px;
            border-top: 1px solid rgba(255,255,255,.08);
            font-size: 14px;
        }
        .pair { font-weight: 800; }
        .tag { border-radius: 999px; padding: 4px 8px; font-size: 12px; color: #031018; background: var(--green); font-weight: 800; }
        .loss { color: var(--red); }
        .dashboard-showcase { position:relative; overflow:hidden; }
        .dashboard-showcase:before {
            content:"";
            position:absolute;
            width:540px;
            height:540px;
            right:-230px;
            top:80px;
            border-radius:50%;
            background:radial-gradient(circle,rgba(25,199,181,.12),transparent 68%);
            pointer-events:none;
        }
        .showcase-copy { max-width:760px; margin-bottom:28px; }
        .showcase-copy h2 { margin:16px 0 13px; font-size:clamp(32px,4.5vw,52px); line-height:1.04; }
        .showcase-copy p { max-width:680px; margin:0; color:var(--muted); font-size:17px; }
        .showcase-points { display:flex; flex-wrap:wrap; gap:9px; margin-top:20px; }
        .showcase-point { display:inline-flex; align-items:center; gap:8px; padding:8px 11px; border:1px solid var(--line); border-radius:999px; color:#c8d9e4; background:rgba(255,255,255,.04); font-size:13px; font-weight:800; }
        .showcase-point:before { content:""; width:7px; height:7px; border-radius:50%; background:linear-gradient(135deg,#ff7a1a,#19c7b5); box-shadow:0 0 12px rgba(25,199,181,.55); }
        .dashboard-preview-card {
            position:relative;
            padding:7px;
            border:1px solid rgba(255,122,26,.34);
            border-radius:14px;
            background:linear-gradient(115deg,rgba(255,76,0,.5),rgba(191,73,17,.16) 40%,rgba(18,165,186,.42) 72%,rgba(17,165,189,.3));
            box-shadow:0 34px 90px rgba(0,0,0,.34),0 0 60px rgba(18,165,186,.08);
            transform-style:preserve-3d;
            transition:transform .45s ease,box-shadow .45s ease;
        }
        .dashboard-preview-card:hover { transform:translateY(-5px); box-shadow:0 42px 100px rgba(0,0,0,.4),0 0 70px rgba(18,165,186,.12); }
        .dashboard-preview-window { overflow:hidden; border-radius:9px; background:#071014; }
        .dashboard-preview-window img { display:block; width:100%; height:auto; background:#071014; }
        .dashboard-preview-caption { display:flex; justify-content:space-between; align-items:center; gap:20px; margin-top:20px; color:var(--muted); font-size:13px; }
        .dashboard-preview-caption strong { color:var(--text); font-size:15px; }
        .supported-exchanges { padding-top:26px; }
        .exchange-intro { display:flex; align-items:end; justify-content:space-between; gap:24px; margin-bottom:22px; }
        .exchange-intro h2 { margin:13px 0 0; font-size:clamp(28px,4vw,42px); line-height:1.08; }
        .exchange-intro p { max-width:470px; margin:0; color:var(--muted); }
        .exchange-support-summary { display:flex; align-items:center; gap:9px; margin-bottom:14px; color:var(--muted); font-size:12px; font-weight:800; }
        .exchange-support-summary:before { content:""; width:8px; height:8px; border-radius:50%; background:var(--green); box-shadow:0 0 14px rgba(32,230,164,.7); }
        .exchange-grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:16px; }
        .exchange-card {
            position:relative;
            overflow:hidden;
            display:grid;
            grid-template-columns:auto minmax(0,1fr);
            gap:18px;
            padding:24px;
            border:1px solid var(--line);
            border-radius:12px;
            background:linear-gradient(145deg,rgba(255,255,255,.055),rgba(255,122,26,.035));
            transition:transform .25s ease,border-color .25s ease,box-shadow .25s ease;
        }
        .exchange-card:after { content:""; position:absolute; width:180px; height:180px; right:-85px; top:-100px; border-radius:50%; pointer-events:none; }
        .exchange-card.shark:after { background:radial-gradient(circle,rgba(18,165,186,.19),transparent 68%); }
        .exchange-card.delta:after { background:radial-gradient(circle,rgba(255,76,0,.2),transparent 68%); }
        .exchange-card:hover { transform:translateY(-4px); border-color:rgba(25,199,181,.4); box-shadow:0 22px 55px rgba(0,0,0,.2); }
        .exchange-card.delta:hover { border-color:rgba(255,122,26,.42); }
        .exchange-badge { width:54px; height:54px; display:grid; place-items:center; border-radius:14px; color:#fff; font-size:22px; font-weight:900; box-shadow:inset 0 1px 0 rgba(255,255,255,.22); }
        .shark .exchange-badge { background:linear-gradient(145deg,#13bdd0,#087c99); }
        .delta .exchange-badge { background:linear-gradient(145deg,#ff7a1a,#d74606); }
        .exchange-card h3 { margin:0 0 7px; font-size:22px; }
        .exchange-card p { margin:0; color:var(--muted); }
        .exchange-card-top { display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap; margin-bottom:7px; }
        .exchange-card-top h3 { margin:0; }
        .supported-badge { position:relative; z-index:1; flex:0 0 auto; padding:4px 7px; border:1px solid rgba(32,230,164,.2); border-radius:999px; color:var(--green); background:rgba(32,230,164,.07); font-size:9px; font-weight:900; text-transform:uppercase; letter-spacing:.05em; }
        .exchange-detail { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:8px; margin:16px 0; }
        .exchange-detail div { min-width:0; padding:10px; border:1px solid rgba(255,255,255,.075); border-radius:8px; background:rgba(0,0,0,.1); }
        .exchange-detail small,.exchange-detail strong { display:block; }
        .exchange-detail small { color:var(--muted); font-size:9px; font-weight:800; text-transform:uppercase; letter-spacing:.05em; }
        .exchange-detail strong { margin-top:3px; font-size:12px; }
        .exchange-capabilities { display:flex; flex-wrap:wrap; gap:7px; margin:16px 0 18px; }
        .exchange-capabilities span { padding:5px 8px; border:1px solid rgba(255,255,255,.09); border-radius:6px; color:#bfd0d7; background:rgba(0,0,0,.12); font-size:11px; font-weight:800; }
        .exchange-link { position:relative; z-index:1; display:inline-flex; align-items:center; gap:8px; color:var(--cyan-2); font-size:13px; font-weight:900; }
        .exchange-link:hover { color:var(--text); }
        .exchange-link span { transition:transform .2s ease; }
        .exchange-link:hover span { transform:translateX(3px); }
        .broker-workflow { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:10px; margin-top:16px; }
        .broker-workflow-step { position:relative; min-width:0; padding:15px 16px; border:1px solid var(--line); border-radius:10px; background:rgba(255,255,255,.025); }
        .broker-workflow-step b { display:block; margin-bottom:4px; color:var(--orange-2); font-size:11px; text-transform:uppercase; letter-spacing:.07em; }
        .broker-workflow-step strong { display:block; font-size:14px; }
        .broker-workflow-step span { display:block; margin-top:3px; color:var(--muted); font-size:11px; }
        .exchange-footnote { margin:14px 0 0; color:var(--muted); font-size:12px; text-align:center; }
        .dashboard-showcase.reveal-enabled .showcase-copy,
        .dashboard-showcase.reveal-enabled .dashboard-preview-card,
        .dashboard-showcase.reveal-enabled .dashboard-preview-caption { opacity:0; transform:translateY(22px); }
        .dashboard-showcase.reveal-enabled.is-visible .showcase-copy,
        .dashboard-showcase.reveal-enabled.is-visible .dashboard-preview-card,
        .dashboard-showcase.reveal-enabled.is-visible .dashboard-preview-caption { opacity:1; transform:translateY(0); transition:opacity .7s ease,transform .7s cubic-bezier(.2,.75,.25,1); }
        .dashboard-showcase.reveal-enabled.is-visible .dashboard-preview-card { transition-delay:.12s; }
        .dashboard-showcase.reveal-enabled.is-visible .dashboard-preview-caption { transition-delay:.24s; }
        section { padding: 72px 0; scroll-margin-top: 78px; }
        main > section { content-visibility:visible; }
        .section-head { display: flex; justify-content: space-between; gap: 24px; align-items: end; margin-bottom: 28px; }
        .section-head h2 { margin: 0; font-size: clamp(30px, 4vw, 46px); line-height: 1.05; }
        .section-head p { margin: 0; max-width: 430px; color: var(--muted); }
        .features { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 16px; }
        .feature, .mini, .step, .faq-item {
            border: 1px solid var(--line);
            border-radius: 8px;
            background: rgba(255,255,255,.045);
            padding: 22px;
        }
        .icon {
            width: 38px;
            height: 38px;
            border-radius: 8px;
            display: grid;
            place-items: center;
            background: rgba(24,199,255,.12);
            color: var(--cyan-2);
            margin-bottom: 16px;
            font-weight: 900;
        }
        .feature h3, .step h3, .faq-item h3 { margin: 0 0 8px; font-size: 19px; }
        .feature p, .step p, .faq-item p { margin: 0; color: var(--muted); }
        .product-tour { position:relative; overflow:hidden; }
        .product-tour:before {
            content:"";
            position:absolute;
            width:620px;
            height:620px;
            left:50%;
            top:90px;
            transform:translateX(-50%);
            border-radius:50%;
            background:radial-gradient(circle,rgba(25,199,181,.09),transparent 68%);
            pointer-events:none;
        }
        .product-grid {
            position:relative;
            display:grid;
            grid-template-columns:repeat(12,minmax(0,1fr));
            gap:16px;
        }
        .product-card {
            position:relative;
            overflow:hidden;
            min-height:250px;
            padding:26px;
            border:1px solid var(--line);
            border-radius:14px;
            background:linear-gradient(145deg,rgba(255,255,255,.065),rgba(255,122,26,.025));
            transition:transform .25s ease,border-color .25s ease,box-shadow .25s ease;
        }
        .product-card:hover { transform:translateY(-4px); border-color:rgba(25,199,181,.38); box-shadow:0 24px 60px rgba(0,0,0,.2); }
        .product-card.large { grid-column:span 7; }
        .product-card.medium { grid-column:span 5; }
        .product-card.third { grid-column:span 4; min-height:230px; }
        .product-number { display:inline-flex; align-items:center; gap:9px; color:var(--cyan-2); font-size:11px; font-weight:900; letter-spacing:.12em; text-transform:uppercase; }
        .product-number:before { content:""; width:22px; height:1px; background:currentColor; }
        .product-card h3 { max-width:480px; margin:32px 0 10px; font-size:clamp(22px,3vw,32px); line-height:1.08; }
        .product-card p { max-width:520px; margin:0; color:var(--muted); }
        .product-tags { display:flex; flex-wrap:wrap; gap:7px; margin-top:24px; }
        .product-tags span { padding:6px 9px; border:1px solid rgba(255,255,255,.09); border-radius:999px; color:#bfd0d7; background:rgba(0,0,0,.14); font-size:11px; font-weight:800; }
        .product-glow { position:absolute; width:180px; height:180px; right:-70px; bottom:-80px; border-radius:50%; background:radial-gradient(circle,rgba(255,122,26,.18),transparent 68%); pointer-events:none; }
        .product-card:nth-child(even) .product-glow { background:radial-gradient(circle,rgba(25,199,181,.17),transparent 68%); }
        .visual-gallery { display:grid; gap:22px; margin-top:24px; }
        .visual-story {
            display:grid;
            grid-template-columns:minmax(280px,.68fr) minmax(0,1.32fr);
            gap:0;
            overflow:hidden;
            border:1px solid rgba(255,255,255,.1);
            border-radius:16px;
            background:linear-gradient(145deg,rgba(255,255,255,.05),rgba(255,122,26,.025));
            box-shadow:0 24px 70px rgba(0,0,0,.18);
        }
        .visual-story:nth-child(even) { grid-template-columns:minmax(0,1.32fr) minmax(280px,.68fr); }
        .visual-story:nth-child(even) .visual-copy { order:2; }
        .visual-story:nth-child(even) .visual-frame { order:1; border-left:0; border-right:1px solid rgba(255,255,255,.08); }
        .visual-copy { display:flex; flex-direction:column; justify-content:center; padding:32px; }
        .visual-copy .product-number { margin-bottom:18px; }
        .visual-copy h3 { margin:0 0 12px; font-size:clamp(25px,3.2vw,38px); line-height:1.07; }
        .visual-copy p { margin:0; color:var(--muted); }
        .visual-list { display:grid; gap:9px; margin-top:22px; }
        .visual-list span { display:flex; align-items:flex-start; gap:9px; color:#c7d8de; font-size:12px; font-weight:700; }
        .visual-list span:before { content:"✓"; color:var(--green); font-weight:900; }
        .visual-frame { min-width:0; display:flex; align-items:center; padding:10px; border-left:1px solid rgba(255,255,255,.08); background:rgba(0,0,0,.16); }
        .visual-frame img { display:block; width:100%; height:auto; min-height:0; object-fit:contain; object-position:center; border-radius:10px; background:#071014; }
        .privacy-strip {
            display:grid;
            grid-template-columns:minmax(0,1.1fr) minmax(300px,.9fr);
            gap:34px;
            align-items:center;
            padding:34px;
            border:1px solid rgba(25,199,181,.25);
            border-radius:14px;
            background:linear-gradient(125deg,rgba(25,199,181,.09),rgba(255,122,26,.055));
        }
        .privacy-strip h2 { margin:13px 0 12px; font-size:clamp(28px,4vw,44px); line-height:1.06; }
        .privacy-strip p { margin:0; color:var(--muted); }
        .privacy-points { display:grid; gap:10px; }
        .privacy-point { display:flex; align-items:flex-start; gap:11px; padding:12px 14px; border:1px solid rgba(255,255,255,.08); border-radius:9px; background:rgba(0,0,0,.12); color:#d3e2e7; font-size:13px; }
        .privacy-point b { color:var(--green); }
        .analytics {
            display: grid;
            grid-template-columns: .9fr 1.1fr;
            gap: 18px;
            align-items: stretch;
        }
        .mini-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px; }
        .mini strong { display: block; font-size: 34px; margin-top: 8px; }
        .review-panel {
            border: 1px solid rgba(102, 236, 255, .2);
            border-radius: 8px;
            background: linear-gradient(135deg, rgba(24,199,255,.12), rgba(32,230,164,.06));
            padding: 24px;
        }
        .review-panel h3 { margin: 0 0 14px; font-size: 24px; }
        .checklist { display: grid; gap: 10px; margin-top: 20px; }
        .check { display: flex; gap: 10px; align-items: center; color: #c8d9e4; }
        .check b { color: var(--green); }
        .steps { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 14px; counter-reset: steps; }
        .step { position: relative; min-height: 180px; }
        .step:before {
            counter-increment: steps;
            content: "0" counter(steps);
            display: inline-flex;
            margin-bottom: 18px;
            color: var(--cyan-2);
            font-weight: 900;
        }
        .cta {
            border: 1px solid rgba(32,230,164,.26);
            border-radius: 8px;
            padding: 38px;
            background:
                linear-gradient(135deg, rgba(24,199,255,.16), rgba(32,230,164,.09)),
                rgba(255,255,255,.04);
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 22px;
            align-items: center;
        }
        .cta h2 { margin: 0 0 10px; font-size: clamp(28px, 4vw, 48px); line-height: 1.05; }
        .cta p { margin: 0; color: #c3d5df; max-width: 650px; }
        .faq { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px; }
        .contact-grid { display:grid; grid-template-columns:.8fr 1.2fr; gap:38px; align-items:start; }
        .contact-copy h2 { margin:14px 0; font-size:clamp(30px,4vw,46px); line-height:1.08; }
        .contact-copy p { color:var(--muted); margin:0; max-width:460px; }
        .contact-email { display:inline-flex; align-items:center; gap:9px; margin-top:18px; color:var(--cyan-2); font-weight:800; }
        .contact-email:hover { color:var(--text); }
        .contact-note { margin-top:24px; padding:16px; border-left:3px solid var(--green); background:rgba(25,199,181,.07); color:#c5d7de; font-size:14px; }
        .contact-form { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:18px; padding:26px; border:1px solid var(--line); border-radius:8px; background:rgba(255,255,255,.045); }
        .form-field { display:grid; gap:7px; }
        .form-field.full { grid-column:1/-1; }
        .form-field label { font-size:13px; font-weight:800; color:#d8e7ec; }
        .form-field input, .form-field select, .form-field textarea { width:100%; border:1px solid rgba(255,255,255,.14); border-radius:8px; padding:12px 13px; color:var(--text); background:#0b171c; font:inherit; outline:none; }
        .form-field textarea { min-height:150px; resize:vertical; }
        .form-field input:focus, .form-field select:focus, .form-field textarea:focus { border-color:var(--cyan); box-shadow:0 0 0 3px rgba(255,122,26,.12); }
        .form-error { color:#ff9aa6; font-size:12px; }
        .form-status { grid-column:1/-1; padding:13px 15px; border-radius:8px; font-size:14px; }
        .form-status.success { color:#b8f7e8; border:1px solid rgba(25,199,181,.3); background:rgba(25,199,181,.1); }
        .form-status.error { color:#ffd3d8; border:1px solid rgba(251,113,133,.3); background:rgba(251,113,133,.1); }
        .form-submit { grid-column:1/-1; display:flex; align-items:center; justify-content:space-between; gap:16px; }
        .form-submit small { color:var(--muted); }
        .form-submit button[disabled] { cursor:wait; opacity:.72; transform:none; }
        .honeypot { position:absolute!important; left:-10000px!important; width:1px!important; height:1px!important; overflow:hidden!important; }
        .toast-viewport { position:fixed; top:18px; right:18px; z-index:100; width:min(380px,calc(100vw - 36px)); display:grid; gap:10px; pointer-events:none; }
        .toast { position:relative; overflow:hidden; padding:13px 42px 13px 14px; border:1px solid; border-radius:8px; color:#fff; box-shadow:0 18px 50px rgba(0,0,0,.28); pointer-events:auto; animation:toast-in .18s ease-out; }
        .toast.success { border-color:#22c55e; background:#15803d; }
        .toast.error { border-color:#ef4444; background:#b91c1c; }
        .toast:before { content:""; position:absolute; inset:0 auto 0 0; width:4px; background:rgba(255,255,255,.7); }
        .toast-title { display:block; margin-bottom:2px; color:#fff; font-weight:900; }
        .toast-message { opacity:.88; }
        .toast-close { position:absolute; top:8px; right:8px; width:28px; height:28px; padding:0; border:1px solid rgba(255,255,255,.3); border-radius:8px; color:#fff; background:rgba(255,255,255,.12); cursor:pointer; }
        @keyframes toast-in { from { opacity:0; transform:translateY(-8px); } to { opacity:1; transform:translateY(0); } }
        html[data-public-theme="light"] .form-field label { color:#273942; }
        html[data-public-theme="light"] .form-field input,
        html[data-public-theme="light"] .form-field select,
        html[data-public-theme="light"] .form-field textarea { color:#17232b; background:#fff; border-color:rgba(22,139,216,.2); }
        html[data-public-theme="light"] body:before {
            opacity:.82;
            background-image:
                radial-gradient(circle at 6% 18%,rgba(0,135,151,.9) 0 1.4px,transparent 2.5px),
                radial-gradient(circle at 22% 7%,rgba(191,73,17,.72) 0 1.2px,transparent 2.3px),
                radial-gradient(circle at 42% 26%,rgba(0,135,151,.72) 0 1.3px,transparent 2.4px),
                radial-gradient(circle at 68% 11%,rgba(191,73,17,.78) 0 1.3px,transparent 2.4px),
                radial-gradient(circle at 92% 31%,rgba(0,135,151,.76) 0 1.4px,transparent 2.5px),
                radial-gradient(circle at 82% 76%,rgba(191,73,17,.58) 0 1.2px,transparent 2.3px);
        }
        html[data-public-theme="light"] body:after {
            border-color:rgba(191,73,17,.3);
            box-shadow:
                0 0 0 62px rgba(255,76,0,.075),
                0 0 0 124px rgba(18,165,186,.075),
                0 0 85px rgba(18,165,186,.1);
        }
        html[data-public-theme="light"] .terminal {
            color:#f7fbfc;
            border-color:rgba(191,73,17,.34);
            background:linear-gradient(145deg,#14272e,#081418) !important;
            box-shadow:0 26px 70px rgba(7,20,24,.28),0 10px 30px rgba(18,165,186,.13);
        }
        html[data-public-theme="light"] .terminal-head {
            color:#94aeb5 !important;
            border-bottom-color:rgba(255,255,255,.09);
        }
        html[data-public-theme="light"] .terminal .chart {
            background-color:#11242a;
        }
        html[data-public-theme="light"] .terminal .trade-card {
            color:#f7fbfc;
            background:rgba(3,16,24,.94);
        }
        html[data-public-theme="light"] .terminal .journal-row {
            color:#f7fbfc;
            border-top-color:rgba(255,255,255,.09);
        }
        html[data-public-theme="light"] .dashboard-preview-card { box-shadow:0 30px 75px rgba(30,54,62,.2),0 0 55px rgba(18,165,186,.1); }
        html[data-public-theme="light"] .dashboard-preview-window { background:#071014; }
        html[data-public-theme="light"] .showcase-point { color:#354e57; background:rgba(255,255,255,.72); }
        html[data-public-theme="light"] .exchange-card { background:linear-gradient(145deg,rgba(255,255,255,.94),rgba(255,122,26,.045)); box-shadow:0 16px 42px rgba(35,71,82,.08); }
        html[data-public-theme="light"] .exchange-capabilities span { color:#49636c; border-color:rgba(20,80,92,.12); background:rgba(238,247,249,.85); }
        html[data-public-theme="light"] .product-card { background:linear-gradient(145deg,rgba(255,255,255,.96),rgba(255,122,26,.045)); box-shadow:0 16px 42px rgba(35,71,82,.07); }
        html[data-public-theme="light"] .product-tags span { color:#49636c; border-color:rgba(20,80,92,.12); background:rgba(238,247,249,.85); }
        html[data-public-theme="light"] .visual-story { background:linear-gradient(145deg,rgba(255,255,255,.96),rgba(255,122,26,.045)); box-shadow:0 18px 52px rgba(35,71,82,.1); }
        html[data-public-theme="light"] .visual-list span { color:#405a63; }
        html[data-public-theme="light"] .privacy-point { color:#354e57; border-color:rgba(20,80,92,.12); background:rgba(255,255,255,.62); }
        footer {
            border-top: 1px solid rgba(255,255,255,.08);
            padding: 28px 0;
            color: var(--muted);
        }
        .footer-inner { display: flex; justify-content: space-between; gap: 20px; flex-wrap: wrap; }
        /* Shark Ember marketing theme */
        :root {
            --bg:#071014; --bg-2:#0c1a20; --panel:#0f1c22; --panel-2:#14272e;
            --line:rgba(255,122,26,.2); --text:#f7fbfc; --muted:#94aeb5;
            --cyan:#ff7a1a; --cyan-2:#ffad68; --green:#19c7b5;
            --orange:#fbbf24; --red:#fb7185;
        }
        body { background:radial-gradient(circle at 14% 4%,rgba(255,122,26,.23),transparent 36rem),radial-gradient(circle at 86% 8%,rgba(25,199,181,.14),transparent 30rem),linear-gradient(155deg,#071014 0%,#0b171c 50%,#050c0f 100%); }
        .nav { background:linear-gradient(90deg,rgba(7,16,20,.96),rgba(5,20,23,.94)); border-bottom-color:rgba(0,184,217,.17); }
        .brand-mark { background:transparent; color:inherit; box-shadow:none; }
        .btn.primary { background:linear-gradient(115deg,#ff4c00 0%,#bf4911 40%,#12a5ba 72%,#11a5bd 100%); color:#fff; border-color:transparent; box-shadow:0 16px 44px rgba(0,184,217,.22); }
        .eyebrow { color:#ffad68; border-color:rgba(255,122,26,.3); background:rgba(255,122,26,.1); }
        .pulse { background:#19c7b5; box-shadow:0 0 18px #19c7b5; }
        .terminal { border-color:rgba(255,122,26,.27); background:linear-gradient(145deg,rgba(20,39,46,.97),rgba(8,20,24,.98)); }
        .path { background:linear-gradient(180deg,rgba(255,122,26,.68),rgba(255,122,26,.03)); border-color:#ff7a1a; }
        .gain { color:#34d399; }
        .tag { background:#ff7a1a; color:#fff; }
        .feature,.mini,.step,.faq-item { background:linear-gradient(145deg,rgba(255,255,255,.055),rgba(255,122,26,.035)); }
        .icon { background:rgba(255,122,26,.13); color:#ffad68; }
        .review-panel,.cta { background:linear-gradient(135deg,rgba(255,122,26,.14),rgba(25,199,181,.06)); border-color:rgba(255,122,26,.24); }
        @media (max-width: 980px) {
            .nav-links { display: none; }
            .hero-grid, .analytics, .cta, .contact-grid { grid-template-columns: 1fr; }
            .terminal { max-width: 680px; }
            .features, .steps { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .product-card.large, .product-card.medium, .product-card.third { grid-column:span 6; }
            .privacy-strip { grid-template-columns:1fr; }
            .visual-story, .visual-story:nth-child(even) { grid-template-columns:1fr; }
            .visual-story:nth-child(even) .visual-copy, .visual-story:nth-child(even) .visual-frame { order:initial; }
            .visual-story:nth-child(even) .visual-frame, .visual-frame { border:0; border-top:1px solid rgba(255,255,255,.08); }
            .section-head { align-items: start; flex-direction: column; }
        }
        @media (max-width: 640px) {
            .wrap { width: min(100% - 24px, 1180px); }
            .nav-inner { min-height: 60px; }
            .nav-actions .btn:first-child { display: none; }
            .hero { padding-top: 54px; }
            .hero-proof, .features, .steps, .mini-grid, .faq, .contact-form { grid-template-columns: 1fr; }
            .chart { height: 220px; }
            .trade-card { position: relative; top: auto; right: auto; width: auto; margin: 14px; }
            .journal-row { grid-template-columns: 1fr; gap: 5px; }
            .cta { padding: 24px; }
            .dashboard-showcase { padding-top:54px; }
            .dashboard-preview-card { padding:4px; border-radius:10px; }
            .dashboard-preview-window { border-radius:7px; overflow-x:auto; }
            .dashboard-preview-window img { width:900px; max-width:none; }
            .dashboard-preview-caption { align-items:flex-start; flex-direction:column; }
            .exchange-intro { align-items:flex-start; flex-direction:column; }
            .exchange-grid { grid-template-columns:1fr; }
            .exchange-card { padding:19px; }
            .broker-workflow { grid-template-columns:1fr; }
            .sync-network { grid-template-columns:minmax(0,1fr); justify-items:stretch; }
            .broker-node:after { display:none; }
            .journal-hub { width:88px; height:88px; }
            .hub-core { width:43px; height:43px; }
            .sync-visual { padding:18px; }
            .live-metrics { grid-template-columns:minmax(0,1fr); }
            .product-grid { grid-template-columns:1fr; }
            .product-card.large, .product-card.medium, .product-card.third { grid-column:auto; min-height:0; }
            .product-card { padding:22px; }
            .product-card h3 { margin-top:24px; }
            .visual-copy { padding:24px; }
            .visual-frame { padding:6px; overflow:hidden; }
            .visual-frame img { width:100%; max-width:100%; height:auto; }
            .privacy-strip { padding:24px; }
        }
        @media (prefers-reduced-motion: reduce) {
            .dashboard-showcase.reveal-enabled .showcase-copy,
            .dashboard-showcase.reveal-enabled .dashboard-preview-card,
            .dashboard-showcase.reveal-enabled .dashboard-preview-caption { opacity:1; transform:none; transition:none; }
            .dashboard-preview-card:hover { transform:none; }
            .terminal,.terminal:hover { transform:none; }
            .terminal-live:before,.sync-visual:after,.broker-node,.broker-node:after,.journal-hub:before,.journal-hub:after,.hub-orbit { animation:none; }
        }
    </style>
    @livewireStyles
</head>
<body>
    @include('partials.public-header')

    <main>
        <header class="hero">
            <div class="wrap hero-grid">
                <div>
                    <span class="eyebrow"><span class="pulse"></span> Shark + Delta, unified</span>
                    <h1>Turn your trade history into better trading decisions.</h1>
                    <p class="lead">Bring Shark Exchange and Delta Exchange India into one private trading journal. Review P&amp;L, execution, notes, and performance trends without moving between platforms.</p>
                    <div class="hero-actions">
                        <a class="btn primary" href="{{ route('register') }}">Create Journal</a>
                        <a class="btn" href="{{ route('broker.guide') }}" wire:navigate.hover>Broker Setup Guide</a>
                        <a class="btn" href="{{ route('login') }}">Open Dashboard</a>
                    </div>
                    <div class="hero-proof" aria-label="Trading journal highlights">
                        <div class="proof"><strong>2-in-1</strong><span>Shark + Delta workspace</span></div>
                        <div class="proof"><strong>360°</strong><span>trade review view</span></div>
                        <div class="proof"><strong>24/7</strong><span>journal access</span></div>
                    </div>
                </div>

                <div class="terminal" aria-label="Animated Shark and Delta broker sync preview">
                    <div class="terminal-top">
                        <div class="dots"><i></i><i></i><i></i></div>
                        <span class="terminal-live">Live journal sync</span>
                    </div>
                    <div class="sync-visual">
                        <div class="sync-network">
                            <div class="broker-node shark">
                                <div class="broker-node-head"><span class="broker-node-mark">S</span><div><strong>Shark Exchange</strong><small>INR derivatives</small></div></div>
                                <span class="broker-node-status">Trade history connected</span>
                            </div>
                            <div class="journal-hub" aria-label="TradeYatra unified data matrix">
                                <span class="hub-core"><img src="{{ asset('images/branding/tradeyatra-icon-v2.png') }}" alt=""></span>
                                <i class="hub-orbit one"></i><i class="hub-orbit two"></i><i class="hub-orbit three"></i>
                            </div>
                            <div class="broker-node delta">
                                <div class="broker-node-head"><span class="broker-node-mark">D</span><div><strong>Delta India</strong><small>Perpetual futures</small></div></div>
                                <span class="broker-node-status">Realized P&amp;L connected</span>
                            </div>
                        </div>
                        <p class="sync-caption">Two broker histories flow into one private performance journal.</p>
                        <div class="live-metrics">
                            <div class="live-metric primary"><small>Net P&amp;L</small><strong>+INR 42.8K</strong></div>
                            <div class="live-metric win"><small>Win rate</small><strong>62%</strong></div>
                            <div class="live-metric rr"><small>Avg. R:R</small><strong>1.8</strong></div>
                        </div>
                    </div>
                    <div class="sync-activity">
                        <div class="sync-activity-head"><span>Recent imports</span><span>Updated now</span></div>
                        <div class="sync-row"><span class="sync-source shark">S</span><span><strong>BTC-INR Long</strong><small>Shark Exchange · Reviewed</small></span><span class="sync-result">Synced</span></div>
                        <div class="sync-row"><span class="sync-source delta">D</span><span><strong>ETHUSD Perpetual</strong><small>Delta India · Realized activity</small></span><span class="sync-result">Synced</span></div>
                    </div>
                    <div class="terminal-security" aria-label="Connection security"><span>Read-only API</span><i>•</i><span>Encrypted credentials</span><i>•</i><span>5-minute auto sync</span></div>
                </div>
            </div>
        </header>

        <section class="dashboard-showcase" id="dashboard-preview" aria-labelledby="dashboard-preview-title">
            <div class="wrap">
                <div class="showcase-copy">
                    <span class="eyebrow"><span class="pulse"></span> Inside TradeYatra</span>
                    <h2 id="dashboard-preview-title">See your complete trading day in one focused dashboard.</h2>
                    <p>This is the workspace you will enter after login. Check exchange balances, review recent performance, sync Shark and Delta activity, and write your trading plan without moving between multiple screens.</p>
                    <div class="showcase-points" aria-label="Dashboard highlights">
                        <span class="showcase-point">Live exchange overview</span>
                        <span class="showcase-point">Daily trading plan</span>
                        <span class="showcase-point">Performance and risk review</span>
                    </div>
                </div>

                <figure style="margin:0">
                    <div class="dashboard-preview-card">
                        <div class="dashboard-preview-window">
                            <img src="{{ asset('images/dashboard-preview.png') }}" width="1918" height="876" loading="lazy" decoding="async" alt="TradeYatra logged-in dashboard showing Shark and Delta exchange summaries, journal balance, daily performance, and trading plan">
                        </div>
                    </div>
                    <figcaption class="dashboard-preview-caption">
                        <span><strong>Your journal, ready after login.</strong><br>Real features shown from the TradeYatra dashboard.</span>
                        <a class="btn primary" href="{{ route('register') }}">Create Your Free Journal</a>
                    </figcaption>
                </figure>
            </div>
        </section>

        <section class="supported-exchanges" aria-labelledby="supported-exchanges-title">
            <div class="wrap">
                <div class="exchange-intro">
                    <div>
                        <span class="eyebrow"><span class="pulse"></span> Supported exchanges</span>
                        <h2 id="supported-exchanges-title">Two supported exchanges. One trading journal.</h2>
                    </div>
                    <p>TradeYatra currently provides dedicated API connection and automatic journal-sync workflows for the two exchanges below.</p>
                </div>
                <div class="exchange-support-summary">Supported now: Shark Exchange and Delta Exchange India</div>

                <div class="exchange-grid">
                    <article class="exchange-card shark">
                        <div class="exchange-badge" aria-hidden="true">S</div>
                        <div>
                            <div class="exchange-card-top"><h3>Shark Exchange</h3><span class="supported-badge">Supported</span></div>
                            <p>Connect a dedicated API key and bring your Shark futures activity into a structured journal.</p>
                            <div class="exchange-detail"><div><small>Primary market</small><strong>Crypto derivatives</strong></div><div><small>Account context</small><strong>INR wallet activity</strong></div></div>
                            <div class="exchange-capabilities" aria-label="Shark Exchange sync data">
                                <span>Trade history</span><span>Orders</span><span>Positions</span><span>INR wallet</span>
                            </div>
                            <a class="exchange-link" href="{{ route('broker.guide') }}#shark-guide" wire:navigate.hover>View Shark setup guide <span aria-hidden="true">→</span></a>
                        </div>
                    </article>

                    <article class="exchange-card delta">
                        <div class="exchange-badge" aria-hidden="true">D</div>
                        <div>
                            <div class="exchange-card-top"><h3>Delta Exchange India</h3><span class="supported-badge">Supported</span></div>
                            <p>Connect your Delta India production account and journal realized perpetual-futures activity.</p>
                            <div class="exchange-detail"><div><small>Supported environment</small><strong>Delta India production</strong></div><div><small>Journal basis</small><strong>Realized wallet P&amp;L</strong></div></div>
                            <div class="exchange-capabilities" aria-label="Delta Exchange sync data">
                                <span>Fills</span><span>Realized P&amp;L</span><span>Positions</span><span>USD wallet</span>
                            </div>
                            <a class="exchange-link" href="{{ route('broker.guide') }}#delta-guide" wire:navigate.hover>View Delta setup guide <span aria-hidden="true">→</span></a>
                        </div>
                    </article>
                </div>
                <div class="broker-workflow" aria-label="Broker connection workflow">
                    <div class="broker-workflow-step"><b>Step 1</b><strong>Choose your exchange</strong><span>Connect Shark, Delta India, or both.</span></div>
                    <div class="broker-workflow-step"><b>Step 2</b><strong>Add a secure API key</strong><span>Use the guided connection process and minimum permissions.</span></div>
                    <div class="broker-workflow-step"><b>Step 3</b><strong>Sync and review</strong><span>Bring supported activity into one journal and analyze each exchange separately or together.</span></div>
                </div>
                <p class="exchange-footnote">TradeYatra is a journaling and analytics platform—not a broker, exchange, or trade-execution service.</p>
            </div>
        </section>

        <section id="features">
            <div class="wrap">
                <div class="section-head">
                    <h2>A focused journal for every stage of your trading review.</h2>
                    <p>Import exchange activity, capture context, and review the results through a workflow designed for practical improvement.</p>
                </div>
                <div class="features">
                    <article class="feature">
                        <div class="icon">01</div>
                        <h3>Import and log trades</h3>
                        <p>Capture symbol, side, screenshots, setup notes, mistakes, emotions, and execution quality without clutter.</p>
                    </article>
                    <article class="feature">
                        <div class="icon">02</div>
                        <h3>Two exchange connections</h3>
                        <p>Sync Shark and Delta trade history into one clean journal while keeping each source easy to filter.</p>
                    </article>
                    <article class="feature">
                        <div class="icon">03</div>
                        <h3>Screenshot review</h3>
                        <p>Attach before and after charts so every trade has context, not just numbers on a table.</p>
                    </article>
                    <article class="feature">
                        <div class="icon">04</div>
                        <h3>Weekly and monthly reports</h3>
                        <p>Compare net P&amp;L, win rate, average trade, best trades, and losing periods across weeks and months.</p>
                    </article>
                    <article class="feature">
                        <div class="icon">05</div>
                        <h3>Discipline tracking</h3>
                        <p>Separate good losses from bad decisions with process tags and post-trade notes.</p>
                    </article>
                    <article class="feature">
                        <div class="icon">06</div>
                        <h3>Built for Indian traders</h3>
                        <p>INR-friendly presentation, crypto derivatives context, and a broker-inspired visual identity.</p>
                    </article>
                </div>
            </div>
        </section>

        <section class="product-tour" id="inside-app" aria-labelledby="inside-app-title">
            <div class="wrap">
                <div class="section-head">
                    <div>
                        <span class="eyebrow"><span class="pulse"></span> After you log in</span>
                        <h2 id="inside-app-title" style="margin-top:16px">One workspace for the full review cycle.</h2>
                    </div>
                    <p>TradeYatra connects the daily actions of a trader—from planning and syncing to review, research, and improvement.</p>
                </div>

                <div class="product-grid">
                    <article class="product-card large">
                        <span class="product-number">01 · Command center</span>
                        <h3>Start each session with the numbers and plan that matter.</h3>
                        <p>Your dashboard brings Shark and Delta balances, recent performance, sync status, and the daily trading plan into one view.</p>
                        <div class="product-tags"><span>Exchange balances</span><span>Daily P&amp;L</span><span>Trading plan</span><span>Recent activity</span></div>
                        <span class="product-glow"></span>
                    </article>
                    <article class="product-card medium">
                        <span class="product-number">02 · Journal</span>
                        <h3>Give every trade useful context.</h3>
                        <p>Record the setup, direction, execution, screenshots, notes, mistakes, and emotions behind the result.</p>
                        <div class="product-tags"><span>Manual entries</span><span>Screenshots</span><span>Notes</span><span>Process tags</span></div>
                        <span class="product-glow"></span>
                    </article>
                    <article class="product-card third">
                        <span class="product-number">03 · Analysis</span>
                        <h3>See what is actually driving performance.</h3>
                        <p>Break results down by exchange, symbol, direction, date, and outcome instead of relying on memory.</p>
                        <div class="product-tags"><span>Win rate</span><span>Net P&amp;L</span><span>Filters</span></div>
                        <span class="product-glow"></span>
                    </article>
                    <article class="product-card third">
                        <span class="product-number">04 · Calendar</span>
                        <h3>Turn daily outcomes into a visible pattern.</h3>
                        <p>Scan profitable and losing days, attach daily notes, and understand how consistency changes over time.</p>
                        <div class="product-tags"><span>P&amp;L calendar</span><span>Daily notes</span><span>Monthly view</span></div>
                        <span class="product-glow"></span>
                    </article>
                    <article class="product-card third">
                        <span class="product-number">05 · Intelligence</span>
                        <h3>Turn journal data into questions you can act on.</h3>
                        <p>Use market news and crypto context for research, then ask Yatra AI to review performance, strategies, mistakes, and discipline—not to generate trade signals.</p>
                        <div class="product-tags"><span>Monthly summary</span><span>Strategy review</span><span>Shark vs Delta</span><span>Plan review</span></div>
                        <span class="product-glow"></span>
                    </article>
                </div>

                <div class="visual-gallery" aria-label="TradeYatra product previews">
                    <article class="visual-story">
                        <div class="visual-copy">
                            <span class="product-number">Calendar review</span>
                            <h3>Read the month at a glance.</h3>
                            <p>The P&amp;L calendar makes streaks, drawdowns, and inactive days visible before you begin a deeper review.</p>
                            <div class="visual-list"><span>Daily profit, loss, and trade count</span><span>Monthly totals and winning-day summary</span><span>Fast navigation between review periods</span></div>
                        </div>
                        <figure class="visual-frame" style="margin:0">
                            <img src="{{ asset('images/product/calendar-preview.png') }}" width="1893" height="874" loading="lazy" decoding="async" alt="TradeYatra calendar showing monthly performance, trading days, win rate, and daily trade results">
                        </figure>
                    </article>

                    <article class="visual-story">
                        <div class="visual-copy">
                            <span class="product-number">Dashboard heatmap</span>
                            <h3>Compare the week and month in one view.</h3>
                            <p>Switch between Shark and Delta reports, review headline metrics, and scan the monthly heatmap for green, red, and inactive days.</p>
                            <div class="visual-list"><span>Weekly and monthly net P&amp;L</span><span>Trades, win rate, and fees together</span><span>Exchange-specific performance switching</span></div>
                        </div>
                        <figure class="visual-frame" style="margin:0">
                            <img src="{{ asset('images/product/heatmap-preview.png') }}" width="1897" height="871" loading="lazy" decoding="async" alt="TradeYatra dashboard showing Shark Exchange weekly metrics and a monthly profit and loss heatmap">
                        </figure>
                    </article>

                    <article class="visual-story">
                        <div class="visual-copy">
                            <span class="product-number">TradeYatra Insights</span>
                            <h3>A journal coach that starts with your own data.</h3>
                            <p>Ask focused questions in plain language and receive private, database-driven calculations from the trades and notes already in your journal.</p>
                            <div class="visual-list">
                                <span>Summarise monthly performance and discipline</span>
                                <span>Compare Shark and Delta trading results</span>
                                <span>Identify stronger strategies and recurring losing mistakes</span>
                                <span>Review today’s trading plan before the session</span>
                                <span>Journal analysis only—never financial advice or market predictions</span>
                            </div>
                        </div>
                        <figure class="visual-frame" style="margin:0">
                            <img src="{{ asset('images/product/ai-coach-preview.png') }}" width="1897" height="880" loading="lazy" decoding="async" alt="TradeYatra Insights journal coach with prompts for performance, exchange comparison, strategy, mistakes, and daily plan review">
                        </figure>
                    </article>
                </div>
            </div>
        </section>

        <section aria-labelledby="privacy-title">
            <div class="wrap privacy-strip">
                <div>
                    <span class="eyebrow"><span class="pulse"></span> Designed around your data</span>
                    <h2 id="privacy-title">Your journal is private by default.</h2>
                    <p>TradeYatra is built as a personal record-keeping and review tool. Broker credentials are used only for the connections you configure, and the product never places trades on your behalf.</p>
                </div>
                <div class="privacy-points">
                    <div class="privacy-point"><b>✓</b><span>API credentials are stored encrypted by the application.</span></div>
                    <div class="privacy-point"><b>✓</b><span>Each account can access only its own journal records.</span></div>
                    <div class="privacy-point"><b>✓</b><span>Read and sync workflows—not an execution or advisory service.</span></div>
                </div>
            </div>
        </section>

        <section id="reports">
            <div class="wrap analytics">
                <div class="mini-grid">
                    <div class="mini"><small>Win Rate</small><strong>62%</strong></div>
                    <div class="mini"><small>Avg Profit</small><strong>INR 7.8K</strong></div>
                    <div class="mini"><small>Best Setup</small><strong>Breakout</strong></div>
                    <div class="mini"><small>Risk Rule</small><strong>92%</strong></div>
                </div>
                <div class="review-panel">
                    <span class="eyebrow"><span class="pulse"></span> Reports that teach</span>
                    <h3>Find the periods worth repeating and the habits worth changing.</h3>
                    <p class="lead">Weekly reports, monthly comparisons, and the P&amp;L calendar turn exchange history into a practical feedback loop for your next session.</p>
                    <div class="checklist">
                        <div class="check"><b>✓</b><span>Filter by market, date, symbol, direction, and outcome.</span></div>
                        <div class="check"><b>✓</b><span>Review notes and screenshots beside P&amp;L metrics.</span></div>
                        <div class="check"><b>✓</b><span>Compare Shark, Delta, and manually logged trades.</span></div>
                    </div>
                </div>
            </div>
        </section>

        <section id="workflow">
            <div class="wrap">
                <div class="section-head">
                    <h2>Review in four simple steps.</h2>
                    <p>A clean workflow that keeps journaling close to the actual trading process.</p>
                </div>
                <div class="steps">
                    <article class="step"><h3>Create account</h3><p>Set up your secure journal profile and start with a focused dashboard.</p></article>
                    <article class="step"><h3>Add or sync</h3><p>Log trades manually or use the Shark sync area to bring your trade history in.</p></article>
                    <article class="step"><h3>Attach context</h3><p>Add chart screenshots, setup labels, rules followed, and trade notes.</p></article>
                    <article class="step"><h3>Improve weekly</h3><p>Use performance reports to spot stronger periods, losses, and discipline trends.</p></article>
                </div>
            </div>
        </section>

        <section>
            <div class="wrap cta">
                <div>
                    <h2>Build a trading record you can trust.</h2>
                    <p>Start your journal, keep your review cycle simple, and make every session easier to learn from.</p>
                </div>
                <a class="btn primary" href="{{ route('register') }}">Start Free</a>
            </div>
        </section>

        <section id="faq">
            <div class="wrap">
                <div class="section-head">
                    <h2>Questions traders ask.</h2>
                    <p>Simple answers before you start tracking your next trade.</p>
                </div>
                <div class="faq">
                    <article class="faq-item"><h3>Which exchanges can I connect?</h3><p>TradeYatra currently provides dedicated connections and sync workflows for Shark Exchange and Delta Exchange.</p></article>
                    <article class="faq-item"><h3>Can I use screenshots?</h3><p>Yes. Trade records can include screenshots so the journal captures both numbers and chart context.</p></article>
                    <article class="faq-item"><h3>Does it replace my broker?</h3><p>No. It is a review and analytics layer for improving your trading process.</p></article>
                    <article class="faq-item"><h3>Can I access my dashboard now?</h3><p>Yes. Login or register from this page and you will enter the existing trading journal app.</p></article>
                </div>
            </div>
        </section>

        <section id="contact">
            <div class="wrap contact-grid">
                <div class="contact-copy">
                    <span class="eyebrow"><span class="pulse"></span> Contact us</span>
                    <h2>How can we help?</h2>
                    <p>Questions about TradeYatra, connecting an exchange, or your account? Send us a message and include enough detail for us to point you in the right direction.</p>
                    <a class="contact-email" href="mailto:slwithrohit@gmail.com" aria-label="Email TradeYatra support at slwithrohit@gmail.com">
                        <span aria-hidden="true">✉</span>
                        <span>slwithrohit@gmail.com</span>
                    </a>
                    <div class="contact-note">For your security, never include passwords, API secrets, recovery codes, or private keys in this form.</div>
                </div>

                <form class="contact-form" action="{{ route('contact.store') }}" method="POST" id="contact-form">
                    @csrf
                    <div class="form-field">
                        <label for="contact-name">Name</label>
                        <input id="contact-name" name="name" type="text" value="{{ old('name') }}" maxlength="100" autocomplete="name" required aria-describedby="name-error">
                        @error('name') <span class="form-error" id="name-error">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-field">
                        <label for="contact-email">Email</label>
                        <input id="contact-email" name="email" type="email" value="{{ old('email') }}" maxlength="254" autocomplete="email" required aria-describedby="email-error">
                        @error('email') <span class="form-error" id="email-error">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-field full">
                        <label for="contact-subject">What can we help with?</label>
                        <select id="contact-subject" name="subject" required aria-describedby="subject-error">
                            <option value="">Choose a topic</option>
                            @foreach (['product' => 'Product question', 'broker' => 'Broker connection', 'account' => 'Account help', 'feedback' => 'Feedback', 'other' => 'Something else'] as $value => $label)
                                <option value="{{ $value }}" @selected(old('subject') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('subject') <span class="form-error" id="subject-error">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-field full">
                        <label for="contact-message">Message</label>
                        <textarea id="contact-message" name="message" minlength="10" maxlength="3000" placeholder="Tell us what you need help with..." required aria-describedby="message-error">{{ old('message') }}</textarea>
                        @error('message') <span class="form-error" id="message-error">{{ $message }}</span> @enderror
                    </div>
                    <div class="honeypot" aria-hidden="true">
                        <label for="contact-website">Website</label>
                        <input id="contact-website" name="website" type="text" tabindex="-1" autocomplete="off">
                    </div>
                    <div class="form-submit">
                        <small>We’ll use your email only to respond to this request.</small>
                        <button class="btn primary" type="submit" data-idle-label="Send Message">Send Message</button>
                    </div>
                </form>
            </div>
        </section>
    </main>

    @include('partials.public-footer')
    <div class="toast-viewport" id="toast-viewport" aria-live="polite" aria-atomic="true">
        @if (session('contact_success'))
            <div class="toast success" role="status">
                <strong class="toast-title">Message sent</strong>
                <div class="toast-message">{{ session('contact_success') }}</div>
                <button class="toast-close" type="button" aria-label="Dismiss message">x</button>
            </div>
        @elseif ($errors->any())
            <div class="toast error" role="alert">
                <strong class="toast-title">Please check</strong>
                <div class="toast-message">Please check the highlighted fields and try again.</div>
                <button class="toast-close" type="button" aria-label="Dismiss message">x</button>
            </div>
        @endif
    </div>
    <script type="application/ld+json">
    {!! json_encode([
        '@context' => 'https://schema.org',
        '@graph' => [
            [
                '@type' => 'Organization',
                '@id' => route('home').'#organization',
                'name' => 'TradeYatra',
                'url' => route('home'),
                'logo' => asset('images/branding/tradeyatra-icon-v2.png'),
                'email' => 'slwithrohit@gmail.com',
            ],
            [
                '@type' => 'WebSite',
                '@id' => route('home').'#website',
                'name' => 'TradeYatra',
                'url' => route('home'),
                'publisher' => ['@id' => route('home').'#organization'],
                'inLanguage' => 'en-IN',
            ],
            [
                '@type' => 'SoftwareApplication',
                'name' => 'TradeYatra',
                'url' => route('home'),
                'applicationCategory' => 'FinanceApplication',
                'operatingSystem' => 'Web',
                'description' => 'A private trading journal for reviewing Shark Exchange and Delta Exchange trades, weekly and monthly performance, P&L calendars, and trade notes.',
                'offers' => ['@type' => 'Offer', 'price' => '0', 'priceCurrency' => 'INR'],
                'publisher' => ['@id' => route('home').'#organization'],
            ],
            [
                '@type' => 'FAQPage',
                'mainEntity' => [
                    ['@type' => 'Question', 'name' => 'Which exchanges can I connect?', 'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'TradeYatra currently provides dedicated connections and sync workflows for Shark Exchange and Delta Exchange.']],
                    ['@type' => 'Question', 'name' => 'Can I add screenshots to trades?', 'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Yes. Trade records can include screenshots so the journal captures both performance numbers and chart context.']],
                    ['@type' => 'Question', 'name' => 'Does TradeYatra replace my exchange?', 'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'No. TradeYatra is a journaling and performance-review layer designed to complement an exchange account.']],
                ],
            ],
        ],
    ], JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}
    </script>
    <script>
    (() => {
        const showcase = document.querySelector('.dashboard-showcase');
        if (!showcase || !('IntersectionObserver' in window)) return;
        showcase.classList.add('reveal-enabled');
        const observer = new IntersectionObserver((entries) => {
            if (!entries[0].isIntersecting) return;
            showcase.classList.add('is-visible');
            observer.disconnect();
        }, { threshold: .16 });
        window.tradeYatraPublicNavigationSignal?.addEventListener('abort', () => observer.disconnect(), { once: true });
        observer.observe(showcase);
    })();

    (() => {
        const supportedSections = new Set(['features', 'reports', 'workflow', 'faq', 'contact']);
        const scrollToHomepageSection = () => {
            const id = decodeURIComponent(window.location.hash.slice(1));
            if (!supportedSections.has(id)) return;
            const section = document.getElementById(id);
            if (!section) return;
            window.requestAnimationFrame(() => window.requestAnimationFrame(() => {
                section.scrollIntoView({ behavior: 'auto', block: 'start' });
            }));
        };

        if (document.readyState === 'complete') scrollToHomepageSection();
        else window.addEventListener('load', scrollToHomepageSection, { once: true, signal: window.tradeYatraPublicNavigationSignal });
        window.addEventListener('hashchange', scrollToHomepageSection, { signal: window.tradeYatraPublicNavigationSignal });
    })();

    (() => {
        const form = document.getElementById('contact-form');
        if (!form || !window.fetch || !window.FormData) return;

        const submit = form.querySelector('button[type="submit"]');
        const fieldNames = ['name', 'email', 'subject', 'message'];

        function showToast(message, type, title) {
            const viewport = document.getElementById('toast-viewport');
            const toast = document.createElement('div');
            toast.className = `toast ${type}`;
            toast.setAttribute('role', type === 'error' ? 'alert' : 'status');

            const heading = document.createElement('strong');
            heading.className = 'toast-title';
            heading.textContent = title;
            const copy = document.createElement('div');
            copy.className = 'toast-message';
            copy.textContent = message;
            const close = document.createElement('button');
            close.className = 'toast-close';
            close.type = 'button';
            close.setAttribute('aria-label', 'Dismiss message');
            close.textContent = 'x';
            close.addEventListener('click', () => toast.remove());

            toast.append(heading, copy, close);
            viewport.appendChild(toast);
            setTimeout(() => toast.remove(), 6000);
        }

        function clearErrors() {
            form.querySelectorAll('[data-async-error]').forEach((error) => error.remove());
            fieldNames.forEach((name) => form.elements[name]?.removeAttribute('aria-invalid'));
        }

        form.addEventListener('submit', async (event) => {
            if (!form.reportValidity()) return;
            event.preventDefault();
            clearErrors();
            submit.disabled = true;
            submit.textContent = 'Sending…';

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    body: new FormData(form),
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                });
                const payload = await response.json().catch(() => ({}));

                if (!response.ok) {
                    if (response.status === 422 && payload.errors) {
                        Object.entries(payload.errors).forEach(([name, messages]) => {
                            const field = form.elements[name];
                            if (!field) return;
                            field.setAttribute('aria-invalid', 'true');
                            const error = document.createElement('span');
                            error.className = 'form-error';
                            error.dataset.asyncError = '';
                            error.textContent = messages[0];
                            field.closest('.form-field')?.append(error);
                        });
                        showToast('Please check the highlighted fields and try again.', 'error', 'Please check');
                        return;
                    }
                    throw new Error(response.status === 429 ? 'Too many messages. Please wait a minute and try again.' : 'We could not send your message. Please try again.');
                }

                form.reset();
                showToast(payload.message || 'Thanks. Your message has been received.', 'success', 'Message sent');
            } catch (error) {
                showToast(error.message || 'We could not send your message. Please try again.', 'error', 'Send failed');
            } finally {
                submit.disabled = false;
                submit.textContent = submit.dataset.idleLabel;
            }
        });

        document.querySelectorAll('.toast-close').forEach((button) => {
            button.addEventListener('click', () => button.closest('.toast')?.remove());
        });
        setTimeout(() => document.querySelectorAll('.toast').forEach((toast) => toast.remove()), 6000);
    })();
    </script>
</body>
</html>
