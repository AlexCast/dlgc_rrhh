// Módulo Documentación (31): registro y listado de documentos del empleado en sesión.
// Solo VER e INSERTAR: el frontend no ofrece edición ni eliminación.
(function () {
    document.addEventListener('DOMContentLoaded', () => {
        const modal = document.getElementById('doc-modal');
        const btnNuevo = document.getElementById('doc-btn-nuevo');
        const btnCerrar = document.getElementById('doc-modal-cerrar');
        const btnCancelar = document.getElementById('doc-modal-cancelar');
        const form = document.getElementById('doc-form');
        const alertas = document.getElementById('doc-alertas');

        const csrf = document.body.dataset.csrf || '';

        if (!modal || !btnNuevo || !form) {
            return;
        }

        btnNuevo.addEventListener('click', abrirModal);
        [btnCerrar, btnCancelar].forEach((btn) => {
            if (btn) btn.addEventListener('click', cerrarModal);
        });

        modal.addEventListener('click', (event) => {
            if (event.target === modal) cerrarModal();
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && !modal.hidden) cerrarModal();
        });

        form.addEventListener('submit', (event) => {
            event.preventDefault();
            guardarDocumento();
        });

        function abrirModal() {
            form.reset();
            modal.hidden = false;
            const titulo = document.getElementById('doc-titulo');
            if (titulo) titulo.focus();
        }

        function cerrarModal() {
            modal.hidden = true;
        }

        function mostrarAlerta(mensaje, ok) {
            if (!alertas) return;
            alertas.hidden = false;
            const div = document.createElement('div');
            div.className = 'doc-alerta ' + (ok ? 'doc-alerta-ok' : 'doc-alerta-error');
            div.textContent = mensaje;
            alertas.appendChild(div);
            window.setTimeout(() => {
                div.remove();
                if (alertas.children.length === 0) alertas.hidden = true;
            }, 6000);
        }

        function guardarDocumento() {
            const btnGuardar = document.getElementById('doc-btn-guardar');
            const archivoInput = document.getElementById('doc-archivo');

            if (!archivoInput || !archivoInput.files || archivoInput.files.length === 0) {
                mostrarAlerta('Adjunta el archivo del documento (JPG, PNG, WebP o PDF, máximo 10 MB).', false);
                return;
            }

            const datos = new FormData(form);
            if (csrf) datos.set('csrf_token', csrf);
            if (btnGuardar) btnGuardar.disabled = true;

            fetch('/app/doc_guardar.php', { method: 'POST', body: datos, credentials: 'same-origin' })
                .then((r) => r.json().catch(() => ({ success: false, message: 'Respuesta inválida del servidor.' })))
                .then((data) => {
                    if (data.success) {
                        cerrarModal();
                        mostrarAlerta(data.message || 'Documento registrado correctamente.', true);
                        cargarLista();
                    } else {
                        mostrarAlerta(data.message || 'No se pudo registrar el documento.', false);
                        // Token de Turnstile de un solo uso: se reinicia para reintentar.
                        if (typeof window.turnstile !== 'undefined' && typeof window.turnstile.reset === 'function') {
                            window.turnstile.reset();
                        }
                    }
                })
                .catch(() => mostrarAlerta('No se pudo conectar con el servidor.', false))
                .finally(() => {
                    if (btnGuardar) btnGuardar.disabled = false;
                });
        }

        function cargarLista() {
            fetch('/app/doc_listar.php', { credentials: 'same-origin' })
                .then((r) => r.json().catch(() => ({ success: false, message: 'Respuesta inválida del servidor.' })))
                .then((data) => {
                    if (!data.success) {
                        mostrarAlerta(data.message || 'No se pudo cargar la lista.', false);
                        return;
                    }
                    renderizarLista(data.documentos || []);
                })
                .catch(() => mostrarAlerta('No se pudo conectar con el servidor.', false));
        }

        function renderizarLista(documentos) {
            const tbody = document.getElementById('doc-tbody');
            const contador = document.getElementById('doc-contador');
            if (!tbody) return;

            tbody.textContent = '';
            if (contador) contador.textContent = documentos.length + ' registro(s)';

            if (documentos.length === 0) {
                const tr = document.createElement('tr');
                const td = document.createElement('td');
                td.colSpan = 6;
                td.className = 'doc-empty';
                td.textContent = 'Aún no tienes documentos registrados.';
                tr.appendChild(td);
                tbody.appendChild(tr);
                return;
            }

            documentos.forEach((doc) => {
                const tr = document.createElement('tr');

                tr.appendChild(celda(doc.tipo_documento || 'N/A'));
                tr.appendChild(celda(doc.titulo || 'N/A'));
                tr.appendChild(celda(doc.id_contrato ? '#' + doc.id_contrato : 'Sin contrato'));

                const tdEstado = celda('');
                const badge = document.createElement('span');
                badge.className = 'doc-badge doc-badge-' + String(doc.estado || 'activo').toLowerCase();
                badge.textContent = doc.estado || 'ACTIVO';
                tdEstado.appendChild(badge);
                tr.appendChild(tdEstado);

                const tdArchivo = celda('');
                if (doc.archivo_actual) {
                    const cont = document.createElement('span');
                    cont.className = 'doc-archivo';
                    if (doc.id_version) {
                        const enlace = document.createElement('a');
                        enlace.className = 'doc-archivo-nombre doc-archivo-enlace';
                        enlace.href = '/app/gestor_descargar.php?id_version=' + encodeURIComponent(doc.id_version);
                        enlace.textContent = doc.archivo_actual;
                        enlace.title = doc.archivo_actual;
                        cont.appendChild(enlace);
                    } else {
                        const nombre = document.createElement('span');
                        nombre.className = 'doc-archivo-nombre';
                        nombre.textContent = doc.archivo_actual;
                        nombre.title = doc.archivo_actual;
                        cont.appendChild(nombre);
                    }
                    if (doc.numero_version != null) {
                        const bv = document.createElement('span');
                        bv.className = 'doc-badge doc-badge-vers';
                        bv.textContent = 'v' + doc.numero_version;
                        cont.appendChild(bv);
                    }
                    tdArchivo.appendChild(cont);
                } else {
                    const s = document.createElement('span');
                    s.className = 'text-muted';
                    s.textContent = 'Sin archivo';
                    tdArchivo.appendChild(s);
                }
                tr.appendChild(tdArchivo);

                tr.appendChild(celda(formatearFecha(doc.documento_fecha)));

                tbody.appendChild(tr);
            });
        }

        function celda(texto) {
            const td = document.createElement('td');
            td.textContent = texto;
            return td;
        }

        function formatearFecha(iso) {
            if (!iso) return 'N/A';
            const dt = new Date(String(iso).replace(' ', 'T'));
            if (Number.isNaN(dt.getTime())) return String(iso);
            return dt.toLocaleDateString('es-CO', { day: '2-digit', month: '2-digit', year: 'numeric' }) +
                ' ' +
                dt.toLocaleTimeString('es-CO', { hour: '2-digit', minute: '2-digit', hour12: false });
        }
    });
})();
