<script>
(() => {
    if (window.tradeYatraPresenceTracker) return;
    window.tradeYatraPresenceTracker = true;

    const endpoint = @json(route('activity.heartbeat'));
    const routeName = @json(request()->route()?->getName());
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
    const storageKey = 'tradeyatra-activity-session';
    let clientSession = sessionStorage.getItem(storageKey);
    if (!clientSession) {
        clientSession = crypto.randomUUID();
        sessionStorage.setItem(storageKey, clientSession);
    }
    let lastInteraction = Date.now();
    let lastHeartbeat = Date.now();
    let sending = false;

    const markActive = () => { lastInteraction = Date.now(); };
    ['pointerdown', 'keydown', 'scroll', 'touchstart'].forEach(eventName => {
        document.addEventListener(eventName, markActive, {passive:true});
    });

    const sendHeartbeat = async (keepalive = false) => {
        if (sending && !keepalive) return;
        const now = Date.now();
        const elapsed = Math.min(60, Math.max(0, Math.round((now - lastHeartbeat) / 1000)));
        lastHeartbeat = now;
        sending = true;
        try {
            await fetch(endpoint, {
                method: 'POST',
                keepalive,
                headers: {'Accept':'application/json','Content-Type':'application/json','X-CSRF-TOKEN':csrf,'X-Requested-With':'XMLHttpRequest'},
                body: JSON.stringify({
                    client_session: clientSession,
                    route: routeName,
                    path: location.pathname,
                    visible: document.visibilityState === 'visible',
                    idle: now - lastInteraction >= 300000,
                    elapsed,
                }),
            });
        } catch (_) {
            // Presence tracking must never interrupt the user's work.
        } finally {
            sending = false;
        }
    };

    sendHeartbeat();
    window.setInterval(sendHeartbeat, 30000);
    document.addEventListener('visibilitychange', () => sendHeartbeat(true));
    window.addEventListener('pagehide', () => sendHeartbeat(true));
})();
</script>
