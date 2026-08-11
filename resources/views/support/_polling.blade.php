<script>
(() => {
    if (window.tradeYatraSupportPolling) return;
    window.tradeYatraSupportPolling = true;

    const refreshConversation = async () => {
        if (document.hidden || !navigator.onLine) return;
        const current = document.querySelector('.support-layout, .ticket-layout');
        if (!current) return;

        const reply = current.querySelector('textarea[name="message"]');
        if ((reply && reply.value.trim()) || current.contains(document.activeElement)) return;

        try {
            const response = await fetch(location.href, {
                headers: {'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html'},
                cache: 'no-store',
            });
            if (!response.ok) return;
            const incomingDocument = new DOMParser().parseFromString(await response.text(), 'text/html');
            const incoming = incomingDocument.querySelector('.support-layout, .ticket-layout');
            const latest = document.querySelector('.support-layout, .ticket-layout');
            if (incoming && latest && incoming.innerHTML !== latest.innerHTML) {
                latest.replaceWith(incoming);
            }
        } catch (_) {
            // Keep the current conversation usable when a background refresh fails.
        }
    };

    window.setInterval(refreshConversation, 12000);
    document.addEventListener('visibilitychange', () => {
        if (!document.hidden) refreshConversation();
    });
})();
</script>
