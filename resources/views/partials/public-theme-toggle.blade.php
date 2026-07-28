<style>
    button, .btn, a.btn, .public-header-button { font-family:inherit; font-weight:600 !important; letter-spacing:normal; }
    .btn.primary, .public-header-button.primary { color:#fff !important; background:linear-gradient(115deg,#ff4c00 0%,#bf4911 40%,#12a5ba 72%,#11a5bd 100%) !important; box-shadow:0 10px 28px rgba(8,124,141,.22) !important; }
    .public-theme-toggle { width:42px; min-width:42px; height:42px; display:inline-grid; place-items:center; padding:0; border:1px solid rgba(255,255,255,.13); border-radius:10px; color:var(--text,var(--ink,#f7fbfc)); background:rgba(255,255,255,.06); cursor:pointer; }
    .public-theme-toggle:hover { border-color:rgba(24,199,255,.4); transform:translateY(-1px); }
    .public-theme-toggle svg { width:19px; height:19px; fill:none; stroke:currentColor; stroke-width:2; stroke-linecap:round; stroke-linejoin:round; }
    .public-theme-toggle .theme-sun { display:none; }
    html[data-public-theme="light"] .public-theme-toggle .theme-sun { display:block; }
    html[data-public-theme="light"] .public-theme-toggle .theme-moon { display:none; }
    .public-theme-toggle.floating { position:fixed; top:18px; right:18px; z-index:60; box-shadow:0 10px 30px rgba(0,0,0,.14); }
    html[data-public-theme="light"] {
        color-scheme:light;
        --bg:#f5f7fa; --bg-2:#eef3f6; --panel:#ffffff; --panel-2:#f7fafb;
        --text:#17232b; --ink:#17232b; --muted:#637680; --line:rgba(22,139,216,.2);
    }
    html[data-public-theme="light"] body { color:#17232b !important; background:radial-gradient(circle at 14% 4%,rgba(255,122,26,.13),transparent 35rem),radial-gradient(circle at 88% 8%,rgba(24,199,255,.1),transparent 30rem),linear-gradient(155deg,#fff,#f4f8fa 55%,#edf3f5) !important; }
    html[data-public-theme="light"] .nav { background:rgba(255,255,255,.86) !important; border-color:rgba(22,139,216,.15) !important; }
    html[data-public-theme="light"] .lead,
    html[data-public-theme="light"] .muted,
    html[data-public-theme="light"] .nav-links,
    html[data-public-theme="light"] .security-card p,
    html[data-public-theme="light"] .step p,
    html[data-public-theme="light"] .trouble p,
    html[data-public-theme="light"] .risk-card p,
    html[data-public-theme="light"] footer { color:#637680 !important; }
    html[data-public-theme="light"] .feature,
    html[data-public-theme="light"] .mini,
    html[data-public-theme="light"] .step,
    html[data-public-theme="light"] .faq-item,
    html[data-public-theme="light"] .card,
    html[data-public-theme="light"] .risk-card,
    html[data-public-theme="light"] .panel,
    html[data-public-theme="light"] .point,
    html[data-public-theme="light"] .toc { color:#17232b; background:rgba(255,255,255,.82) !important; box-shadow:0 12px 35px rgba(20,50,65,.06); }
    html[data-public-theme="light"] .story { color:#17232b; background:linear-gradient(145deg,rgba(255,122,26,.08),rgba(24,199,255,.05)),#f5f8fa !important; }
    html[data-public-theme="light"] .story p { color:#526872; }
    html[data-public-theme="light"] .terminal { color:#f7fbfc; }
    html[data-public-theme="light"] .terminal-head,
    html[data-public-theme="light"] .terminal small,
    html[data-public-theme="light"] .terminal .trade-card small { color:#9fb2ba !important; }
    html[data-public-theme="light"] .terminal .pair { color:#f7fbfc; }
    html[data-public-theme="light"] .check,
    html[data-public-theme="light"] .cta p,
    html[data-public-theme="light"] .contact-note,
    html[data-public-theme="light"] .notice p,
    html[data-public-theme="light"] .closing p { color:#526872 !important; }
    html[data-public-theme="light"] .warning { color:#7a4b00; background:rgba(251,191,36,.13); }
    html[data-public-theme="light"] .eyebrow { color:#9a3f0a; }
    html[data-public-theme="light"] .icon,
    html[data-public-theme="light"] .step:before,
    html[data-public-theme="light"] .toc strong,
    html[data-public-theme="light"] .risk-card h2 span,
    html[data-public-theme="light"] .contact-email { color:#a64208 !important; }
    html[data-public-theme="light"] main a:not(.btn) { color:#086f91; }
    html[data-public-theme="light"] .switch a { color:#a64208; }
    html[data-public-theme="light"] .contact-form { background:rgba(255,255,255,.82); }
    html[data-public-theme="light"] select,
    html[data-public-theme="light"] textarea { color:#17232b; background:#fff; }
    html[data-public-theme="light"] input { color:#17232b; background:#fff; }
    html[data-public-theme="light"] .public-theme-toggle { color:#17232b; border-color:rgba(22,139,216,.2); background:rgba(255,255,255,.75); }
</style>
<button class="public-theme-toggle {{ ($floating ?? false) ? 'floating' : '' }}" id="publicThemeToggle" type="button" aria-label="Switch to light mode" title="Switch color theme">
    <svg class="theme-moon" viewBox="0 0 24 24"><path d="M20.5 14.5A8.5 8.5 0 0 1 9.5 3.5 7 7 0 1 0 20.5 14.5Z"/></svg>
    <svg class="theme-sun" viewBox="0 0 24 24"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41"/></svg>
</button>
<script>
(() => {
    const root = document.documentElement;
    const button = document.getElementById('publicThemeToggle');
    const saved = localStorage.getItem('public-theme');
    const initial = saved || (window.matchMedia('(prefers-color-scheme: light)').matches ? 'light' : 'dark');

    function apply(theme) {
        root.dataset.publicTheme = theme;
        if (button) button.setAttribute('aria-label', theme === 'light' ? 'Switch to dark mode' : 'Switch to light mode');
    }

    apply(initial);
    button?.addEventListener('click', () => {
        const next = root.dataset.publicTheme === 'light' ? 'dark' : 'light';
        localStorage.setItem('public-theme', next);
        apply(next);
    });
})();
</script>
