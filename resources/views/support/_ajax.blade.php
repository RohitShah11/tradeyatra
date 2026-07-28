<style>
    #supportApp{position:relative;transition:opacity .15s ease,transform .15s ease}#supportApp.support-loading{opacity:.46;transform:translateY(3px);pointer-events:none}#supportAjaxLoader{position:fixed;z-index:85;top:82px;left:50%;display:flex;align-items:center;gap:10px;padding:10px 15px;border:1px solid color-mix(in srgb,var(--accent) 35%,var(--line));border-radius:999px;color:var(--ink);background:color-mix(in srgb,var(--panel) 95%,transparent);box-shadow:0 14px 38px rgba(0,0,0,.25);opacity:0;visibility:hidden;transform:translate(-50%,-7px);transition:.15s;pointer-events:none}#supportAjaxLoader.active{opacity:1;visibility:visible;transform:translate(-50%,0)}.support-ajax-spinner{width:17px;height:17px;border:2px solid color-mix(in srgb,var(--accent) 22%,transparent);border-top-color:var(--accent);border-right-color:var(--accent-2);border-radius:50%;animation:support-spin .65s linear infinite}@keyframes support-spin{to{transform:rotate(360deg)}}
</style>
<div id="supportAjaxLoader" role="status" aria-live="polite"><span class="support-ajax-spinner"></span><strong>Updating support…</strong></div>
<script>
(() => {
    window.supportAjaxReady = true;

    const loader = document.getElementById('supportAjaxLoader');
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;

    const setLoading = (active) => {
        document.getElementById('supportApp')?.classList.toggle('support-loading', active);
        loader?.classList.toggle('active', active);
    };

    const render = (html, url, push) => {
        const documentCopy = new DOMParser().parseFromString(html, 'text/html');
        const incoming = documentCopy.getElementById('supportApp');
        if (!incoming) throw new Error('The support response was incomplete.');
        document.getElementById('supportApp').replaceWith(incoming);
        const title = incoming.dataset.pageTitle;
        const subtitle = incoming.dataset.pageSubtitle;
        if (title) document.querySelector('.topbar h1').textContent = title;
        if (subtitle) document.querySelector('.topbar .muted').textContent = subtitle;
        document.title = documentCopy.title || document.title;
        if (push) history.pushState({support:true}, '', url);
        window.scrollTo({top:0, behavior:'smooth'});
    };

    const request = async (url, options = {}, push = true) => {
        setLoading(true);
        try {
            const response = await fetch(url, {
                ...options,
                headers: {'X-Requested-With':'XMLHttpRequest', 'Accept':'text/html', ...(options.headers || {})},
            });
            const html = await response.text();
            if (!response.ok) throw new Error('Unable to update support right now.');
            render(html, response.url || url, push);
        } catch (error) {
            const app = document.getElementById('supportApp');
            app?.insertAdjacentHTML('afterbegin', '<div class="alert error" role="alert">Could not update support. Please try again.</div>');
        } finally {
            setLoading(false);
        }
    };

    document.addEventListener('click', (event) => {
        const link = event.target.closest('#supportApp a[href]');
        if (!link || link.origin !== location.origin || !link.pathname.includes('/support')) return;
        event.preventDefault();
        request(link.href);
    }, { signal: window.tradeYatraNavigationSignal });

    document.addEventListener('submit', (event) => {
        const form = event.target.closest('#supportApp form');
        if (!form) return;
        event.preventDefault();
        const data = new FormData(form);
        request(form.action, {method:form.method || 'POST', body:data, headers:csrf ? {'X-CSRF-TOKEN':csrf} : {}});
    }, { signal: window.tradeYatraNavigationSignal });

    window.addEventListener('popstate', () => {
        if (location.pathname.includes('/support')) request(location.href, {}, false);
    }, { signal: window.tradeYatraNavigationSignal });
})();
</script>
