document.addEventListener('DOMContentLoaded', () => {
    // Pestañas
    const tabs = document.querySelectorAll('.codes-tab');
    const panels = document.querySelectorAll('.codes-panel');

    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            if (tab.classList.contains('active')) return;

            const targetId = tab.getAttribute('aria-controls');

            tabs.forEach(t => {
                const isActive = t.getAttribute('aria-controls') === targetId;
                t.classList.toggle('active', isActive);
                t.setAttribute('aria-selected', isActive ? 'true' : 'false');
            });

            panels.forEach(p => {
                const isActive = p.id === targetId;
                p.classList.toggle('active', isActive);
                p.setAttribute('aria-hidden', isActive ? 'false' : 'true');
            });
        });
    });

    // Copiar al portapapeles
    const toast = document.getElementById('codes-toast');
    const displays = document.querySelectorAll('.codes-display[data-copy-target]');

    function showToast(message) {
        if (!toast) return;
        toast.textContent = message;
        toast.classList.add('show');
        setTimeout(() => {
            toast.classList.remove('show');
        }, 2000);
    }

    displays.forEach(display => {
        const targetId = display.getAttribute('data-copy-target');
        const valueEl = document.getElementById(targetId);
        if (!valueEl) return;

        display.style.cursor = 'pointer';
        display.setAttribute('title', 'Haz clic para copiar');

        display.addEventListener('click', async () => {
            const codigo = valueEl.textContent.trim();
            if (!codigo || codigo.toLowerCase().includes('sin código')) return;

            try {
                await navigator.clipboard.writeText(codigo);
                showToast('Código copiado');
            } catch (err) {
                const range = document.createRange();
                range.selectNode(valueEl);
                window.getSelection().removeAllRanges();
                window.getSelection().addRange(range);
                showToast('Código seleccionado');
            }
        });
    });
});
