document.addEventListener('DOMContentLoaded', () => {
    // Sidebar mobile
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

    // Pestañas
    const tabs = document.querySelectorAll('.sst-tab');
    const panels = document.querySelectorAll('.sst-tab-panel');

    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            const target = tab.dataset.tab;

            tabs.forEach(t => {
                t.classList.remove('active');
                t.setAttribute('aria-selected', 'false');
            });
            panels.forEach(p => {
                p.classList.remove('active');
                p.setAttribute('hidden', '');
            });

            tab.classList.add('active');
            tab.setAttribute('aria-selected', 'true');
            const panel = document.getElementById('tab-' + target);
            if (panel) {
                panel.classList.add('active');
                panel.removeAttribute('hidden');
            }
        });
    });

    // Formulario de queja
    const form = document.getElementById('sst-queja-form');
    const feedback = document.getElementById('sst-form-feedback');
    const btnEnviar = document.getElementById('btn-enviar-queja');
    const btnCancelarEdicion = document.getElementById('btn-cancelar-edicion');
    const inputIdQueja = document.getElementById('id_queja');

    if (!form) {
        return;
    }

    const csrfToken = form.dataset.csrfToken || '';
    const guardarUrl = '/dlgc_rrhh/app/sst_guardar_queja.php';
    const actualizarUrl = '/dlgc_rrhh/app/sst_actualizar_queja.php';
    const cancelarUrl = '/dlgc_rrhh/app/sst_cancelar_queja.php';

    function mostrarFeedback(mensaje, tipo) {
        feedback.textContent = mensaje;
        feedback.className = 'sst-feedback ' + tipo;
    }

    function resetFormulario() {
        form.reset();
        inputIdQueja.value = '';
        btnEnviar.textContent = 'Enviar';
        btnCancelarEdicion.style.display = 'none';
    }

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        mostrarFeedback('', '');

        const formData = new FormData(form);
        const idQueja = inputIdQueja.value.trim();
        const url = idQueja ? actualizarUrl : guardarUrl;

        btnEnviar.disabled = true;
        btnEnviar.textContent = idQueja ? 'Actualizando...' : 'Enviando...';

        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                mostrarFeedback(data.message, 'success');
                setTimeout(() => {
                    window.location.reload();
                }, 800);
            } else {
                mostrarFeedback(data.message || 'Error al procesar la solicitud.', 'error');
                btnEnviar.disabled = false;
                btnEnviar.textContent = idQueja ? 'Actualizar' : 'Enviar';
            }
        } catch (error) {
            mostrarFeedback('Error de conexión. Intenta de nuevo.', 'error');
            btnEnviar.disabled = false;
            btnEnviar.textContent = idQueja ? 'Actualizar' : 'Enviar';
        }
    });

    btnCancelarEdicion.addEventListener('click', resetFormulario);

    // Editar queja
    document.querySelectorAll('.btn-edit-queja').forEach(boton => {
        boton.addEventListener('click', () => {
            const card = boton.closest('.sst-queja-card');
            if (!card) {
                return;
            }

            const idQueja = card.dataset.id;
            const tipo = card.querySelector('.sst-queja-type')?.textContent?.trim() || '';
            const asunto = card.querySelector('.sst-queja-subject')?.textContent?.trim() || '';
            const descripcion = card.querySelector('.sst-queja-desc')?.innerText?.trim() || '';

            inputIdQueja.value = idQueja;
            document.getElementById('tipo_peticion').value = tipo;
            document.getElementById('asunto').value = asunto;
            document.getElementById('descripcion').value = descripcion;

            btnEnviar.textContent = 'Actualizar';
            btnCancelarEdicion.style.display = 'inline-block';

            // Abrir pestaña del buzón y hacer scroll
            const tabBuzon = document.querySelector('.sst-tab[data-tab="buzon"]');
            if (tabBuzon) {
                tabBuzon.click();
            }
            form.scrollIntoView({ behavior: 'smooth' });
        });
    });

    // Cancelar queja
    document.querySelectorAll('.btn-cancel-queja').forEach(boton => {
        boton.addEventListener('click', async () => {
            if (!confirm('¿Estás seguro de cancelar esta solicitud?')) {
                return;
            }

            const idQueja = boton.dataset.id;
            boton.disabled = true;

            try {
                const response = await fetch(cancelarUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: 'csrf_token=' + encodeURIComponent(csrfToken) + '&id_queja=' + encodeURIComponent(idQueja)
                });

                const data = await response.json();

                if (data.success) {
                    window.location.reload();
                } else {
                    alert(data.message || 'No se pudo cancelar la solicitud.');
                    boton.disabled = false;
                }
            } catch (error) {
                alert('Error de conexión. Intenta de nuevo.');
                boton.disabled = false;
            }
        });
    });
});
