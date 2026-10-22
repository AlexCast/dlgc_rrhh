document.addEventListener('DOMContentLoaded', () => {
    // Aviso flotante temporal
    document.querySelectorAll('.codes-flash[data-auto-dismiss]').forEach(flash => {
        const closeBtn = flash.querySelector('.codes-flash-close');

        const dismiss = () => {
            if (flash.classList.contains('is-hiding')) return;
            flash.classList.add('is-hiding');
            setTimeout(() => flash.remove(), 300);
        };

        if (closeBtn) {
            closeBtn.addEventListener('click', dismiss);
        }

        const delay = parseInt(flash.getAttribute('data-auto-dismiss'), 10);
        setTimeout(dismiss, Number.isNaN(delay) ? 4500 : delay);
    });

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

    // En escritorio ambos paneles están visibles a la vez
    const vistaAmplia = window.matchMedia('(min-width: 1200px)');

    const sincronizarPanelesVisibles = () => {
        panels.forEach(p => {
            const visible = vistaAmplia.matches || p.classList.contains('active');
            p.setAttribute('aria-hidden', visible ? 'false' : 'true');
        });
    };

    vistaAmplia.addEventListener('change', sincronizarPanelesVisibles);
    sincronizarPanelesVisibles();

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
