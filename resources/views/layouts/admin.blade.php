<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title>@yield('title', 'Admin') | TradeYatra</title>
    <link rel="icon" type="image/png" href="{{ asset('images/branding/tradeyatra-icon-v2.png') }}">
    <style>
        :root{--bg:#061014;--panel:#0d1a20;--panel2:#12232a;--line:rgba(255,255,255,.1);--ink:#f4f9fa;--muted:#8ea5ad;--orange:#ad3b07;--teal:#075762;--bad:#fb7185}*{box-sizing:border-box}body{margin:0;color:var(--ink);background:radial-gradient(circle at 12% 0,rgba(255,122,26,.13),transparent 30rem),var(--bg);font:14px/1.5 ui-sans-serif,system-ui,-apple-system,"Segoe UI",sans-serif}a{color:inherit;text-decoration:none}button,input,select,textarea{font:inherit}.admin-shell{min-height:100vh;display:grid;grid-template-columns:230px 1fr}.sidebar{position:sticky;top:0;height:100vh;padding:22px 16px;border-right:1px solid var(--line);background:rgba(6,15,19,.94)}.brand{display:flex;align-items:center;gap:10px;padding:0 8px 22px;font-size:18px;font-weight:800}.brand img{width:34px;height:34px;object-fit:contain}.admin-label{margin-left:auto;padding:3px 6px;border-radius:5px;color:#ffd2b1;background:rgba(255,122,26,.12);font-size:9px;text-transform:uppercase;letter-spacing:.1em}.nav{display:grid;gap:5px}.nav a{padding:10px 12px;border-radius:8px;color:var(--muted);font-weight:600}.nav a:hover,.nav a.active{color:#fff;background:linear-gradient(90deg,rgba(255,122,26,.18),rgba(25,199,181,.08))}.sidebar-foot{position:absolute;left:16px;right:16px;bottom:20px}.sidebar-foot small{display:block;margin:0 8px 9px;color:var(--muted);overflow:hidden;text-overflow:ellipsis}.link-button{width:100%;padding:9px 11px;border:1px solid var(--line);border-radius:8px;color:var(--muted);background:transparent;text-align:left;cursor:pointer}.content{min-width:0;padding:30px}.page-head{display:flex;align-items:flex-end;justify-content:space-between;gap:18px;margin-bottom:24px}.page-head h1{margin:0;font-size:28px}.page-head p{margin:4px 0 0;color:var(--muted)}.grid{display:grid;gap:16px}.stats{grid-template-columns:repeat(4,minmax(0,1fr));margin-bottom:22px}.stat,.panel{border:1px solid var(--line);border-radius:12px;background:linear-gradient(145deg,rgba(255,255,255,.05),rgba(255,122,26,.025));box-shadow:0 18px 50px rgba(0,0,0,.12)}.stat{padding:18px}.stat span{color:var(--muted);font-size:12px}.stat strong{display:block;margin-top:6px;font-size:28px}.two-col{grid-template-columns:repeat(2,minmax(0,1fr))}.panel{overflow:hidden}.panel-head{display:flex;justify-content:space-between;gap:14px;padding:16px 18px;border-bottom:1px solid var(--line)}.panel-head h2{margin:0;font-size:16px}.panel-body{padding:18px}.table-wrap{overflow:auto}table{width:100%;border-collapse:collapse}th,td{padding:12px 14px;border-bottom:1px solid var(--line);text-align:left;white-space:nowrap}th{color:var(--muted);font-size:10px;text-transform:uppercase;letter-spacing:.08em}tbody tr:hover{background:rgba(255,255,255,.025)}.muted{color:var(--muted)}.badge{display:inline-flex;padding:4px 7px;border-radius:999px;color:#b9f9ec;background:rgba(25,199,181,.1);font-size:10px;font-weight:700;text-transform:capitalize}.badge.new{color:#ffd4b5;background:rgba(255,122,26,.13)}.badge.closed{color:#cbd5e1;background:rgba(148,163,184,.12)}.btn{display:inline-flex;align-items:center;justify-content:center;min-height:36px;padding:8px 13px;border:0;border-radius:8px;color:#fff;background:linear-gradient(115deg,#ff4c00 0%,#bf4911 40%,#12a5ba 72%,#11a5bd 100%);box-shadow:0 10px 28px rgba(8,124,141,.22);font-weight:600;cursor:pointer}.btn.secondary{border:1px solid var(--line);color:var(--ink);background:rgba(255,255,255,.05);box-shadow:none}.filters{display:flex;flex-wrap:wrap;gap:9px;margin-bottom:16px}.field{display:grid;gap:6px}.field label{color:var(--muted);font-size:11px;font-weight:700}.input{min-height:40px;padding:9px 11px;border:1px solid var(--line);border-radius:8px;color:var(--ink);background:#09151a;outline:none}.input:focus{border-color:var(--orange)}textarea.input{min-height:140px;resize:vertical}.filters .input{min-width:210px}.alert{margin-bottom:18px;padding:12px 14px;border:1px solid rgba(25,199,181,.3);border-radius:8px;color:#b9f9ec;background:rgba(25,199,181,.08)}.error{color:#fda4af;font-size:12px}.detail-list{display:grid;grid-template-columns:150px 1fr;gap:12px;padding:0;margin:0}.detail-list dt{color:var(--muted)}.detail-list dd{margin:0;word-break:break-word}.message-copy{white-space:pre-wrap;line-height:1.75}.pagination{padding:16px}.empty{padding:34px;color:var(--muted);text-align:center}@media(max-width:950px){.admin-shell{grid-template-columns:1fr}.sidebar{position:relative;height:auto}.nav{grid-template-columns:repeat(4,auto);overflow:auto}.sidebar-foot{position:static;margin-top:16px}.content{padding:20px}.stats,.two-col{grid-template-columns:repeat(2,minmax(0,1fr))}}@media(max-width:600px){.content{padding:16px}.stats,.two-col{grid-template-columns:1fr}.page-head{align-items:flex-start;flex-direction:column}.detail-list{grid-template-columns:1fr;gap:3px}}
        .brand{gap:0}
    </style>
</head>
<body>
<div class="admin-shell">
    <aside class="sidebar">
        <a class="brand" href="{{ route('admin.dashboard') }}"><img src="{{ asset('images/branding/tradeyatra-icon-v2.png') }}" alt=""><span>TradeYatra</span><span class="admin-label">Admin</span></a>
        <nav class="nav" aria-label="Admin navigation">
            <a class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">Overview</a>
            <a class="{{ request()->routeIs('admin.analytics') ? 'active' : '' }}" href="{{ route('admin.analytics') }}">Analytics</a>
            <a class="{{ request()->routeIs('admin.users.*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}">Users</a>
            <a class="{{ request()->routeIs('admin.contacts.*') ? 'active' : '' }}" href="{{ route('admin.contacts.index') }}">Contact inbox</a>
            @php($supportUnread = \App\Models\SupportTicket::query()->sum('admin_unread_count'))
            <a class="{{ request()->routeIs('admin.support.*') ? 'active' : '' }}" href="{{ route('admin.support.index') }}">Support inbox @if($supportUnread)<span class="badge new">{{ min($supportUnread,99) }}</span>@endif</a>
            @php($pendingContributions = \App\Models\SupportContribution::query()->where('status', 'pending')->count())
            <a class="{{ request()->routeIs('admin.contributions.*') ? 'active' : '' }}" href="{{ route('admin.contributions.index') }}">Contributions @if($pendingContributions)<span class="badge new">{{ min($pendingContributions,99) }}</span>@endif</a>
            <a class="{{ request()->routeIs('admin.profile.*') ? 'active' : '' }}" href="{{ route('admin.profile.edit') }}">Profile</a>
        </nav>
        <div class="sidebar-foot"><small>{{ auth('admin')->user()->email }}</small><form method="POST" action="{{ route('admin.logout') }}">@csrf<button class="link-button" type="submit">Log out</button></form></div>
    </aside>
    <main class="content">
        @if(session('success'))<div class="alert" role="status">{{ session('success') }}</div>@endif
        @yield('content')
    </main>
</div>
</body>
</html>
