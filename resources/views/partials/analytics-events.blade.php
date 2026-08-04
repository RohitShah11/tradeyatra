<script>
document.addEventListener('DOMContentLoaded', () => {
    const endpoint = @json(route('analytics.events.store'));
    const csrfToken = @json(csrf_token());

    const recordEvent = (event, metadata = {}) => {
        const payload = JSON.stringify({ event, metadata });

        return fetch(endpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: payload,
            credentials: 'same-origin',
            keepalive: true,
        }).catch(() => null);
    };

    document.querySelectorAll('[data-analytics-event]').forEach((element) => {
        element.addEventListener('click', () => {
            recordEvent(element.dataset.analyticsEvent, {
                cta: element.textContent.trim().slice(0, 80),
                placement: element.dataset.analyticsPlacement || 'unknown',
                broker: element.dataset.analyticsBroker || undefined,
            });
        });
    });

    document.querySelectorAll('[data-analytics-form]').forEach((form) => {
        let started = false;
        form.addEventListener('input', () => {
            if (started) return;
            started = true;
            recordEvent('registration_form_started', {
                placement: form.dataset.analyticsForm,
            });
        }, { once: true });
    });
});
</script>
