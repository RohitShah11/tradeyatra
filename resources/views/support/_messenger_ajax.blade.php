<script>
(() => {
    if (window.tradeYatraMessengerAjax) return;
    window.tradeYatraMessengerAjax = true;

    const conversationSelector = '.ticket-layout, .support-layout';

    const scrollToLatest = () => {
        const list = document.querySelector('.chat-list');
        if (list) list.scrollTop = list.scrollHeight;
        const scroller = document.querySelector('.ticket-layout');
        if (scroller) scroller.scrollTop = scroller.scrollHeight;
    };

    const showError = (form, message) => {
        form.querySelector('.chat-send-error')?.remove();
        const error = document.createElement('div');
        error.className = 'chat-send-error error';
        error.style.gridColumn = '1 / -1';
        error.textContent = message;
        form.appendChild(error);
    };

    document.addEventListener('keydown', (event) => {
        const input = event.target.closest('.chat-compose textarea[name="message"]');
        if (!input || event.key !== 'Enter' || event.shiftKey || event.isComposing) return;
        event.preventDefault();
        input.closest('form')?.requestSubmit();
    });

    document.addEventListener('input', (event) => {
        const input = event.target.closest('.chat-compose textarea[name="message"]');
        if (!input) return;
        input.style.height = 'auto';
        input.style.height = `${Math.min(input.scrollHeight, 120)}px`;
    });

    document.addEventListener('submit', async (event) => {
        const form = event.target.closest('.chat-compose');
        if (!form) return;
        event.preventDefault();

        const input = form.querySelector('textarea[name="message"]');
        const button = form.querySelector('button[type="submit"]');
        if (!input?.value.trim() || button?.disabled) return;

        const originalMessage = input.value;
        button.disabled = true;
        button.setAttribute('aria-busy', 'true');
        form.querySelector('.chat-send-error')?.remove();

        try {
            const response = await fetch(form.action, {
                method: form.method || 'POST',
                body: new FormData(form),
                headers: {'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html'},
            });
            const html = await response.text();
            if (!response.ok) throw new Error('Your message could not be sent. Please try again.');

            const incomingDocument = new DOMParser().parseFromString(html, 'text/html');
            const incoming = incomingDocument.querySelector(conversationSelector);
            const incomingForm = incomingDocument.querySelector('.chat-compose');
            const current = document.querySelector(conversationSelector);
            if (!incoming || !current) throw new Error('The conversation could not be updated.');

            current.replaceWith(incoming);
            const currentForm = document.querySelector('.chat-compose');
            if (currentForm && incomingForm) currentForm.action = incomingForm.action;
            const currentInput = currentForm?.querySelector('textarea[name="message"]');
            if (currentInput) {
                currentInput.value = '';
                currentInput.style.height = 'auto';
                currentInput.focus();
            }
            scrollToLatest();
        } catch (error) {
            input.value = originalMessage;
            showError(form, error.message || 'Your message could not be sent. Please try again.');
        } finally {
            const currentButton = document.querySelector('.chat-compose button[type="submit"]');
            if (currentButton) {
                currentButton.disabled = false;
                currentButton.removeAttribute('aria-busy');
            }
        }
    });

    scrollToLatest();
})();
</script>
