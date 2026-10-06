(() => {
    const panel = document.getElementById('login-verification');
    if (!panel) return;
    const started = performance.now();
    const serverTime = Number(panel.dataset.serverTime);
    const locked = panel.dataset.locked === 'true';
    const delivered = panel.dataset.delivered === 'true';
    const remaining = timestamp => Math.max(0, Math.ceil(Number(timestamp) - serverTime - (performance.now() - started) / 1000));
    function update() {
        const expiry = remaining(panel.dataset.expires);
        const resend = remaining(panel.dataset.resend);
        const label = expiry ? `Code expires in ${Math.floor(expiry / 60)}:${String(expiry % 60).padStart(2, '0')}.` : 'Code expired. Request a new code.';
        const expiryElement = document.getElementById('expiry');
        // Announce expiry, without making a screen reader read every second.
        expiryElement.setAttribute('aria-live', expiry ? 'off' : 'polite');
        expiryElement.textContent = delivered ? label : 'No active code. Request a new email.';
        document.getElementById('resend-countdown').textContent = locked ? 'Return to login to start again.' : resend ? `Resend available in ${resend}s.` : 'You can now request a new code.';
        if (!panel.dataset.busy) {
            document.getElementById('verify-button').disabled = locked || !delivered || !expiry;
            document.getElementById('resend-button').disabled = locked || !!resend;
        }
    }
    const input = document.getElementById('code');
    input.addEventListener('paste', event => {
        const pasted = event.clipboardData.getData('text').trim();
        if (/^[0-9]{6}$/.test(pasted)) { event.preventDefault(); input.value = pasted; }
    });
    panel.querySelectorAll('form').forEach(form => form.addEventListener('submit', () => {
        panel.dataset.busy = 'true';
        form.setAttribute('aria-busy', 'true');
        form.querySelector('button').textContent = form.dataset.loading;
        panel.querySelectorAll('button').forEach(button => { button.disabled = true; });
    }));
    window.addEventListener('pageshow', event => { if (event.persisted) window.location.reload(); });
    update();
    setInterval(update, 1000);
})();
