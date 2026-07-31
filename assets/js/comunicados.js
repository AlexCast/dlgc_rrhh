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

    // Modal de lectura
    const modal = document.getElementById('cp-modal-comunicado');
    const modalTitle = document.getElementById('cp-modal-title');
    const modalCategory = document.getElementById('cp-modal-category');
    const modalAuthor = document.getElementById('cp-modal-author');
    const modalDate = document.getElementById('cp-modal-date');
    const modalContent = document.getElementById('cp-modal-content');
    const modalVistos = document.getElementById('cp-modal-vistos');

    let currentArticulo = null;

    function actualizarUiVisto(articulo) {
        articulo.classList.remove('comunicado-nuevo');
        articulo.classList.add('comunicado-visto');
        articulo.dataset.visto = '1';

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
        if (articulo.dataset.marcandoVisto === '1' || articulo.dataset.visto === '1') {
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

    function htmlDecode(input) {
        const decoder = document.createElement('textarea');
        decoder.innerHTML = input;
        return decoder.value;
    }

    function formatVistos(total) {
        const count = parseInt(total, 10) || 0;
        return count + ' visto' + (count !== 1 ? 's' : '');
    }

    function abrirModal(articulo) {
        if (!modal || !articulo) {
            return;
        }

        currentArticulo = articulo;

        const titulo = articulo.dataset.titulo || 'Comunicado';
        const categoria = articulo.dataset.categoria || 'Categoría';
        const categoriaClase = articulo.dataset.categoriaClase || 'is-general';
        const autor = articulo.dataset.autor || 'Administrador';
        const fecha = articulo.dataset.fechaCompleta || articulo.dataset.fecha || 'Fecha desconocida';
        const contenido = articulo.dataset.contenido || '';
        const vistos = articulo.dataset.vistos || '0';

        if (modalTitle) modalTitle.textContent = titulo;
        if (modalCategory) {
            modalCategory.textContent = categoria;
            modalCategory.className = 'cp-modal-category ' + categoriaClase;
        }
        if (modalAuthor) modalAuthor.textContent = autor;
        if (modalDate) modalDate.textContent = fecha;
        if (modalContent) modalContent.innerHTML = htmlDecode(contenido);
        if (modalVistos) modalVistos.textContent = formatVistos(vistos);

        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('modal-open');

        const idComunicado = articulo.dataset.idComunicado;
        if (idComunicado) {
            marcarComoVisto(articulo, idComunicado);
        }
    }

    function cerrarModal() {
        if (!modal) {
            return;
        }
        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('modal-open');
        currentArticulo = null;
    }

    if (modal) {
        modal.addEventListener('click', (event) => {
            if (event.target === modal || event.target.closest('[data-cp-close]')) {
                event.preventDefault();
                cerrarModal();
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && modal.classList.contains('is-open')) {
                cerrarModal();
            }
        });
    }

    lista.addEventListener('click', (event) => {
        const boton = event.target.closest('.btn-ver-mas');
        const articulo = event.target.closest('.comunicado-card');

        if (!articulo || articulo.classList.contains('comunicado-empty')) {
            return;
        }

        // Si se hace clic en un elemento interactivo que no es "Ver más", no abrir modal.
        const esInteractivo = event.target.closest('a, button, [role="button"]');
        if (esInteractivo && !boton) {
            return;
        }

        event.preventDefault();
        abrirModal(articulo);
    });

    lista.addEventListener('keydown', (event) => {
        if (event.key !== 'Enter' && event.key !== ' ') {
            return;
        }

        const articulo = event.target.closest('.comunicado-card');
        if (!articulo || articulo.classList.contains('comunicado-empty')) {
            return;
        }

        event.preventDefault();
        abrirModal(articulo);
    });

    // Comunicados cortos se consideran leídos al cargar la lista,
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
