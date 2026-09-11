if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => navigator.serviceWorker.register('/sw.js').catch(() => {}));
}

// Subida directa de fotos: al elegir archivos se envían sin pulsar Guardar.
// No se desactiva el campo de archivo: los campos desactivados no se envían
// y la subida llegaría vacía. Se evita el doble envío con una marca en el formulario.
document.addEventListener('change', (event) => {
    const input = event.target.closest('[data-auto-upload-input]');
    if (!input || !input.files || input.files.length === 0) {
        return;
    }
    const form = input.closest('form[data-auto-upload]');
    if (!form || form.dataset.uploading === '1') {
        return;
    }
    form.dataset.uploading = '1';
    const status = form.querySelector('[data-upload-status]');
    if (status) {
        status.hidden = false;
    }
    form.submit();
});
