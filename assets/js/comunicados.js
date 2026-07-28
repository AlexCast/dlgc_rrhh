document.addEventListener('DOMContentLoaded', () => {
    const openSidebarBtn = document.getElementById('open-sidebar');
    const closeSidebarBtn = document.getElementById('close-sidebar');
    const sidebar = document.getElementById('sidebar');

    if (openSidebarBtn && closeSidebarBtn && sidebar) {
        openSidebarBtn.addEventListener('click', () => {
            sidebar.classList.add('active');
            openSidebarBtn.setAttribute('aria-expanded', 'true');
        });

        closeSidebarBtn.addEventListener('click', () => {
            sidebar.classList.remove('active');
            openSidebarBtn.setAttribute('aria-expanded', 'false');
        });

        document.addEventListener('click', (event) => {
            const isClickInsideSidebar = sidebar.contains(event.target);
            const isClickOnOpenBtn = openSidebarBtn.contains(event.target);

            if (!isClickInsideSidebar && !isClickOnOpenBtn && sidebar.classList.contains('active')) {
                sidebar.classList.remove('active');
                openSidebarBtn.setAttribute('aria-expanded', 'false');
            }
        });
    }

    const lista = document.querySelector('.comunicados-list');
    if (!lista) {
        return;
    }

    const csrfToken = lista.dataset.csrfToken || '';
    const marcarVistoUrl = '/dlgc_rrhh/src/comunicados/marcar_visto.php';

    function actualizarUiVisto(articulo) {
        articulo.classList.remove('comunicado-nuevo');
        articulo.classList.add('comunicado-visto');

        const nuevoBadge = articulo.querySelector('.comunicado-nuevo-badge');
        if (nuevoBadge) {
            nuevoBadge.remove();
        }

        const footer = articulo.querySelector('.comunicado-footer');
        if (footer && !articulo.querySelector('.visto-badge')) {
            const badge = document.createElement('span');
            badge.className = 'visto-badge';
            badge.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg> Leído';
            footer.appendChild(badge);
        }
    }

    function marcarComoVisto(articulo, idComunicado) {
        if (articulo.dataset.marcandoVisto === '1' || articulo.classList.contains('comunicado-visto')) {
            return;
        }
        articulo.dataset.marcandoVisto = '1';

        fetch(marcarVistoUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: 'csrf_token=' + encodeURIComponent(csrfToken) + '&id_comunicado=' + encodeURIComponent(idComunicado)
        })
        .then(response => response.json())
        .then(data => {
            if (data.ok) {
                actualizarUiVisto(articulo);
            }
        })
        .catch(() => {
            // No interrumpir la experiencia por fallo silencioso.
        })
        .finally(() => {
            articulo.dataset.marcandoVisto = '0';
        });
    }

    lista.addEventListener('click', (event) => {
        const boton = event.target.closest('.btn-ver-mas');
        if (!boton) {
            return;
        }

        event.preventDefault();
        const articulo = boton.closest('.comunicado-card');
        const parrafo = articulo ? articulo.querySelector('.comunicado-content') : null;
        const idComunicado = articulo ? articulo.dataset.idComunicado : null;

        if (!articulo || !parrafo || !idComunicado) {
            return;
        }

        const textoCompleto = parrafo.dataset.fullText || parrafo.textContent;
        parrafo.innerHTML = textoCompleto.replace(/\n/g, '<br>');
        parrafo.classList.remove('comunicado-truncado');
        boton.remove();

        marcarComoVisto(articulo, idComunicado);
    });

    // Comunicados cortos (sin botón "Ver más") se consideran leídos al cargar la lista,
    // porque su contenido completo es visible sin interacción adicional.
    const comunicadosCortos = lista.querySelectorAll('.comunicado-card.comunicado-nuevo:not(:has(.btn-ver-mas))');
    comunicadosCortos.forEach((articulo, index) => {
        const idComunicado = articulo.dataset.idComunicado;
        if (!idComunicado) {
            return;
        }
        setTimeout(() => {
            marcarComoVisto(articulo, idComunicado);
        }, 400 + (index * 150));
    });
});
