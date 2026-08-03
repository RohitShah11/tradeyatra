<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex,nofollow,noarchive">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'TradeYatra' }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/branding/tradeyatra-icon-v2.png') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=manrope:400,500,600,700,800" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.documentElement.dataset.theme = localStorage.getItem('journal-theme') || 'dark';
    </script>
    @livewireStyles
    <style>
        :root {
            --bg: #050b13;
            --bg-2: #071a24;
            --panel: #0b1722;
            --panel-2: #101f2d;
            --ink: #eef8ff;
            --muted: #8fa8b8;
            --line: rgba(120, 214, 255, .18);
            --accent: #18c7ff;
            --accent-2: #20e6a4;
            --good: #20e6a4;
            --bad: #ff6171;
            --warn: #ff9c36;
            --livewire-progress-bar-color: #ff9c36;
            --soft: rgba(24, 199, 255, .12);
            --body-bg:
                radial-gradient(circle at 18% 6%, rgba(24, 199, 255, .16), transparent 28rem),
                radial-gradient(circle at 85% 2%, rgba(32, 230, 164, .1), transparent 24rem),
                linear-gradient(180deg, #041019 0%, var(--bg) 52%, #071018 100%);
            --sidebar-bg: linear-gradient(180deg, rgba(7, 26, 36, .98), rgba(4, 12, 21, .98));
            --panel-bg: linear-gradient(180deg, rgba(16, 31, 45, .88), rgba(8, 18, 30, .88));
            --field-bg: rgba(255,255,255,.06);
            --nav-text: #b5c9d7;
        }
        html[data-theme="light"] {
            --bg: #edf7fb;
            --bg-2: #ffffff;
            --panel: #ffffff;
            --panel-2: #f6fbfd;
            --ink: #06141d;
            --muted: #587181;
            --line: rgba(8, 79, 112, .16);
            --accent: #008fd3;
            --accent-2: #00a87e;
            --good: #008f68;
            --bad: #d8324f;
            --warn: #c97913;
            --soft: rgba(0, 143, 211, .1);
            --body-bg:
                radial-gradient(circle at 18% 6%, rgba(0, 143, 211, .12), transparent 28rem),
                radial-gradient(circle at 85% 2%, rgba(0, 168, 126, .1), transparent 24rem),
                linear-gradient(180deg, #f8fdff 0%, #edf7fb 100%);
            --sidebar-bg: linear-gradient(180deg, rgba(255,255,255,.96), rgba(236,247,252,.96));
            --panel-bg: linear-gradient(180deg, rgba(255,255,255,.94), rgba(244,251,253,.94));
            --field-bg: rgba(255,255,255,.86);
            --nav-text: #587181;
        }
        * { box-sizing: border-box; }
        html, body { max-width:100%; overflow-x:clip; }
        body {
            margin: 0;
            background: var(--body-bg);
            color: var(--ink);
            font: 14px/1.5 "Manrope", ui-sans-serif, system-ui, sans-serif;
            position: relative;
        }
        body:before {
            content: "";
            position: fixed;
            inset: 0;
            pointer-events: none;
            z-index: 0;
            opacity: .7;
            background-image:
                radial-gradient(circle at 7% 16%, rgba(103,232,249,.9) 0 1px, transparent 2px),
                radial-gradient(circle at 27% 8%, rgba(255,255,255,.65) 0 1px, transparent 2px),
                radial-gradient(circle at 45% 31%, rgba(103,232,249,.7) 0 1px, transparent 2px),
                radial-gradient(circle at 71% 13%, rgba(255,173,104,.8) 0 1px, transparent 2px),
                radial-gradient(circle at 91% 27%, rgba(103,232,249,.7) 0 1px, transparent 2px),
                radial-gradient(circle at 79% 77%, rgba(255,255,255,.45) 0 1px, transparent 2px);
        }
        body:after {
            content:"";
            position:fixed;
            width:520px;
            height:520px;
            left:-280px;
            top:-250px;
            border:1px solid rgba(255,122,26,.13);
            border-radius:50%;
            box-shadow:0 0 0 54px rgba(255,122,26,.035),0 0 0 108px rgba(25,199,181,.025);
            pointer-events:none;
            z-index:-1;
        }
        a { color: inherit; text-decoration: none; }
        .shell { display: grid; grid-template-columns: 276px 1fr; min-height: 100vh; }
        .menu-toggle, .sidebar-backdrop { display: none; }
        .sidebar {
            background: var(--sidebar-bg);
            color: var(--ink);
            padding: 24px 16px;
            position: sticky;
            top: 0;
            height: 100vh;
            height: 100dvh;
            overflow-y: hidden;
            overflow-x: hidden;
            overscroll-behavior: contain;
            scrollbar-width: thin;
            scrollbar-color: color-mix(in srgb, var(--accent) 58%, transparent) transparent;
            border-right: 1px solid var(--line);
            box-shadow: 18px 0 60px rgba(0,0,0,.22);
            display: flex;
            flex-direction: column;
        }
        .sidebar::-webkit-scrollbar { width: 6px; }
        .sidebar::-webkit-scrollbar-track { background: transparent; }
        .sidebar::-webkit-scrollbar-thumb {
            background: linear-gradient(180deg, var(--accent), var(--accent-2));
            border-radius: 999px;
        }
        .sidebar::-webkit-scrollbar-thumb:hover { background: var(--accent); }
        .sidebar-header { flex:0 0 auto; padding:0 4px 18px; border-bottom:1px solid var(--line); }
        .brand-row { display:block; margin:0 0 16px; }
        .brand { min-width:0; font-size:19px; font-weight:900; display:flex; align-items:center; gap:0; letter-spacing:-.03em; }
        .brand:before {
            content: "";
            width: 42px;
            height: 42px;
            border-radius: 0;
            display: grid;
            place-items: center;
            background: transparent url("{{ asset('images/branding/tradeyatra-icon-v2.png') }}") center/contain no-repeat;
            box-shadow: none;
            margin-right: -4px;
        }
        .brand small { display:block; color:var(--muted); font-size:9px; letter-spacing:.16em; text-transform:uppercase; font-weight:700; margin-top:1px; }
        .brand-support-link { width:100%; min-height:34px; display:inline-flex; align-items:center; justify-content:center; margin-top:10px; padding:8px 12px; border:1px solid transparent; border-radius:9px; color:var(--ink); background:linear-gradient(color-mix(in srgb,var(--panel) 96%,transparent),color-mix(in srgb,var(--panel) 96%,transparent)) padding-box,linear-gradient(90deg,#ff7a1a,#ffb13b,#18c7ff,#00dce8,#ff7a1a) border-box; background-size:100% 100%,300% 100%; font-size:12px; line-height:1; font-weight:700; letter-spacing:0; animation:sync-border-flow 3s linear infinite; box-shadow:0 7px 22px rgba(24,199,255,.08); }
        .brand-support-link:hover { color:var(--ink); box-shadow:0 9px 26px rgba(255,122,26,.13),0 7px 22px rgba(24,199,255,.1); }
        @media(prefers-reduced-motion:reduce){.brand-support-link{animation:none}}
        .nav { display:flex; flex-direction:column; gap:12px; overflow-y:auto; overflow-x:hidden; padding:16px 3px 20px 0; scrollbar-width:thin; scrollbar-color:rgba(255,122,26,.55) transparent; }
        .nav::-webkit-scrollbar { width:5px; }
        .nav::-webkit-scrollbar-thumb { border-radius:999px; background:linear-gradient(var(--accent),var(--accent-2)); }
        .nav-group { display:grid; gap:3px; padding:7px; border:1px solid transparent; border-radius:14px; }
        .nav-group.exchange-group { background:linear-gradient(145deg,rgba(255,122,26,.055),rgba(25,199,181,.025)); border-color:rgba(255,122,26,.1); }
        .nav-label { color:var(--muted); opacity:.72; padding:2px 8px 7px; font-size:9px; font-weight:800; letter-spacing:.16em; text-transform:uppercase; display:flex; align-items:center; gap:8px; }
        .nav-label:after { content:""; height:1px; flex:1; background:var(--line); }
        .nav a { position:relative; color:var(--nav-text); padding:9px 10px; border-radius:10px; display:flex; align-items:center; gap:10px; border:1px solid transparent; font-weight:700; transition:background .18s ease,border-color .18s ease,color .18s ease,transform .18s ease; }
        .nav a:hover { transform:translateX(2px); }
        .nav a .nav-arrow { margin-left:auto; opacity:0; transform:translateX(-4px); transition:.18s ease; font-size:13px; }
        .nav-count { min-width:20px; height:20px; display:inline-grid; place-items:center; margin-left:auto; padding:0 6px; border-radius:999px; color:#fff; background:var(--bad); font-size:9px; font-weight:900; }
        .nav a.active .nav-arrow,.nav a:hover .nav-arrow { opacity:.75; transform:translateX(0); }
        .nav a.active, .nav a:hover {
            color: var(--ink);
            background: rgba(24, 199, 255, .1);
            border-color: rgba(24, 199, 255, .22);
        }
        .topnav {
            position: sticky;
            top: 0;
            z-index: 40;
            display: grid;
            grid-template-columns: 1fr auto 1fr;
            align-items: center;
            gap: 22px;
            margin: -24px -24px 24px;
            padding: 12px 24px;
            min-height: 70px;
            border: 1px solid var(--line);
            border-top: 0;
            border-left: 0;
            border-right: 0;
            border-radius: 0;
            background:color-mix(in srgb,var(--panel) 82%,transparent);
            backdrop-filter: blur(22px) saturate(140%);
            box-shadow:0 12px 35px rgba(2,8,11,.12);
        }
        .topnav-actions { grid-column:2; display:flex; align-items:center; justify-content:center; gap:9px; transform:translateX(-138px); }
        .topnav-news { grid-column:1; display:flex; align-items:center; justify-content:flex-start; }
        .future-nav-button { display:inline-flex; align-items:center; justify-content:center; gap:8px; min-height:42px; padding:9px 15px; border:1px solid var(--line); border-radius:11px; color:var(--ink); background:rgba(255,255,255,.035); font:inherit; font-size:13px; font-weight:850; cursor:default; }
        .future-nav-button.ai { min-height:auto; padding:6px; border:0; background:transparent; box-shadow:none; }
        .future-nav-button.ai { cursor:pointer; }
        .ai-label { color:transparent; background:linear-gradient(90deg,#ff8a1f 8%,#ffad38 43%,#18c7ff 68%,#00dce8 100%); background-clip:text; -webkit-background-clip:text; font-size:16px; font-weight:900; }
        .future-nav-button.news { border-color:rgba(255,122,26,.25); background:linear-gradient(135deg,rgba(255,122,26,.12),rgba(24,199,255,.06)); }
        .future-nav-button .icon { color:var(--accent-2); }
        .header-context { display:flex; align-items:center; gap:11px; min-width:180px; }
        .header-mark { width:38px; height:38px; border-radius:11px; display:grid; place-items:center; background:linear-gradient(135deg,var(--accent),var(--accent-2)); color:#fff; font-weight:900; box-shadow:0 8px 24px rgba(255,122,26,.18); }
        .header-context small { display:block; color:var(--muted); font-size:9px; text-transform:uppercase; letter-spacing:.14em; font-weight:800; }
        .header-context strong { display:block; color:var(--ink); font-size:14px; line-height:1.25; }
        .header-links { display:flex; justify-content:center; align-items:center; gap:4px; }
        .header-links a { color:var(--muted); font-weight:750; font-size:13px; padding:9px 12px; border-radius:9px; transition:.18s ease; }
        .header-links a:hover { color:var(--ink); background:var(--soft); }
        .header-links a.active { color:#fff; background:linear-gradient(135deg,rgba(255,122,26,.88),rgba(22,139,216,.88)); box-shadow:0 7px 18px rgba(22,139,216,.13); }
        .topnav-user { grid-column:3; display:flex; align-items:center; gap:8px; justify-content:flex-end; }
        .account-chip { display:flex; align-items:center; gap:9px; padding:5px 9px 5px 5px; border:1px solid var(--line); border-radius:12px; background:rgba(255,255,255,.035); transition:.18s ease; }
        .account-chip:hover { background:var(--soft); border-color:color-mix(in srgb,var(--accent) 32%,transparent); }
        .user-avatar { width:32px; height:32px; border-radius:9px; display:grid; place-items:center; color:#fff; background:linear-gradient(135deg,#ff7a1a,#168bd8); font-size:12px; font-weight:900; }
        .account-copy { line-height:1.15; }
        .account-copy strong { display:block; font-size:12px; color:var(--ink); max-width:110px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        .account-copy small { color:var(--muted); font-size:9px; text-transform:uppercase; letter-spacing:.08em; }
        .icon {
            width: 18px;
            height: 18px;
            flex: 0 0 18px;
            stroke: currentColor;
            stroke-width: 2;
            fill: none;
            stroke-linecap: round;
            stroke-linejoin: round;
        }
        .nav-icon {
            width: 30px;
            height: 30px;
            border-radius: 9px;
            display: inline-grid;
            place-items: center;
            color: var(--accent);
            background: var(--soft);
        }
        .theme-toggle {
            width: 40px;
            min-width: 40px;
            height: 40px;
            padding: 0;
            border-radius: 10px;
        }
        .theme-toggle .sun-icon,
        .theme-toggle .moon-icon { display: none; }
        html[data-theme="light"] .theme-toggle .sun-icon { display: block; }
        html[data-theme="dark"] .theme-toggle .moon-icon,
        html:not([data-theme]) .theme-toggle .moon-icon { display: block; }
        .logout-button {
            margin: 0;
            border: 1px solid var(--line);
            border-radius: 10px;
            padding: 9px 11px;
            background: rgba(255,255,255,.07);
            color: var(--ink);
            font-weight: 800;
            cursor: pointer;
            text-align: center;
            min-height: 40px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        .logout-button:hover { background: rgba(24,199,255,.1); border-color: rgba(24,199,255,.28); }
        .main { padding: 24px; max-width: 1480px; width: 100%; min-width:0; }
        .topbar { display: flex; justify-content: space-between; gap: 18px; align-items: center; margin-bottom: 20px; }
        .sync-menu { position:relative; }
        .sync-menu summary { list-style:none; cursor:pointer; }
        .sync-menu summary.btn.secondary {
            border-color:transparent;
            background:
                linear-gradient(color-mix(in srgb,var(--panel) 96%,transparent),color-mix(in srgb,var(--panel) 96%,transparent)) padding-box,
                linear-gradient(90deg,#ff7a1a,#ffb13b,#18c7ff,#00dce8,#ff7a1a) border-box;
            background-size:100% 100%,300% 100%;
            animation:sync-border-flow 3s linear infinite;
            box-shadow:0 7px 22px rgba(24,199,255,.08);
        }
        .sync-menu summary.btn.secondary:hover { box-shadow:0 9px 26px rgba(255,122,26,.13),0 7px 22px rgba(24,199,255,.1); }
        .sync-menu summary::-webkit-details-marker { display:none; }
        .sync-menu summary .sync-chevron { width:14px; height:14px; transition:transform .18s ease; }
        .sync-menu[open] summary .sync-chevron { transform:rotate(180deg); }
        .sync-menu-popover { position:absolute; top:calc(100% + 8px); right:0; z-index:35; width:250px; padding:7px; border:1px solid var(--line); border-radius:13px; background:color-mix(in srgb,var(--panel) 96%,#07131d); box-shadow:0 18px 50px rgba(0,0,0,.3); }
        .sync-menu-title { padding:7px 9px 9px; color:var(--muted); font-size:10px; font-weight:850; letter-spacing:.1em; text-transform:uppercase; }
        .sync-choice { display:flex; align-items:center; gap:10px; padding:11px; border-radius:9px; color:var(--ink); transition:.16s ease; }
        .sync-choice:hover { background:var(--soft); }
        .sync-choice-icon { width:34px; height:34px; display:grid; place-items:center; flex:0 0 auto; border-radius:9px; }
        .sync-choice.shark .sync-choice-icon { color:#18c7ff; background:rgba(24,199,255,.12); }
        .sync-choice.delta .sync-choice-icon { color:#ff8a1f; background:rgba(255,138,31,.12); }
        .sync-choice strong,.sync-choice small { display:block; }
        .sync-choice small { margin-top:2px; color:var(--muted); font-size:10px; }
        @keyframes sync-border-flow { to { background-position:0 0,300% 0; } }
        @media (prefers-reduced-motion:reduce) { .sync-menu summary.btn.secondary { animation:none; } }
        h1 { margin: 0; font-size: 26px; letter-spacing: 0; }
        h2 { margin: 0 0 14px; font-size: 18px; }
        h3 { margin: 0 0 8px; font-size: 15px; }
        .muted { color: var(--muted); }
        .grid { display: grid; gap: 16px; }
        .cols-2 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .cols-3 { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        .cols-4 { grid-template-columns: repeat(4, minmax(0, 1fr)); }
        .panel, .card {
            background: var(--panel-bg);
            border: 1px solid var(--line);
            border-radius: 14px;
            padding: 18px;
            box-shadow: inset 0 1px 0 rgba(255,255,255,.05);
        }
        .card .label { color: var(--muted); font-size: 11px; text-transform: uppercase; letter-spacing: .09em; font-weight:800; }
        .metric { font-size: 27px; font-weight: 800; margin-top: 5px; letter-spacing:-.04em; }
        .positive { color: var(--good); }
        .negative { color: var(--bad); }
        .toolbar { display: flex; gap: 10px; flex-wrap: wrap; align-items: end; }
        .btn {
            position: relative;
            isolation: isolate;
            overflow: hidden;
            border: 1px solid rgba(255,255,255,.16);
            border-radius: 12px;
            padding: 10px 16px;
            font-weight: 900;
            background: linear-gradient(115deg, #ff4c00 0%, #bf4911 40%, #12a5ba 72%, #11a5bd 100%);
            color: #fff;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            min-height: 40px;
            box-shadow: 0 8px 20px rgba(184,63,8,.2), 0 10px 28px rgba(5,105,121,.18), inset 0 1px 0 rgba(255,255,255,.2);
            text-shadow: 0 1px 1px rgba(0,0,0,.16);
            transition: transform .18s ease, box-shadow .18s ease, filter .18s ease;
        }
        .btn:before {
            content: "";
            position: absolute;
            z-index: 0;
            pointer-events: none;
            inset: 0;
            background: linear-gradient(105deg, transparent 20%, rgba(255,255,255,.18) 48%, transparent 72%);
            transform: translateX(-110%);
            transition: transform .48s ease;
        }
        .btn:hover { filter: brightness(1.1) saturate(1.04); transform: translateY(-2px); box-shadow: 0 12px 28px rgba(184,63,8,.25), 0 14px 36px rgba(5,105,121,.23), inset 0 1px 0 rgba(255,255,255,.24); }
        .btn:hover:before { transform: translateX(110%); }
        .btn:active { transform: translateY(0) scale(.985); box-shadow: 0 5px 14px rgba(0,0,0,.2), inset 0 1px 2px rgba(0,0,0,.13); }
        .btn:focus-visible { outline: 3px solid rgba(214,83,13,.35); outline-offset: 3px; }
        .btn.secondary { background: rgba(255,255,255,.07); color: var(--ink); border: 1px solid var(--line); box-shadow: none; text-shadow:none; }
        .btn.secondary:before,.btn.warn:before,.btn.danger:before { display:none; }
        @media (prefers-reduced-motion: reduce) {
            .btn { transition:none; }
            .btn:before { display:none; }
        }
        .btn.warn { background: var(--warn); color: #251100; }
        .btn.danger { background: var(--bad); }
        label { display: block; font-size: 12px; color: var(--muted); margin-bottom: 6px; font-weight: 700; }
        input, select, textarea {
            width: 100%;
            border: 1px solid var(--line);
            border-radius: 8px;
            padding: 10px 11px;
            background: var(--field-bg);
            color: var(--ink);
            min-height: 40px;
            outline: none;
        }
        input:focus, select:focus, textarea:focus { border-color: var(--accent); box-shadow: 0 0 0 3px rgba(24, 199, 255, .12); }
        option { color: #071018; background: #eef8ff; }
        textarea { min-height: 108px; resize: vertical; }
        .form-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 14px; }
        .span-2 { grid-column: span 2; }
        .span-4 { grid-column: span 4; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 11px 10px; border-bottom: 1px solid var(--line); text-align: left; vertical-align: middle; }
        th { color: var(--muted); font-size: 12px; text-transform: uppercase; letter-spacing: .05em; }
        .badge { border-radius: 999px; padding: 4px 8px; font-size: 12px; font-weight: 800; background: var(--soft); color: var(--accent); display: inline-flex; }
        .toast-viewport {
            position: fixed;
            top: 18px;
            right: 18px;
            z-index: 80;
            width: min(380px, calc(100vw - 36px));
            display: grid;
            gap: 10px;
            pointer-events: none;
        }
        .toast {
            position: relative;
            overflow: hidden;
            border: 1px solid var(--line);
            border-radius: 8px;
            padding: 13px 42px 13px 14px;
            color: var(--ink);
            background: var(--panel-bg);
            box-shadow: 0 18px 50px rgba(0,0,0,.28);
            pointer-events: auto;
            animation: toast-in .18s ease-out;
        }
        .toast:before {
            content: "";
            position: absolute;
            inset: 0 auto 0 0;
            width: 4px;
            background: var(--accent);
        }
        .toast.success {
            color:#fff;
            border-color:#22c55e;
            background:#15803d;
        }
        .toast.error {
            color:#fff;
            border-color:#ef4444;
            background:#b91c1c;
        }
        html[data-theme="light"] .toast.success {
            color:#fff;
            background:#16a34a;
        }
        html[data-theme="light"] .toast.error {
            color:#fff;
            background:#dc2626;
        }
        .toast.success:before { background: var(--good); }
        .toast.error:before { background: var(--bad); }
        .toast-title { display: block; margin-bottom: 2px; font-weight: 900; }
        .toast.success .toast-title,.toast.error .toast-title { color:#fff; }
        .toast-message { color: currentColor; opacity: .84; }
        .toast-close {
            position: absolute;
            top: 8px;
            right: 8px;
            width: 28px;
            min-width: 28px;
            height: 28px;
            border: 1px solid var(--line);
            border-radius: 8px;
            color: var(--ink);
            background: rgba(255,255,255,.06);
            cursor: pointer;
        }
        html[data-theme="light"] .toast-close { color:#fff; background:rgba(255,255,255,.12); }
        @keyframes toast-in {
            from { opacity: 0; transform: translateY(-8px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .ai-chat-backdrop { display:none; position:fixed; z-index:89; inset:0; border:0; padding:0; background:rgba(1,5,7,.68); backdrop-filter:blur(3px); }
        .ai-chat-panel { position:fixed; z-index:90; top:0; right:0; width:min(480px,100vw); height:100vh; height:100dvh; display:grid; grid-template-rows:auto auto 1fr auto; overflow:hidden; color:var(--ink); background:radial-gradient(circle at 8% 0%,rgba(255,122,26,.16),transparent 27%),radial-gradient(circle at 100% 26%,rgba(0,184,217,.13),transparent 31%),linear-gradient(180deg,#071419,#02080b 60%); border-left:1px solid rgba(111,220,237,.16); box-shadow:-30px 0 90px rgba(0,0,0,.48); transform:translateX(105%); transition:transform .28s cubic-bezier(.2,.8,.2,1); }
        body.ai-chat-open { overflow:hidden; }
        body.ai-chat-open .ai-chat-panel { transform:translateX(0); }
        body.ai-chat-open .ai-chat-backdrop { display:block; }
        .ai-chat-header { display:flex; align-items:center; justify-content:space-between; gap:12px; padding:20px 20px 17px; border-bottom:1px solid rgba(255,255,255,.08); background:rgba(3,13,17,.5); backdrop-filter:blur(16px); }
        .ai-chat-brand { display:flex; align-items:center; gap:12px; min-width:0; }
        .ai-chat-mark { position:relative; width:44px; height:44px; flex:0 0 44px; display:grid; place-items:center; border-radius:14px; color:#fff; background:linear-gradient(135deg,#ff8a25,#00b8d9); box-shadow:0 10px 25px rgba(0,184,217,.22); overflow:hidden; }
        .ai-chat-mark:after { content:""; position:absolute; width:32px; height:32px; border:1px solid rgba(255,255,255,.3); border-radius:50%; transform:translate(14px,-14px); }
        .ai-chat-mark .icon { position:relative; z-index:1; }
        .ai-chat-brand strong,.ai-chat-brand small { display:block; }.ai-chat-brand strong{font-size:17px;letter-spacing:-.025em}.ai-chat-brand small{display:flex;align-items:center;gap:5px;margin-top:3px;color:#86a4ab;font-size:9px;text-transform:uppercase;letter-spacing:.11em}.ai-chat-brand small:before{content:"";width:6px;height:6px;border-radius:50%;background:#19c7b5;box-shadow:0 0 10px #19c7b5}
        .ai-chat-header-actions { display:flex; gap:7px; }
        .ai-chat-icon-button { width:40px; height:40px; display:grid; place-items:center; border:1px solid rgba(255,255,255,.12); border-radius:12px; color:var(--ink); background:rgba(255,255,255,.045); cursor:pointer; font-size:20px; transition:.18s ease; }.ai-chat-icon-button:hover{border-color:rgba(24,199,255,.5);color:#59def2;background:rgba(0,184,217,.1);transform:translateY(-1px)}
        .ai-chat-history { padding:11px 20px 13px; border-bottom:1px solid rgba(255,255,255,.07); background:rgba(1,7,10,.28); }
        .ai-chat-history-head { display:flex; justify-content:space-between; gap:8px; margin:0 2px 7px; color:var(--muted); font-size:8px; font-weight:900; letter-spacing:.1em; text-transform:uppercase; }.ai-chat-history-head span:last-child{color:#4bbfd0}
        .ai-chat-history select { min-height:42px; padding:8px 12px; border-color:rgba(96,190,207,.2); border-radius:11px; font-size:11px; background:rgba(255,255,255,.045); }
        .ai-chat-messages { min-height:0; overflow-y:auto; padding:22px 20px; display:flex; flex-direction:column; gap:15px; scrollbar-width:thin; background:linear-gradient(90deg,rgba(255,255,255,.012),transparent 18%,transparent 82%,rgba(255,255,255,.012)); }
        .ai-chat-welcome { position:relative; overflow:hidden; margin:auto 0; padding:24px 20px 20px; border:1px solid rgba(90,210,228,.22); border-radius:20px; text-align:center; background:linear-gradient(145deg,rgba(255,122,26,.14),rgba(0,184,217,.08)); box-shadow:inset 0 1px 0 rgba(255,255,255,.06),0 18px 46px rgba(0,0,0,.16); }
        .ai-chat-welcome:before { content:"✦"; display:grid; place-items:center; width:35px; height:35px; margin:0 auto 10px; border-radius:11px; color:#fff; background:linear-gradient(135deg,#ff8a25,#00b8d9); box-shadow:0 8px 22px rgba(0,184,217,.22); font-size:19px; }
        .ai-chat-welcome h3 { margin:5px 0 8px; font-size:18px; letter-spacing:-.03em; }.ai-chat-welcome p{margin:0;color:#a8c1c7;font-size:12px;line-height:1.55}
        .ai-chat-suggestions { display:grid; gap:8px; margin-top:19px; }
        .ai-chat-suggestion { position:relative; padding:11px 35px 11px 13px; border:1px solid rgba(255,255,255,.1); border-radius:11px; color:var(--ink); background:rgba(3,13,17,.5); text-align:left; font:inherit; font-size:11px; font-weight:750; cursor:pointer; transition:.18s ease; }.ai-chat-suggestion:after{content:"→";position:absolute;right:13px;color:#55d9ef;font-size:15px;transition:transform .18s ease}
        .ai-chat-suggestion:hover { border-color:rgba(0,184,217,.48); background:rgba(0,184,217,.1); transform:translateY(-1px); }.ai-chat-suggestion:hover:after{transform:translateX(3px)}
        .ai-chat-message { max-width:90%; padding:13px 14px; border-radius:16px; white-space:pre-wrap; overflow-wrap:anywhere; font-size:12px; line-height:1.65; box-shadow:0 9px 20px rgba(0,0,0,.1); }
        .ai-chat-message.user { align-self:flex-end; color:#fff; background:linear-gradient(125deg,#e6680c,#118fa4); border:1px solid rgba(255,255,255,.12); border-bottom-right-radius:5px; }
        .ai-chat-message.assistant { align-self:flex-start; border:1px solid rgba(126,214,228,.18); background:linear-gradient(145deg,rgba(255,255,255,.065),rgba(255,255,255,.025)); border-bottom-left-radius:5px; }
        .ai-chat-metrics { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:6px; margin-top:10px; }
        .ai-chat-metric { min-width:0; padding:7px 8px; border:1px solid var(--line); border-radius:8px; background:rgba(0,0,0,.14); }
        .ai-chat-metric span,.ai-chat-metric strong { display:block; }
        .ai-chat-metric span { color:var(--muted); font-size:8px; font-weight:800; text-transform:uppercase; letter-spacing:.07em; }
        .ai-chat-metric strong { margin-top:3px; font-size:11px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        .ai-chat-metric.positive strong { color:var(--good); }.ai-chat-metric.negative strong { color:var(--bad); }
        .ai-chat-context { align-self:flex-start; display:inline-flex; align-items:center; gap:6px; color:#53d7eb; font-size:9px; font-weight:850; text-transform:uppercase; letter-spacing:.08em; }
        .ai-chat-context:before { content:""; width:7px; height:7px; border-radius:50%; background:#19c7b5; box-shadow:0 0 9px #19c7b5; }
        .ai-chat-links { display:flex; flex-wrap:wrap; gap:6px; margin-top:9px; }
        .ai-chat-links a { padding:6px 8px; border:1px solid var(--line); border-radius:8px; color:#58d8ed; font-size:9px; font-weight:850; }
        .ai-chat-typing:after { content:"▋"; color:#18c7ff; animation:ai-cursor .65s step-end infinite; }
        @keyframes ai-cursor { 50% { opacity:0; } }
        .ai-chat-composer { padding:14px 20px 18px; border-top:1px solid rgba(255,255,255,.09); background:rgba(1,7,10,.88); backdrop-filter:blur(18px); }
        .ai-chat-input-wrap { position:relative; }.ai-chat-composer textarea { min-height:82px; max-height:150px; padding:13px 13px 27px; border-color:rgba(96,190,207,.23); border-radius:14px; resize:vertical; background:rgba(255,255,255,.045); transition:.18s ease; }.ai-chat-composer textarea:focus{border-color:rgba(24,199,255,.6);box-shadow:0 0 0 3px rgba(24,199,255,.09)}
        .ai-chat-char-count { position:absolute; right:11px; bottom:9px; color:var(--muted); font-size:9px; font-weight:800; }
        .ai-chat-composer-foot { display:flex; align-items:center; justify-content:space-between; gap:9px; margin-top:8px; }
        .ai-chat-disclaimer { color:var(--muted); font-size:8px; max-width:210px; line-height:1.35; }
        .ai-chat-send,.ai-chat-stop { min-width:104px; min-height:42px; border-radius:12px; }
        .ai-chat-stop { display:none; }
        .ai-chat-mobile-launcher { display:none; position:fixed; z-index:60; right:16px; bottom:18px; width:52px; height:52px; place-items:center; border:1px solid rgba(255,255,255,.2); border-radius:16px; color:#fff; background:linear-gradient(135deg,#ff7a1a,#00b8d9); box-shadow:0 16px 38px rgba(0,0,0,.38); cursor:pointer; }
        .ai-chat-composer.is-generating .ai-chat-send { display:none; }
        .ai-chat-composer.is-generating .ai-chat-stop { display:inline-flex; }
        html[data-theme="light"] .ai-chat-panel { background:linear-gradient(180deg,#fff,#f5fbfd); }
        html[data-theme="light"] .ai-chat-composer { background:rgba(255,255,255,.96); }
        .actions { display: flex; gap: 8px; flex-wrap: wrap; }
        .screens { display: flex; gap: 8px; flex-wrap: wrap; }
        .screens img { width: 84px; height: 58px; object-fit: cover; border-radius: 6px; border: 1px solid var(--line); }
        .pagination { display: flex; gap: 7px; list-style: none; padding: 0; }
        .pagination a, .pagination span { display: block; padding: 8px 10px; background: rgba(255,255,255,.06); border: 1px solid var(--line); border-radius: 6px; }
        .exchange-pills { display:flex; gap:7px; margin:0; }
        .exchange-pill { flex:1; padding:7px 9px; border:1px solid var(--line); border-radius:9px; background:rgba(255,255,255,.035); color:var(--muted); font-size:10px; font-weight:800; text-align:center; letter-spacing:.02em; }
        .exchange-pill.shark { color:#63e6be; }
        .exchange-pill.delta { color:#74c0fc; }
        /* Shark Ember theme */
        :root {
            --bg: #03080a;
            --bg-2: #071116;
            --panel: #091419;
            --panel-2: #0d1d23;
            --ink: #f7fbfc;
            --muted: #8fa7ad;
            --line: rgba(255, 122, 26, .17);
            --accent: #ff7a1a;
            --accent-2: #19c7b5;
            --good: #34d399;
            --bad: #fb7185;
            --warn: #fbbf24;
            --soft: rgba(255, 122, 26, .12);
            --body-bg:
                radial-gradient(circle at 12% 0%, rgba(255, 122, 26, .13), transparent 34rem),
                radial-gradient(circle at 88% 8%, rgba(25, 199, 181, .08), transparent 30rem),
                linear-gradient(155deg, #020608 0%, #061015 54%, #010405 100%);
            --sidebar-bg: linear-gradient(180deg, rgba(4, 13, 17, .995), rgba(1, 6, 8, .995));
            --panel-bg: linear-gradient(145deg, rgba(12, 27, 33, .96), rgba(4, 13, 17, .98));
            --field-bg: rgba(255,255,255,.04);
            --nav-text: #a3b8bd;
        }
        html[data-theme="light"] {
            --bg: #fff8f1;
            --bg-2: #ffffff;
            --panel: #ffffff;
            --panel-2: #fffaf5;
            --ink: #292019;
            --muted: #75665b;
            --line: rgba(196, 83, 17, .17);
            --accent: #e7620b;
            --accent-2: #0f9f91;
            --good: #059669;
            --bad: #e11d48;
            --warn: #d97706;
            --soft: rgba(231, 98, 11, .09);
            --body-bg: radial-gradient(circle at 8% 0%, rgba(255,122,26,.14), transparent 28rem), linear-gradient(145deg, #fffdfb, #fff5eb);
            --sidebar-bg: linear-gradient(180deg, rgba(255,255,255,.98), rgba(255,247,239,.98));
            --panel-bg: linear-gradient(145deg, rgba(255,255,255,.98), rgba(255,250,245,.96));
            --field-bg: rgba(255,255,255,.9);
            --nav-text: #706259;
        }
        .brand:before { background:transparent url("{{ asset('images/branding/tradeyatra-icon-v2.png') }}") center/contain no-repeat; box-shadow:none; }
        .btn:not(.secondary):not(.danger):not(.warn) { background:linear-gradient(115deg,#ff4c00 0%,#bf4911 40%,#12a5ba 72%,#11a5bd 100%); color:#fff; box-shadow:0 12px 34px rgba(8,124,141,.24); }
        .btn:not(.secondary):not(.danger):not(.warn):hover { box-shadow:0 16px 40px rgba(255,122,26,.2),0 8px 24px rgba(0,184,217,.22); }
        html[data-theme="light"] .btn:not(.secondary):not(.danger):not(.warn) { background:linear-gradient(115deg,#ff4c00 0%,#bf4911 40%,#12a5ba 72%,#11a5bd 100%); color:#fff; box-shadow:0 12px 30px rgba(0,152,183,.18); }
        .nav a.active, .nav a:hover { background:linear-gradient(90deg,color-mix(in srgb,var(--accent) 17%,transparent),color-mix(in srgb,var(--accent-2) 6%,transparent)); border-color:color-mix(in srgb,var(--accent) 30%,transparent); }
        .nav a.active { box-shadow:inset 3px 0 var(--accent); }
        .panel, .card { min-width:0; box-shadow:0 18px 55px rgba(3,4,14,.15), inset 0 1px 0 rgba(255,255,255,.055); }
        .table-wrap { max-width:100%; overflow-x:auto; overscroll-behavior-inline:contain; scrollbar-width:thin; scrollbar-color:color-mix(in srgb,var(--accent) 65%,transparent) transparent; }
        .table-wrap::-webkit-scrollbar { height:8px; }
        .table-wrap::-webkit-scrollbar-thumb { border-radius:999px; background:color-mix(in srgb,var(--accent) 65%,transparent); }
        .nav-icon { color:color-mix(in srgb,var(--accent) 72%,white); background:var(--soft); }
        .exchange-pill.shark { color:#34d399; background:rgba(52,211,153,.07); }
        .exchange-pill.shark { color:#45c9e8; background:rgba(0,184,217,.08); }
        .exchange-pill.delta { color:#ff9a47; background:rgba(255,122,26,.08); }
        input:focus, select:focus, textarea:focus { border-color:var(--accent); box-shadow:0 0 0 3px color-mix(in srgb,var(--accent) 14%,transparent); }
        @media (max-width: 980px) {
            .shell { display:block; }
            .sidebar {
                position:fixed;
                inset:0 auto 0 0;
                z-index:80;
                width:min(320px,calc(100vw - 48px));
                height:100vh;
                height:100dvh;
                max-height:none;
                overflow-y:auto;
                overscroll-behavior:contain;
                transform:translateX(-105%);
                transition:transform .22s ease;
            }
            body.nav-open { overflow:hidden; }
            body.nav-open .sidebar { transform:translateX(0); }
            .sidebar-backdrop { position:fixed; inset:0; z-index:70; border:0; padding:0; background:rgba(2,8,11,.72); backdrop-filter:blur(3px); cursor:pointer; }
            body.nav-open .sidebar-backdrop { display:block; }
            .menu-toggle { display:inline-grid; place-items:center; width:42px; min-width:42px; height:42px; padding:0; border:1px solid var(--line); border-radius:11px; color:var(--ink); background:rgba(255,255,255,.04); cursor:pointer; }
            .sidebar-header { border-bottom:0; }
            .nav { display:grid; grid-template-columns:1fr; overflow:visible; }
            .nav-group { grid-column:1; align-content:start; }
            .cols-2, .cols-3, .cols-4, .form-grid { grid-template-columns: 1fr; }
            .span-2, .span-4 { grid-column: span 1; }
            .topbar { align-items: flex-start; flex-direction: column; }
            .topnav { margin:-24px -24px 22px; grid-template-columns:auto 1fr auto; }
            .topnav-news { grid-column:1; gap:8px; }
            .topnav-actions { grid-column:2; justify-content:center; transform:none; }
            .topnav-user { grid-column:3; }
            .header-links { grid-column:1/-1; grid-row:2; justify-content:flex-start; overflow-x:auto; padding-bottom:2px; }
            .header-links a { white-space:nowrap; }
            .topnav-user { justify-content:flex-end; }
            .table-wrap { overflow-x: auto; }
        }
        @media (max-width: 680px) {
            .main { padding:16px; }
            .topnav { padding:10px 16px; margin:-24px -24px 20px; gap:10px; }
            .main .topnav { margin:-16px -16px 18px; }
            .topnav-actions { width:auto; }
            .topnav-actions { transform:translateX(-8px); }
            .future-nav-button { flex:0 1 auto; }
            .header-context small,.account-copy,.logout-label { display:none; }
            .header-context { min-width:0; }
            .header-links { gap:2px; }
            .header-links a { padding:8px 10px; }
            .account-chip { padding:4px; border:0; background:transparent; }
            .logout-button { width:40px; min-width:40px; padding:0; }
        }
        @media (max-width: 520px) {
            .topnav-actions { display:none; }
            .topnav { grid-template-columns:1fr auto; }
            .topnav-news { grid-column:1; }
            .topnav-user { grid-column:2; }
            .ai-chat-panel { left:0; right:0; width:auto; max-width:none; border-left:0; }
            .ai-chat-mobile-launcher { display:grid; }
            body.ai-chat-open .ai-chat-mobile-launcher { display:none; }
        }
        button, .btn, a.btn, [role="button"] { font-family:inherit; font-weight:600 !important; letter-spacing:normal; }
    </style>
    @stack('styles')
</head>
<body>
<script>
    window.tradeYatraNavigationController?.abort();
    window.tradeYatraNavigationController = new AbortController();
    window.tradeYatraNavigationSignal = window.tradeYatraNavigationController.signal;
</script>
@php
    $toastMessages = collect();

    if (session('success')) {
        $toastMessages->push(['type' => 'success', 'title' => 'Success', 'message' => session('success')]);
    }

    if (session('error')) {
        $toastMessages->push(['type' => 'error', 'title' => 'Error', 'message' => session('error')]);
    }

    if ($errors->any()) {
        $toastMessages->push(['type' => 'error', 'title' => 'Please check', 'message' => $errors->first()]);
    }

    if (trim($__env->yieldContent('toast_error')) !== '') {
        $toastMessages->push(['type' => 'error', 'title' => 'Error', 'message' => trim($__env->yieldContent('toast_error'))]);
    }
@endphp
@if($toastMessages->isNotEmpty())
    <div class="toast-viewport" aria-live="polite" aria-atomic="true">
        @foreach($toastMessages as $toast)
            <div class="toast {{ $toast['type'] }}" role="status">
                <strong class="toast-title">{{ $toast['title'] }}</strong>
                <div class="toast-message">{{ $toast['message'] }}</div>
                <button class="toast-close" type="button" aria-label="Dismiss message" onclick="this.closest('.toast').remove()">x</button>
            </div>
        @endforeach
    </div>
@endif
<svg width="0" height="0" style="position:absolute" aria-hidden="true" focusable="false">
    <symbol id="icon-dashboard" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="8" rx="1"></rect><rect x="14" y="3" width="7" height="5" rx="1"></rect><rect x="14" y="12" width="7" height="9" rx="1"></rect><rect x="3" y="15" width="7" height="6" rx="1"></rect></symbol>
    <symbol id="icon-trades" viewBox="0 0 24 24"><path d="M4 17h16"></path><path d="M7 14l3-3 3 2 4-6"></path><path d="M17 7h3v3"></path></symbol>
    <symbol id="icon-plus" viewBox="0 0 24 24"><path d="M12 5v14"></path><path d="M5 12h14"></path></symbol>
    <symbol id="icon-analytics" viewBox="0 0 24 24"><path d="M4 19V5"></path><path d="M4 19h16"></path><rect x="7" y="11" width="3" height="5" rx="1"></rect><rect x="12" y="7" width="3" height="9" rx="1"></rect><rect x="17" y="9" width="3" height="7" rx="1"></rect></symbol>
    <symbol id="icon-week" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="17" rx="2"></rect><path d="M8 2v4"></path><path d="M16 2v4"></path><path d="M3 9h18"></path><path d="M8 14h.01"></path><path d="M12 14h.01"></path><path d="M16 14h.01"></path></symbol>
    <symbol id="icon-calendar" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="17" rx="2"></rect><path d="M8 2v4"></path><path d="M16 2v4"></path><path d="M3 9h18"></path></symbol>
    <symbol id="icon-sync" viewBox="0 0 24 24"><path d="M21 12a9 9 0 0 1-15.5 6.2"></path><path d="M3 12A9 9 0 0 1 18.5 5.8"></path><path d="M18 2v4h-4"></path><path d="M6 22v-4h4"></path></symbol>
    <symbol id="icon-market" viewBox="0 0 24 24"><path d="M3 17l6-6 4 4 7-8"></path><path d="M14 7h6v6"></path></symbol>
    <symbol id="icon-settings" viewBox="0 0 24 24"><path d="M12 15.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7Z"></path><path d="M19.4 15a1.7 1.7 0 0 0 .3 1.9l.1.1a2 2 0 1 1-2.8 2.8l-.1-.1a1.7 1.7 0 0 0-1.9-.3 1.7 1.7 0 0 0-1 1.6V21a2 2 0 1 1-4 0v-.1a1.7 1.7 0 0 0-1-1.6 1.7 1.7 0 0 0-1.9.3l-.1.1A2 2 0 1 1 4.2 17l.1-.1a1.7 1.7 0 0 0 .3-1.9 1.7 1.7 0 0 0-1.6-1H3a2 2 0 1 1 0-4h.1a1.7 1.7 0 0 0 1.6-1 1.7 1.7 0 0 0-.3-1.9L4.3 7A2 2 0 1 1 7.1 4.2l.1.1a1.7 1.7 0 0 0 1.9.3 1.7 1.7 0 0 0 1-1.6V3a2 2 0 1 1 4 0v.1a1.7 1.7 0 0 0 1 1.6 1.7 1.7 0 0 0 1.9-.3l.1-.1A2 2 0 1 1 19.8 7l-.1.1a1.7 1.7 0 0 0-.3 1.9 1.7 1.7 0 0 0 1.6 1h.1a2 2 0 1 1 0 4H21a1.7 1.7 0 0 0-1.6 1Z"></path></symbol>
    <symbol id="icon-user" viewBox="0 0 24 24"><path d="M20 21a8 8 0 0 0-16 0"></path><circle cx="12" cy="7" r="4"></circle></symbol>
    <symbol id="icon-logout" viewBox="0 0 24 24"><path d="M10 17l5-5-5-5"></path><path d="M15 12H3"></path><path d="M21 3v18"></path></symbol>
    <symbol id="icon-sun" viewBox="0 0 24 24"><circle cx="12" cy="12" r="4"></circle><path d="M12 2v2"></path><path d="M12 20v2"></path><path d="M4.93 4.93l1.41 1.41"></path><path d="M17.66 17.66l1.41 1.41"></path><path d="M2 12h2"></path><path d="M20 12h2"></path><path d="M4.93 19.07l1.41-1.41"></path><path d="M17.66 6.34l1.41-1.41"></path></symbol>
    <symbol id="icon-moon" viewBox="0 0 24 24"><path d="M20.5 14.5A8.5 8.5 0 0 1 9.5 3.5 7 7 0 1 0 20.5 14.5Z"></path></symbol>
    <symbol id="icon-ai" viewBox="0 0 24 24"><path d="M12 3 14 8l5 2-5 2-2 5-2-5-5-2 5-2 2-5Z"></path><path d="m19 16 .8 2.2L22 19l-2.2.8L19 22l-.8-2.2L16 19l2.2-.8L19 16Z"></path></symbol>
    <symbol id="icon-news" viewBox="0 0 24 24"><path d="M4 5h13v14H4z"></path><path d="M17 9h3v8a2 2 0 0 1-2 2"></path><path d="M7 9h7M7 13h7M7 16h4"></path></symbol>
</svg>
<div class="shell">
    <aside class="sidebar">
        <div class="sidebar-header">
            <div class="brand-row"><a class="brand" href="{{ route('dashboard') }}" wire:navigate.hover><span>TradeYatra<small>Your trading journey</small></span></a></div>
            <div class="exchange-pills"><span class="exchange-pill shark">● Shark</span><span class="exchange-pill delta">● Delta</span></div>
            <a class="brand-support-link" href="{{ route('support-fund.index') }}">Support TradeYatra</a>
        </div>
        <nav class="nav">
            <div class="nav-group">
                <div class="nav-label">Journal</div>
                <a class="{{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}" wire:navigate.hover><span class="nav-icon"><svg class="icon"><use href="#icon-dashboard"></use></svg></span>Overview<span class="nav-arrow">›</span></a>
                <a class="{{ request()->routeIs('trades.index') ? 'active' : '' }}" href="{{ route('trades.index') }}" wire:navigate.hover><span class="nav-icon"><svg class="icon"><use href="#icon-trades"></use></svg></span>All trades<span class="nav-arrow">›</span></a>
                <a class="{{ request()->routeIs('trades.shark') ? 'active' : '' }}" href="{{ route('trades.shark') }}" wire:navigate.hover><span class="nav-icon"><svg class="icon"><use href="#icon-trades"></use></svg></span>Shark trades<span class="nav-arrow">›</span></a>
                <a class="{{ request()->routeIs('trades.delta') ? 'active' : '' }}" href="{{ route('trades.delta') }}" wire:navigate.hover><span class="nav-icon"><svg class="icon"><use href="#icon-trades"></use></svg></span>Delta trades<span class="nav-arrow">›</span></a>
                <a class="{{ request()->routeIs('trades.calendar') ? 'active' : '' }}" href="{{ route('trades.calendar') }}" wire:navigate.hover><span class="nav-icon"><svg class="icon"><use href="#icon-calendar"></use></svg></span>Calendar<span class="nav-arrow">›</span></a>
            </div>
            <div class="nav-group">
                <div class="nav-label">Insights</div>
                <a class="{{ request()->routeIs('trades.analysis') ? 'active' : '' }}" href="{{ route('trades.analysis') }}" wire:navigate.hover><span class="nav-icon"><svg class="icon"><use href="#icon-week"></use></svg></span>Reports<span class="nav-arrow">›</span></a>
                <a class="{{ request()->routeIs('shark.market') ? 'active' : '' }}" href="{{ route('shark.market') }}" wire:navigate.hover><span class="nav-icon"><svg class="icon"><use href="#icon-market"></use></svg></span>Market<span class="nav-arrow">›</span></a>
            </div>
            <div class="nav-group exchange-group">
                <div class="nav-label">Exchanges</div>
                <a class="{{ request()->routeIs('shark.sync') ? 'active' : '' }}" href="{{ route('shark.sync') }}" wire:navigate.hover><span class="nav-icon"><svg class="icon"><use href="#icon-sync"></use></svg></span>Shark sync<span class="nav-arrow">›</span></a>
                <a class="{{ request()->routeIs('shark.settings') ? 'active' : '' }}" href="{{ route('shark.settings') }}" wire:navigate.hover><span class="nav-icon"><svg class="icon"><use href="#icon-settings"></use></svg></span>Shark API<span class="nav-arrow">›</span></a>
                <a class="{{ request()->routeIs('delta.sync') ? 'active' : '' }}" href="{{ route('delta.sync') }}" wire:navigate.hover><span class="nav-icon"><svg class="icon"><use href="#icon-sync"></use></svg></span>Delta sync<span class="nav-arrow">›</span></a>
                <a class="{{ request()->routeIs('delta.settings') ? 'active' : '' }}" href="{{ route('delta.settings') }}" wire:navigate.hover><span class="nav-icon"><svg class="icon"><use href="#icon-settings"></use></svg></span>Delta API<span class="nav-arrow">›</span></a>
            </div>
            <div class="nav-group">
                <div class="nav-label">Account</div>
                @php($supportUnread = auth()->user()->supportTickets()->sum('user_unread_count'))
                <a class="{{ request()->routeIs('support.*') ? 'active' : '' }}" href="{{ route('support.index') }}" wire:navigate.hover>
                    <span class="nav-icon"><svg class="icon"><use href="#icon-news"></use></svg></span>Support
                    @if($supportUnread)
                        <span class="nav-count">{{ min($supportUnread, 99) }}</span>
                    @else
                        <span class="nav-arrow">›</span>
                    @endif
                </a>
                <a class="{{ request()->routeIs('profile.edit') ? 'active' : '' }}" href="{{ route('profile.edit') }}" wire:navigate.hover><span class="nav-icon"><svg class="icon"><use href="#icon-user"></use></svg></span>Profile &amp; settings<span class="nav-arrow">›</span></a>
            </div>
        </nav>
    </aside>
    <button class="sidebar-backdrop" type="button" data-nav-close aria-label="Close navigation"></button>
    <main class="main">
        @auth
            <div class="topnav">
                <div class="topnav-news">
                    <button class="menu-toggle" type="button" data-nav-toggle aria-label="Open navigation" aria-controls="workspaceSidebar" aria-expanded="false"><svg class="icon" viewBox="0 0 24 24"><path d="M4 7h16M4 12h16M4 17h16"></path></svg></button>
                    <a class="future-nav-button news {{ request()->routeIs('news.*') ? 'active' : '' }}" href="{{ route('news.index') }}" title="Open market news"><svg class="icon"><use href="#icon-news"></use></svg>News</a>
                </div>
                <div class="topnav-actions" aria-label="Upcoming workspace tools">
                    <button class="future-nav-button ai" id="aiChatToggle" type="button" title="Open Yatra AI" aria-controls="aiChatPanel" aria-expanded="false"><svg class="icon"><use href="#icon-ai"></use></svg><span class="ai-label">Yatra AI</span></button>
                </div>
                <div class="topnav-user">
                    <button type="button" class="btn secondary theme-toggle" id="themeToggle" aria-label="Toggle light and dark mode" title="Toggle light and dark mode">
                        <svg class="icon sun-icon"><use href="#icon-sun"></use></svg>
                        <svg class="icon moon-icon"><use href="#icon-moon"></use></svg>
                    </button>
                    <a class="account-chip" href="{{ route('profile.edit') }}" aria-label="Open profile">
                        <span class="user-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 2)) }}</span>
                        <span class="account-copy"><strong>{{ auth()->user()->name }}</strong><small>{{ auth()->user()->currency }} · {{ auth()->user()->timezone }}</small></span>
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="logout-button" type="submit"><svg class="icon"><use href="#icon-logout"></use></svg><span class="logout-label">Log out</span></button>
                    </form>
                </div>
            </div>
        @endauth
        <div class="topbar">
            <div>
                <h1>@yield('page_title', 'Trading Journal')</h1>
                <div class="muted">@yield('page_subtitle', 'Plan, import, review, and improve every trade.')</div>
            </div>
            <div class="actions">
                @unless(request()->routeIs('support.*'))
                <details class="sync-menu">
                    <summary class="btn secondary"><svg class="icon"><use href="#icon-sync"></use></svg>Sync exchanges<svg class="icon sync-chevron" viewBox="0 0 24 24"><path d="m7 10 5 5 5-5"></path></svg></summary>
                    <div class="sync-menu-popover">
                        <div class="sync-menu-title">Choose an exchange</div>
                        <a class="sync-choice shark" href="{{ route('shark.sync') }}"><span class="sync-choice-icon"><svg class="icon"><use href="#icon-sync"></use></svg></span><span><strong>Shark Exchange</strong><small>Open Shark sync controls</small></span></a>
                        <a class="sync-choice delta" href="{{ route('delta.sync') }}"><span class="sync-choice-icon"><svg class="icon"><use href="#icon-sync"></use></svg></span><span><strong>Delta Exchange</strong><small>Open Delta sync controls</small></span></a>
                    </div>
                </details>
                @endunless
            </div>
        </div>

        @yield('content')
    </main>
</div>
@auth
<button class="ai-chat-backdrop" id="aiChatBackdrop" type="button" aria-label="Close AI chat"></button>
<aside class="ai-chat-panel" id="aiChatPanel" aria-label="Yatra AI" aria-hidden="true"
    data-index-url="{{ route('ai-chat.index') }}"
    data-conversation-url="{{ route('ai-chat.conversations.create') }}"
    data-message-url="{{ route('ai-chat.messages.store') }}">
    <header class="ai-chat-header">
        <div class="ai-chat-brand"><span class="ai-chat-mark"><svg class="icon"><use href="#icon-ai"></use></svg></span><span><strong>TradeYatra Insights</strong><small>Your trading journal coach</small></span></div>
        <div class="ai-chat-header-actions"><button class="ai-chat-icon-button" id="aiChatNew" type="button" title="New conversation" aria-label="New conversation">+</button><button class="ai-chat-icon-button" id="aiChatClose" type="button" aria-label="Close AI chat">×</button></div>
    </header>
    <div class="ai-chat-history"><div class="ai-chat-history-head"><span>Conversation</span><span>Private journal data</span></div><label class="sr-only" for="aiChatConversation">Chat history</label><select id="aiChatConversation" aria-label="Chat history"><option value="">New conversation</option></select></div>
    <div class="ai-chat-messages" id="aiChatMessages"></div>
    <form class="ai-chat-composer" id="aiChatForm">
        <label class="sr-only" for="aiChatInput">Ask about your trading journal</label>
        <div class="ai-chat-input-wrap"><textarea id="aiChatInput" maxlength="1000" placeholder="Ask about your trades, strategies, losses, discipline, or plan..."></textarea><span class="ai-chat-char-count" id="aiChatCharacterCount">0 / 1000</span></div>
        <div class="ai-chat-composer-foot"><span class="ai-chat-disclaimer">Journal analysis only · Not financial advice</span><button class="btn ai-chat-send" type="submit">Send</button><button class="btn secondary ai-chat-stop" id="aiChatStop" type="button">Stop</button></div>
    </form>
</aside>
<button class="ai-chat-mobile-launcher" id="aiChatMobileToggle" type="button" aria-label="Open Yatra AI"><svg class="icon"><use href="#icon-ai"></use></svg></button>
@endauth
@livewireScripts
<script>
(() => {
setTimeout(() => {
    document.querySelectorAll('.toast').forEach((toast) => toast.remove());
}, 6000);

window.showAppToast = (type, title, message) => {
    let viewport = document.querySelector('.toast-viewport');
    if (!viewport) {
        viewport = document.createElement('div');
        viewport.className = 'toast-viewport';
        viewport.setAttribute('aria-live', 'polite');
        viewport.setAttribute('aria-atomic', 'true');
        document.body.appendChild(viewport);
    }

    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.setAttribute('role', 'status');

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
};

const themeToggle = document.getElementById('themeToggle');
const sidebar = document.querySelector('.sidebar');
const navToggle = document.querySelector('[data-nav-toggle]');
const navClose = document.querySelector('[data-nav-close]');
const storedTheme = localStorage.getItem('journal-theme') || 'dark';

if (sidebar) sidebar.id = 'workspaceSidebar';

function setNavigationOpen(open) {
    document.body.classList.toggle('nav-open', open);
    navToggle?.setAttribute('aria-expanded', open ? 'true' : 'false');
    navToggle?.setAttribute('aria-label', open ? 'Close navigation' : 'Open navigation');
}

navToggle?.addEventListener('click', () => setNavigationOpen(!document.body.classList.contains('nav-open')));
navClose?.addEventListener('click', () => setNavigationOpen(false));
sidebar?.querySelectorAll('a').forEach((link) => link.addEventListener('click', () => setNavigationOpen(false)));
document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') setNavigationOpen(false);
}, { signal: window.tradeYatraNavigationSignal });
window.addEventListener('resize', () => {
    if (window.innerWidth > 980) setNavigationOpen(false);
}, { signal: window.tradeYatraNavigationSignal });

function applyTheme(theme) {
    document.documentElement.dataset.theme = theme;
    if (themeToggle) {
        const label = theme === 'light' ? 'Switch to dark mode' : 'Switch to light mode';
        themeToggle.setAttribute('aria-label', label);
        themeToggle.setAttribute('title', label);
        themeToggle.setAttribute('aria-pressed', theme === 'light' ? 'true' : 'false');
    }
}

applyTheme(storedTheme);

themeToggle?.addEventListener('click', () => {
    const nextTheme = document.documentElement.dataset.theme === 'light' ? 'dark' : 'light';
    localStorage.setItem('journal-theme', nextTheme);
    applyTheme(nextTheme);
});

const aiChatPanel = document.getElementById('aiChatPanel');
const aiChatToggle = document.getElementById('aiChatToggle');
const aiChatMobileToggle = document.getElementById('aiChatMobileToggle');
const aiChatBackdrop = document.getElementById('aiChatBackdrop');
const aiChatClose = document.getElementById('aiChatClose');
const aiChatNew = document.getElementById('aiChatNew');
const aiChatConversation = document.getElementById('aiChatConversation');
const aiChatMessages = document.getElementById('aiChatMessages');
const aiChatForm = document.getElementById('aiChatForm');
const aiChatInput = document.getElementById('aiChatInput');
const aiChatCharacterCount = document.getElementById('aiChatCharacterCount');
const aiChatStop = document.getElementById('aiChatStop');
let aiChatLoaded = false;
let aiChatConversationId = null;
let aiChatGenerationController = null;
let aiChatStreamTimer = null;

function updateAiChatCharacterCount() {
    if (aiChatCharacterCount && aiChatInput) aiChatCharacterCount.textContent = `${aiChatInput.value.length} / 1000`;
}
updateAiChatCharacterCount();

function setAiChatOpen(open) {
    document.body.classList.toggle('ai-chat-open', open);
    aiChatPanel?.setAttribute('aria-hidden', open ? 'false' : 'true');
    aiChatToggle?.setAttribute('aria-expanded', open ? 'true' : 'false');
    if (open) setTimeout(() => aiChatInput?.focus(), 180);
}

function aiChatScrollToBottom() {
    if (aiChatMessages) aiChatMessages.scrollTop = aiChatMessages.scrollHeight;
}

function renderAiChatHistory(conversations = []) {
    if (!aiChatConversation) return;
    aiChatConversation.innerHTML = '<option value="">New conversation</option>';
    conversations.forEach((conversation) => {
        const option = document.createElement('option');
        option.value = conversation.id;
        option.textContent = conversation.title;
        option.selected = String(conversation.id) === String(aiChatConversationId);
        aiChatConversation.appendChild(option);
    });
}

function renderAiChatMessage(message, stream = false) {
    if (!aiChatMessages) return null;
    if (message.context) {
        const context = document.createElement('div');
        context.className = 'ai-chat-context';
        context.textContent = message.context;
        aiChatMessages.appendChild(context);
    }
    const bubble = document.createElement('div');
    bubble.className = `ai-chat-message ${message.role}`;
    bubble.textContent = stream ? '' : message.content;
    aiChatMessages.appendChild(bubble);
    if (!stream && message.metrics?.length) appendAiChatMetrics(bubble, message.metrics);
    if (!stream && message.links?.length) appendAiChatLinks(bubble, message.links);
    aiChatScrollToBottom();
    return bubble;
}

function appendAiChatMetrics(bubble, metrics = []) {
    const wrapper = document.createElement('div');
    wrapper.className = 'ai-chat-metrics';
    metrics.slice(0, 4).forEach((metric) => {
        const card = document.createElement('div');
        card.className = `ai-chat-metric ${metric.tone || 'neutral'}`;
        const label = document.createElement('span');
        label.textContent = metric.label;
        const value = document.createElement('strong');
        value.textContent = metric.value;
        card.append(label, value);
        wrapper.appendChild(card);
    });
    bubble.appendChild(wrapper);
}

function appendAiChatLinks(bubble, links = []) {
    if (!links.length) return;
    const wrapper = document.createElement('div');
    wrapper.className = 'ai-chat-links';
    links.forEach((link) => {
        const anchor = document.createElement('a');
        anchor.href = link.url;
        anchor.textContent = link.label;
        wrapper.appendChild(anchor);
    });
    bubble.appendChild(wrapper);
}

function renderAiChatWelcome(suggestions = []) {
    if (!aiChatMessages) return;
    const welcome = document.createElement('div');
    welcome.className = 'ai-chat-welcome';
    const heading = document.createElement('h3');
    heading.textContent = 'Namaste! What should we review?';
    const copy = document.createElement('p');
    copy.textContent = 'I analyse your TradeYatra journal using free, private database calculations.';
    const choices = document.createElement('div');
    choices.className = 'ai-chat-suggestions';
    suggestions.forEach((suggestion) => {
        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'ai-chat-suggestion';
        button.textContent = suggestion;
        button.addEventListener('click', () => {
            aiChatInput.value = suggestion;
            aiChatForm.requestSubmit();
        });
        choices.appendChild(button);
    });
    welcome.append(heading, copy, choices);
    aiChatMessages.appendChild(welcome);
}

async function loadAiChat(conversationId = null) {
    if (!aiChatPanel || !aiChatMessages) return;
    aiChatMessages.innerHTML = '<div class="ai-chat-context">Loading journal conversations</div>';
    const url = new URL(aiChatPanel.dataset.indexUrl, window.location.href);
    if (conversationId) url.searchParams.set('conversation_id', conversationId);
    try {
        const response = await fetch(url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
        if (!response.ok) throw new Error('Chat history could not be loaded.');
        const data = await response.json();
        aiChatConversationId = data.conversation?.id || null;
        aiChatMessages.innerHTML = '';
        renderAiChatHistory(data.conversations);
        if (data.conversation?.messages?.length) data.conversation.messages.forEach((message) => renderAiChatMessage(message));
        else renderAiChatWelcome(data.suggestions || []);
        aiChatLoaded = true;
    } catch (error) {
        aiChatMessages.innerHTML = '';
        renderAiChatMessage({ role:'assistant', content:error.message || 'Chat could not be loaded.' });
    }
}

function stopAiChatGeneration() {
    aiChatGenerationController?.abort();
    aiChatGenerationController = null;
    if (aiChatStreamTimer) clearTimeout(aiChatStreamTimer);
    aiChatStreamTimer = null;
    aiChatForm?.classList.remove('is-generating');
    aiChatInput?.removeAttribute('disabled');
}

function streamAiChatMessage(message) {
    return new Promise((resolve) => {
        const bubble = renderAiChatMessage(message, true);
        bubble.classList.add('ai-chat-typing');
        let position = 0;
        const write = () => {
            if (!aiChatForm.classList.contains('is-generating')) return resolve();
            position = Math.min(message.content.length, position + 4);
            bubble.firstChild ? bubble.firstChild.nodeValue = message.content.slice(0, position) : bubble.append(document.createTextNode(message.content.slice(0, position)));
            aiChatScrollToBottom();
            if (position >= message.content.length) {
                bubble.classList.remove('ai-chat-typing');
                appendAiChatLinks(bubble, message.links || []);
                return resolve();
            }
            aiChatStreamTimer = setTimeout(write, 14);
        };
        write();
    });
}

aiChatToggle?.addEventListener('click', () => {
    setAiChatOpen(true);
    if (!aiChatLoaded) loadAiChat();
});
aiChatMobileToggle?.addEventListener('click', () => {
    setAiChatOpen(true);
    if (!aiChatLoaded) loadAiChat();
});
aiChatClose?.addEventListener('click', () => setAiChatOpen(false));
aiChatBackdrop?.addEventListener('click', () => setAiChatOpen(false));
aiChatStop?.addEventListener('click', stopAiChatGeneration);
aiChatInput?.addEventListener('input', updateAiChatCharacterCount);
aiChatConversation?.addEventListener('change', () => loadAiChat(aiChatConversation.value || null));
aiChatNew?.addEventListener('click', async () => {
    stopAiChatGeneration();
    const response = await fetch(aiChatPanel.dataset.conversationUrl, {
        method:'POST',
        headers:{ 'Accept':'application/json', 'X-Requested-With':'XMLHttpRequest', 'X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content },
    });
    if (!response.ok) return window.showAppToast?.('error', 'Chat error', 'A new conversation could not be created.');
    const data = await response.json();
    aiChatConversationId = data.conversation.id;
    aiChatMessages.innerHTML = '';
    renderAiChatHistory(data.conversations);
    renderAiChatWelcome(['Give me a weekly coaching review','Summarise my performance this month','Compare my Shark and Delta trades','What mistakes appear in my losing trades?']);
    aiChatInput.focus();
});

aiChatForm?.addEventListener('submit', async (event) => {
    event.preventDefault();
    const message = aiChatInput.value.trim();
    if (!message || aiChatForm.classList.contains('is-generating')) return;
    if (aiChatMessages.querySelector('.ai-chat-welcome')) aiChatMessages.innerHTML = '';
    renderAiChatMessage({ role:'user', content:message });
    aiChatInput.value = '';
    updateAiChatCharacterCount();
    aiChatInput.disabled = true;
    aiChatForm.classList.add('is-generating');
    aiChatGenerationController = new AbortController();

    try {
        const response = await fetch(aiChatPanel.dataset.messageUrl, {
            method:'POST',
            headers:{ 'Accept':'application/json', 'X-Requested-With':'XMLHttpRequest', 'X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content, 'Content-Type':'application/json' },
            body:JSON.stringify({ conversation_id:aiChatConversationId, message }),
            signal:aiChatGenerationController.signal,
        });
        const data = await response.json().catch(() => ({}));
        if (!response.ok) throw new Error(data.message || 'The journal analysis could not be completed.');
        aiChatConversationId = data.conversation_id;
        renderAiChatHistory(data.conversations);
        await streamAiChatMessage(data.message);
    } catch (error) {
        if (error.name !== 'AbortError') renderAiChatMessage({ role:'assistant', content:error.message || 'The journal analysis could not be completed.' });
    } finally {
        stopAiChatGeneration();
        aiChatInput?.focus();
    }
});

document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && document.body.classList.contains('ai-chat-open')) setAiChatOpen(false);
}, { signal: window.tradeYatraNavigationSignal });
})();
</script>
</body>
</html>
