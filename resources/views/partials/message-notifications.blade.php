<style>.message-toast{position:fixed;z-index:110;left:24px;bottom:142px;max-width:300px;padding:12px 14px;border:1px solid color-mix(in srgb,#1687e8 45%,var(--line));border-radius:12px;color:var(--ink);background:color-mix(in srgb,var(--panel) 96%,transparent);box-shadow:0 18px 48px rgba(0,0,0,.28);opacity:0;visibility:hidden;transform:translateY(8px);transition:.2s;pointer-events:none}.message-toast.show{opacity:1;visibility:visible;transform:translateY(0)}.message-toast strong,.message-toast span{display:block}.message-toast span{margin-top:2px;color:var(--muted);font-size:10px}@media(max-width:680px){.message-toast{left:16px;right:16px;bottom:78px;max-width:none}}</style>
<div class="message-toast" id="messageUnreadToast" role="status" aria-live="polite"><strong>New message from TradeYatra</strong><span>Open chat to read the reply.</span></div>
<script>
(() => {
    if (window.tradeYatraMessageNotifications) return;
    window.tradeYatraMessageNotifications = true;

    const endpoint = @json(route('messages.unread-count'));
    const nav = document.querySelector('[data-message-nav]');
    const launcher = document.querySelector('[data-chat-open]');
    const toast = document.getElementById('messageUnreadToast');
    let previousCount = Number(@json((int) ($chatUnread ?? 0)));
    let toastTimer;

    const badge = (container, count) => {
        if (!container) return;
        let counter = container.querySelector('[data-message-count]');
        if (count > 0) {
            if (!counter) {
                counter = document.createElement('span');
                counter.className = 'nav-count';
                counter.dataset.messageCount = '';
                container.querySelector('.nav-arrow')?.remove();
                container.appendChild(counter);
            }
            counter.textContent = Math.min(count, 99);
        } else {
            counter?.remove();
            if (container === nav && !container.querySelector('.nav-arrow')) {
                const arrow = document.createElement('span'); arrow.className = 'nav-arrow'; arrow.textContent = '›'; container.appendChild(arrow);
            }
        }
    };

    const refresh = async () => {
        if (document.hidden) return;
        try {
            const response = await fetch(endpoint, {headers:{'Accept':'application/json','X-Requested-With':'XMLHttpRequest'},cache:'no-store'});
            if (!response.ok) return;
            const count = Number((await response.json()).count || 0);
            badge(nav, count); badge(launcher, count);
            if (count > previousCount) {
                toast?.classList.add('show');
                clearTimeout(toastTimer);
                toastTimer = setTimeout(() => toast?.classList.remove('show'), 5000);
            }
            previousCount = count;
        } catch (_) {}
    };

    window.setInterval(refresh, 15000);
    document.addEventListener('visibilitychange', () => { if (!document.hidden) refresh(); });
})();
</script>
