if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => navigator.serviceWorker.register('/sw.js').catch(() => {}));
}

// Subida directa de fotos: al elegir archivos se envían sin pulsar Guardar.
document.addEventListener('change', (event) => {
    const input = event.target.closest('[data-auto-upload-input]');
    if (!input || input.disabled || input.files.length === 0) {
        return;
    }
    const form = input.closest('form[data-auto-upload]');
    if (!form) {
        return;
    }
    input.disabled = true;
    const status = form.querySelector('[data-upload-status]');
    if (status) {
        status.hidden = false;
    }
    form.submit();
});
