// Módulo Gestor de Archivos (30): tabla de archivos vigentes + historial de versiones
// + subida de nuevas versiones (FormData). Sin recargar la página.
(function () {
    document.addEventListener('DOMContentLoaded', () => {
        const body = document.body;
        const csrf = body.dataset.csrf || '';
        const puedeCrear = body.dataset.puedeCrear === '1';

        const tbody = document.getElementById('gestor-tbody');
        const contador = document.getElementById('gestor-contador');
        const alertas = document.getElementById('gestor-alertas');

        const modalVersiones = document.getElementById('gestor-modal-versiones');
        const modalSubir = document.getElementById('gestor-modal-subir');
        const listaVersiones = document.getElementById('gestor-versiones-list');
        const tituloVersiones = document.getElementById('gestor-versiones-titulo');

        const formSubir = document.getElementById('gestor-form-subir');
        const inputIdDocumento = document.getElementById('gestor-subir-id-documento');
        const inputArchivo = document.getElementById('gestor-subir-archivo');

        let documentoActual = null;

        // Delegación de eventos: aplica también a las filas renderizadas por JS.
        if (tbody) {
            tbody.addEventListener('click', (event) => {
                const btnVersiones = event.target.closest('.gestor-btn-versiones');
                if (btnVersiones) {
                    abrirHistorial(btnVersiones.dataset.idDocumento, btnVersiones.dataset.titulo || '');
                    return;
                }
                const btnSubir = event.target.closest('.gestor-btn-subir');
                if (btnSubir && puedeCrear) {
                    abrirFormularioSubir(btnSubir.dataset.idDocumento, btnSubir.dataset.titulo || '');
                }
            });
        }

        const btnActualizar = document.getElementById('gestor-btn-actualizar');
        if (btnActualizar) btnActualizar.addEventListener('click', cargarLista);

        document.querySelectorAll('.gestor-modal-cerrar').forEach((btn) => {
            btn.addEventListener('click', cerrarModales);
        });

        [modalVersiones, modalSubir].forEach((m) => {
            if (!m) return;
            m.addEventListener('click', (event) => {
                if (event.target === m) cerrarModales();
            });
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') cerrarModales();
        });

        const btnSubirDesdeHistorial = document.getElementById('gestor-versiones-subir');
        if (btnSubirDesdeHistorial && puedeCrear) {
            btnSubirDesdeHistorial.addEventListener('click', () => {
                if (documentoActual) abrirFormularioSubir(documentoActual.id, documentoActual.titulo);
            });
        }

        if (formSubir) {
            formSubir.addEventListener('submit', (event) => {
                event.preventDefault();
                subirArchivo();
            });
        }

        function cerrarModales() {
            if (modalVersiones) modalVersiones.hidden = true;
            if (modalSubir) modalSubir.hidden = true;
        }

        function mostrarAlerta(mensaje, ok) {
            if (!alertas) return;
            alertas.hidden = false;
            const div = document.createElement('div');
            div.className = 'gestor-alerta ' + (ok ? 'gestor-alerta-ok' : 'gestor-alerta-error');
            div.textContent = mensaje;
            alertas.appendChild(div);
            window.setTimeout(() => {
                div.remove();
                if (alertas.children.length === 0) alertas.hidden = true;
            }, 6000);
        }

        function abrirHistorial(idDocumento, titulo) {
            documentoActual = { id: idDocumento, titulo: titulo };
            if (tituloVersiones) tituloVersiones.textContent = 'Historial de versiones — ' + titulo;
            if (modalVersiones) modalVersiones.hidden = false;
            cargarHistorial(idDocumento);
        }

        function cargarHistorial(idDocumento) {
            if (listaVersiones) {
                listaVersiones.textContent = '';
                const p = document.createElement('p');
                p.className = 'gestor-cargando';
                p.textContent = 'Cargando versiones...';
                listaVersiones.appendChild(p);
            }

            fetch('/app/gestor_historial.php?id_documento=' + encodeURIComponent(idDocumento), { credentials: 'same-origin' })
                .then((r) => r.json().catch(() => ({ success: false, message: 'Respuesta inválida del servidor.' })))
                .then((data) => {
                    if (!data.success) {
                        mostrarAlerta(data.message || 'No se pudo cargar el historial.', false);
                        return;
                    }
                    renderizarHistorial(data.versiones || []);
                })
                .catch(() => mostrarAlerta('No se pudo conectar con el servidor.', false));
        }

        function renderizarHistorial(versiones) {
            if (!listaVersiones) return;
            listaVersiones.textContent = '';

            if (versiones.length === 0) {
                const p = document.createElement('p');
                p.className = 'gestor-cargando';
                p.textContent = 'Este documento todavía no tiene archivos.';
                listaVersiones.appendChild(p);
                return;
            }

            versiones.forEach((v) => {
                const item = document.createElement('div');
                item.className = 'gestor-version-item' + (esVigente(v) ? ' gestor-version-vigente' : '');

                const info = document.createElement('div');
                info.className = 'gestor-version-info';

                const nombre = document.createElement('span');
                nombre.className = 'gestor-version-nombre';
                nombre.textContent = v.nombre_original || 'archivo';
                nombre.title = v.nombre_original || '';

                const meta = document.createElement('span');
                meta.className = 'gestor-version-meta';
                const numVersion = v.numero_version != null ? 'Versión ' + v.numero_version : 'Pendiente de aprobación';
                let textoMeta = numVersion + ' · ' + formatearTamano(v.tamano_bytes) + ' · ' + formatearFecha(v.fec_insert);
                if (v.estado_aprobacion) textoMeta += ' · ' + v.estado_aprobacion;
                if (v.observacion) textoMeta += ' · ' + v.observacion;
                meta.textContent = textoMeta;

                info.appendChild(nombre);
                info.appendChild(meta);

                const badge = document.createElement('span');
                badge.className = 'gestor-badge ' + (esVigente(v) ? 'gestor-badge-vigente' : 'gestor-badge-historica');
                badge.textContent = esVigente(v) ? 'VIGENTE' : (v.numero_version != null ? 'v' + v.numero_version : 'PENDIENTE');

                const enlace = document.createElement('a');
                enlace.className = 'gestor-enlace';
                enlace.href = '/app/gestor_descargar.php?id_version=' + encodeURIComponent(v.id_version);
                enlace.textContent = 'Descargar';

                item.appendChild(info);
                item.appendChild(badge);
                item.appendChild(enlace);
                listaVersiones.appendChild(item);
            });
        }

        function esVigente(v) {
            return v.es_actual === '1' || v.es_actual === 1 || v.es_actual === true;
        }

        function abrirFormularioSubir(idDocumento, titulo) {
            if (!modalSubir) return;
            documentoActual = { id: idDocumento, titulo: titulo };
            if (formSubir) formSubir.reset();
            if (inputIdDocumento) inputIdDocumento.value = idDocumento;
            const tituloSubir = document.getElementById('gestor-subir-titulo');
            if (tituloSubir) tituloSubir.textContent = 'Subir nueva versión — ' + titulo;
            modalSubir.hidden = false;
            if (inputArchivo) inputArchivo.focus();
        }

        function subirArchivo() {
            if (!formSubir || !inputArchivo) return;
            const archivo = inputArchivo.files && inputArchivo.files[0];
            if (!archivo) {
                mostrarAlerta('Selecciona un archivo para subir.', false);
                return;
            }

            const btnGuardar = document.getElementById('gestor-subir-guardar');
            const datos = new FormData(formSubir);
            if (csrf) datos.set('csrf_token', csrf);
            if (btnGuardar) btnGuardar.disabled = true;

            fetch('/app/gestor_subir.php', { method: 'POST', body: datos, credentials: 'same-origin' })
                .then((r) => r.json().catch(() => ({ success: false, message: 'Respuesta inválida del servidor.' })))
                .then((data) => {
                    if (data.success) {
                        if (modalSubir) modalSubir.hidden = true;
                        mostrarAlerta(data.message || 'Versión subida correctamente.', true);
                        cargarLista();
                        if (documentoActual && modalVersiones && !modalVersiones.hidden) {
                            cargarHistorial(documentoActual.id);
                        }
                    } else {
                        mostrarAlerta(data.message || 'No se pudo subir el archivo.', false);
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
            fetch('/app/gestor_listar.php', { credentials: 'same-origin' })
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
            if (!tbody) return;
            tbody.textContent = '';
            if (contador) contador.textContent = documentos.length + ' documento(s)';

            if (documentos.length === 0) {
                const tr = document.createElement('tr');
                const td = document.createElement('td');
                td.colSpan = 5;
                td.className = 'gestor-empty';
                td.textContent = 'No hay documentos registrados todavía.';
                tr.appendChild(td);
                tbody.appendChild(tr);
                return;
            }

            documentos.forEach((doc) => {
                const tr = document.createElement('tr');

                // Documento
                const tdDoc = document.createElement('td');
                tdDoc.setAttribute('data-label', 'Documento');
                const divDoc = document.createElement('div');
                divDoc.className = 'gestor-doc';
                const spanTitulo = document.createElement('span');
                spanTitulo.className = 'gestor-doc-titulo';
                spanTitulo.textContent = doc.titulo || 'N/A';
                const spanTipo = document.createElement('span');
                spanTipo.className = 'gestor-doc-tipo';
                spanTipo.textContent = doc.tipo_documento || 'N/A';
                divDoc.appendChild(spanTitulo);
                divDoc.appendChild(spanTipo);
                tdDoc.appendChild(divDoc);
                tr.appendChild(tdDoc);

                // Empleado
                tr.appendChild(celda('Empleado', doc.nombre_empleado || doc.id_usuario || 'N/A'));

                // Archivo vigente
                const tdArchivo = document.createElement('td');
                tdArchivo.setAttribute('data-label', 'Archivo vigente');
                if (doc.archivo_actual && doc.id_version) {
                    const cont = document.createElement('div');
                    cont.className = 'gestor-archivo';

                    const nombre = document.createElement('span');
                    nombre.className = 'gestor-archivo-nombre';
                    nombre.textContent = doc.archivo_actual;
                    nombre.title = doc.archivo_actual;

                    const meta = document.createElement('span');
                    meta.className = 'text-muted';
                    meta.textContent = formatearTamano(doc.archivo_tamano) + ' · ' + formatearFecha(doc.archivo_fecha);

                    const enlace = document.createElement('a');
                    enlace.className = 'gestor-enlace';
                    enlace.href = '/app/gestor_descargar.php?id_version=' + encodeURIComponent(doc.id_version);
                    enlace.textContent = 'Descargar';

                    cont.appendChild(nombre);
                    cont.appendChild(meta);
                    cont.appendChild(enlace);
                    tdArchivo.appendChild(cont);
                } else {
                    const s = document.createElement('span');
                    s.className = 'text-muted';
                    s.textContent = 'Sin archivo';
                    tdArchivo.appendChild(s);
                }
                tr.appendChild(tdArchivo);

                // Versiones
                const tdVersiones = document.createElement('td');
                tdVersiones.setAttribute('data-label', 'Versiones');
                const badgeV = document.createElement('span');
                badgeV.className = 'gestor-badge gestor-badge-versiones';
                badgeV.textContent = String(doc.total_versiones != null ? doc.total_versiones : 0);
                tdVersiones.appendChild(badgeV);
                tr.appendChild(tdVersiones);

                // Acciones
                const tdAcciones = document.createElement('td');
                tdAcciones.setAttribute('data-label', 'Acciones');
                const divAcciones = document.createElement('div');
                divAcciones.className = 'gestor-acciones';

                const btnVersiones = document.createElement('button');
                btnVersiones.type = 'button';
                btnVersiones.className = 'gestor-btn gestor-btn-outline gestor-btn-versiones';
                btnVersiones.dataset.idDocumento = doc.id_documento;
                btnVersiones.dataset.titulo = doc.titulo || '';
                btnVersiones.textContent = 'Ver versiones';
                divAcciones.appendChild(btnVersiones);

                if (puedeCrear) {
                    const btnSubir = document.createElement('button');
                    btnSubir.type = 'button';
                    btnSubir.className = 'gestor-btn gestor-btn-primary gestor-btn-subir';
                    btnSubir.dataset.idDocumento = doc.id_documento;
                    btnSubir.dataset.titulo = doc.titulo || '';
                    btnSubir.textContent = 'Subir versión';
                    divAcciones.appendChild(btnSubir);
                }

                tdAcciones.appendChild(divAcciones);
                tr.appendChild(tdAcciones);

                tbody.appendChild(tr);
            });
        }

        function celda(label, texto) {
            const td = document.createElement('td');
            td.setAttribute('data-label', label);
            td.textContent = texto;
            return td;
        }

        function formatearTamano(bytes) {
            const n = Number(bytes);
            if (!Number.isFinite(n) || n <= 0) return 'N/A';
            if (n >= 1048576) {
                return (n / 1048576).toFixed(2).replace('.', ',') + ' MB';
            }
            return Math.round(n / 1024) + ' KB';
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
