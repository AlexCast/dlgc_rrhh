// Módulo Gestor de Archivos (30): tabla de archivos vigentes + historial de versiones
// + subida de nuevas versiones (FormData). Sin recargar la página.
(function () {
    document.addEventListener('DOMContentLoaded', () => {
        const body = document.body;
        const csrf = body.dataset.csrf || '';
        const puedeCrear = body.dataset.puedeCrear === '1';
        const puedeAprobar = body.dataset.puedeAprobar === '1';
        const puedeRechazar = body.dataset.puedeRechazar === '1';

        const tbody = document.getElementById('gestor-tbody');
        const pendientesTbody = document.getElementById('gestor-pendientes-tbody');
        const pendientesContador = document.getElementById('gestor-pendientes-contador');
        const contador = document.getElementById('gestor-contador');
        const alertas = document.getElementById('gestor-alertas');
        const buscador = document.getElementById('gestor-buscar');

        const modalVersiones = document.getElementById('gestor-modal-versiones');
        const modalSubir = document.getElementById('gestor-modal-subir');
        const modalRechazar = document.getElementById('gestor-modal-rechazar');
        const listaVersiones = document.getElementById('gestor-versiones-list');
        const tituloVersiones = document.getElementById('gestor-versiones-titulo');

        const formSubir = document.getElementById('gestor-form-subir');
        const formRechazar = document.getElementById('gestor-form-rechazar');
        const inputIdDocumento = document.getElementById('gestor-subir-id-documento');
        const inputArchivo = document.getElementById('gestor-subir-archivo');

        let documentoActual = null;
        let documentosActuales = [];

        // Delegación de eventos: aplica también a las filas renderizadas por JS.
        if (tbody) {
            tbody.addEventListener('click', (event) => {
                const btnCarpeta = event.target.closest('.gestor-folder-toggle, .gestor-empleado-toggle');
                if (btnCarpeta) {
                    const grupo = btnCarpeta.closest('.gestor-folder, .gestor-empleado');
                    const contenido = grupo ? grupo.querySelector(':scope > .gestor-folder-body, :scope > .gestor-empleado-body') : null;
                    if (grupo && contenido) {
                        const cerrada = grupo.classList.toggle('gestor-grupo-cerrado');
                        btnCarpeta.setAttribute('aria-expanded', cerrada ? 'false' : 'true');
                        contenido.hidden = cerrada;
                        const iconoCarpeta = btnCarpeta.querySelector('.gestor-folder-icon');
                        if (iconoCarpeta) {
                            iconoCarpeta.classList.toggle('fa-folder', cerrada);
                            iconoCarpeta.classList.toggle('fa-folder-open', !cerrada);
                        }
                    }
                    return;
                }
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

        if (pendientesTbody) {
            pendientesTbody.addEventListener('click', (event) => {
                const btnAprobar = event.target.closest('.gestor-btn-aprobar');
                if (btnAprobar && puedeAprobar) {
                    resolverPendiente(btnAprobar.dataset.idVersion, '/app/gestor_aprobar.php', {});
                    return;
                }
                const btnRechazar = event.target.closest('.gestor-btn-rechazar');
                if (btnRechazar && puedeRechazar) {
                    abrirFormularioRechazar(btnRechazar.dataset.idVersion);
                }
            });
        }

        const btnActualizar = document.getElementById('gestor-btn-actualizar');
        if (btnActualizar) btnActualizar.addEventListener('click', cargarLista);
        if (buscador) {
            buscador.addEventListener('input', () => renderizarLista(documentosActuales));
        }

        document.querySelectorAll('.gestor-modal-cerrar').forEach((btn) => {
            btn.addEventListener('click', cerrarModales);
        });

        [modalVersiones, modalSubir, modalRechazar].forEach((m) => {
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

        if (formRechazar) {
            formRechazar.addEventListener('submit', (event) => {
                event.preventDefault();
                const idVersion = document.getElementById('gestor-rechazar-id-version')?.value || '';
                const motivo = document.getElementById('gestor-rechazar-motivo')?.value || '';
                resolverPendiente(idVersion, '/app/gestor_rechazar.php', { motivo: motivo });
            });
        }

        function cerrarModales() {
            if (modalVersiones) modalVersiones.hidden = true;
            if (modalSubir) modalSubir.hidden = true;
            if (modalRechazar) modalRechazar.hidden = true;
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

        function abrirFormularioRechazar(idVersion) {
            if (!modalRechazar) return;
            const input = document.getElementById('gestor-rechazar-id-version');
            const motivo = document.getElementById('gestor-rechazar-motivo');
            if (formRechazar) formRechazar.reset();
            if (input) input.value = idVersion;
            modalRechazar.hidden = false;
            if (motivo) motivo.focus();
        }

        function resolverPendiente(idVersion, endpoint, campos) {
            if (!idVersion) return;
            if (endpoint.indexOf('aprobar') !== -1 && !window.confirm('¿Aprobar esta versión como documento oficial?')) return;
            if (endpoint.indexOf('rechazar') !== -1 && (!campos.motivo || campos.motivo.trim().length < 3)) {
                mostrarAlerta('Indica un motivo de al menos 3 caracteres.', false);
                return;
            }

            const datos = new FormData();
            datos.set('csrf_token', csrf);
            datos.set('id_version', idVersion);
            Object.keys(campos).forEach((clave) => datos.set(clave, campos[clave]));

            fetch(endpoint, { method: 'POST', body: datos, credentials: 'same-origin' })
                .then((r) => r.json().catch(() => ({ success: false, message: 'Respuesta inválida del servidor.' })))
                .then((data) => {
                    if (!data.success) {
                        mostrarAlerta(data.message || 'No se pudo resolver la versión.', false);
                        return;
                    }
                    cerrarModales();
                    mostrarAlerta(data.message || 'Operación completada.', true);
                    cargarPendientes();
                    cargarLista();
                })
                .catch(() => mostrarAlerta('No se pudo conectar con el servidor.', false));
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

        function cargarPendientes() {
            if (!pendientesTbody) return;
            fetch('/app/gestor_pendientes.php', { credentials: 'same-origin' })
                .then((r) => r.json().catch(() => ({ success: false, message: 'Respuesta inválida del servidor.' })))
                .then((data) => {
                    if (!data.success) {
                        mostrarAlerta(data.message || 'No se pudieron cargar los pendientes.', false);
                        return;
                    }
                    renderizarPendientes(data.pendientes || []);
                })
                .catch(() => mostrarAlerta('No se pudo conectar con el servidor.', false));
        }

        function renderizarPendientes(pendientes) {
            pendientesTbody.textContent = '';
            if (pendientesContador) pendientesContador.textContent = pendientes.length + ' pendiente(s)';
            if (pendientes.length === 0) {
                const tr = document.createElement('tr');
                const td = document.createElement('td');
                td.colSpan = 5;
                td.className = 'gestor-empty';
                td.textContent = 'No hay versiones pendientes.';
                tr.appendChild(td);
                pendientesTbody.appendChild(tr);
                return;
            }

            pendientes.forEach((v) => {
                const tr = document.createElement('tr');
                tr.appendChild(celda('Documento', (v.titulo || 'N/A') + ' · ' + (v.tipo_documento || 'N/A')));
                tr.appendChild(celda('Empleado', v.nombre_empleado || v.id_usuario || 'N/A'));
                const archivo = document.createElement('td');
                archivo.setAttribute('data-label', 'Archivo');
                const enlace = document.createElement('a');
                enlace.className = 'gestor-enlace';
                enlace.href = '/app/gestor_descargar.php?id_version=' + encodeURIComponent(v.id_version);
                enlace.textContent = v.nombre_original || 'Descargar';
                archivo.appendChild(enlace);
                tr.appendChild(archivo);
                tr.appendChild(celda('Enviado', formatearFecha(v.fec_insert)));

                const acciones = document.createElement('td');
                acciones.setAttribute('data-label', 'Acciones');
                if (puedeAprobar) {
                    const aprobar = document.createElement('button');
                    aprobar.type = 'button';
                    aprobar.className = 'gestor-btn gestor-btn-primary gestor-btn-aprobar';
                    aprobar.dataset.idVersion = v.id_version;
                    aprobar.textContent = 'Aprobar';
                    acciones.appendChild(aprobar);
                }
                if (puedeRechazar) {
                    const rechazar = document.createElement('button');
                    rechazar.type = 'button';
                    rechazar.className = 'gestor-btn gestor-btn-outline gestor-btn-rechazar';
                    rechazar.dataset.idVersion = v.id_version;
                    rechazar.textContent = 'Rechazar';
                    acciones.appendChild(rechazar);
                }
                tr.appendChild(acciones);
                pendientesTbody.appendChild(tr);
            });
        }

        function renderizarLista(documentos) {
            if (!tbody) return;
            documentosActuales = documentos;
            tbody.textContent = '';
            const termino = buscador ? buscador.value.trim().toLocaleLowerCase() : '';
            const visibles = documentos.filter((doc) => {
                if (!termino) return true;
                return [
                    doc.tipo_documento,
                    doc.titulo,
                    doc.nombre_empleado,
                    doc.archivo_actual
                ].some((valor) => String(valor || '').toLocaleLowerCase().includes(termino));
            });
            if (contador) contador.textContent = documentos.length + ' documento(s)';

            if (visibles.length === 0) {
                const empty = document.createElement('div');
                empty.className = 'gestor-empty';
                empty.textContent = termino ? 'No hay documentos que coincidan con la búsqueda.' : 'No hay documentos registrados todavía.';
                tbody.appendChild(empty);
                return;
            }

            const grupos = new Map();
            visibles.forEach((doc) => {
                const categoria = doc.tipo_documento || 'Sin categoría';
                if (!grupos.has(categoria)) grupos.set(categoria, []);
                grupos.get(categoria).push(doc);
            });

            Array.from(grupos.keys()).sort((a, b) => a.localeCompare(b, 'es')).forEach((categoria) => {
                const documentosCategoria = grupos.get(categoria);
                const carpeta = document.createElement('section');
                carpeta.className = 'gestor-folder';
                const botonCarpeta = document.createElement('button');
                botonCarpeta.type = 'button';
                botonCarpeta.className = 'gestor-folder-toggle';
                const carpetaAbierta = termino.length > 0;
                botonCarpeta.setAttribute('aria-expanded', carpetaAbierta ? 'true' : 'false');
                botonCarpeta.append(icono(carpetaAbierta ? 'fa-folder-open' : 'fa-folder', 'gestor-folder-icon'), texto(categoria, 'gestor-folder-name'));
                const total = texto(documentosCategoria.length + ' archivo(s)', 'gestor-folder-count');
                botonCarpeta.appendChild(total);
                carpeta.appendChild(botonCarpeta);

                const cuerpoCarpeta = document.createElement('div');
                cuerpoCarpeta.className = 'gestor-folder-body';
                cuerpoCarpeta.hidden = !carpetaAbierta;
                if (!carpetaAbierta) carpeta.classList.add('gestor-grupo-cerrado');
                const empleados = new Map();
                documentosCategoria.forEach((doc) => {
                    const empleado = doc.nombre_empleado || doc.id_usuario || 'Empleado sin identificar';
                    if (!empleados.has(empleado)) empleados.set(empleado, []);
                    empleados.get(empleado).push(doc);
                });
                Array.from(empleados.keys()).sort((a, b) => a.localeCompare(b, 'es')).forEach((empleado) => {
                    const grupoEmpleado = document.createElement('div');
                    grupoEmpleado.className = 'gestor-empleado';
                    const botonEmpleado = document.createElement('button');
                    botonEmpleado.type = 'button';
                    botonEmpleado.className = 'gestor-empleado-toggle';
                    botonEmpleado.setAttribute('aria-expanded', carpetaAbierta ? 'true' : 'false');
                    botonEmpleado.append(icono('fa-user'), texto(empleado, 'gestor-empleado-name'));
                    botonEmpleado.appendChild(texto(empleados.get(empleado).length + ' documento(s)', 'gestor-empleado-count'));
                    grupoEmpleado.appendChild(botonEmpleado);

                    const cuerpoEmpleado = document.createElement('div');
                    cuerpoEmpleado.className = 'gestor-empleado-body';
                    cuerpoEmpleado.hidden = !carpetaAbierta;
                    if (!carpetaAbierta) grupoEmpleado.classList.add('gestor-grupo-cerrado');
                    empleados.get(empleado).forEach((doc) => {
                        const archivo = document.createElement('article');
                        archivo.className = 'gestor-file';
                        archivo.appendChild(icono(doc.archivo_actual ? 'fa-file-lines' : 'fa-file-circle-question', 'gestor-file-icon'));
                        const detalle = document.createElement('div');
                        detalle.className = 'gestor-file-detail';
                        detalle.appendChild(texto(doc.titulo || 'Documento sin título', 'gestor-file-title'));
                        detalle.appendChild(texto(doc.archivo_actual || 'Sin archivo vigente', 'gestor-file-name'));
                        detalle.appendChild(texto(
                            (doc.archivo_actual ? formatearTamano(doc.archivo_tamano) + ' · ' : '') +
                            'Última actualización: ' + formatearFecha(doc.archivo_fecha),
                            'gestor-file-meta'
                        ));
                        archivo.appendChild(detalle);
                        const acciones = document.createElement('div');
                        acciones.className = 'gestor-file-actions';
                        if (doc.archivo_actual && doc.id_version) {
                            const descargar = document.createElement('a');
                            descargar.className = 'gestor-btn gestor-btn-outline';
                            descargar.href = '/app/gestor_descargar.php?id_version=' + encodeURIComponent(doc.id_version);
                            descargar.append(icono('fa-download'), document.createTextNode(' Descargar'));
                            acciones.appendChild(descargar);
                        }
                        const versiones = document.createElement('button');
                        versiones.type = 'button';
                        versiones.className = 'gestor-btn gestor-btn-outline gestor-btn-versiones';
                        versiones.dataset.idDocumento = doc.id_documento;
                        versiones.dataset.titulo = doc.titulo || '';
                        versiones.append(icono('fa-clock-rotate-left'), document.createTextNode(' Versiones (' + (doc.total_versiones || 0) + ')'));
                        acciones.appendChild(versiones);
                        if (puedeCrear) {
                            const subir = document.createElement('button');
                            subir.type = 'button';
                            subir.className = 'gestor-btn gestor-btn-primary gestor-btn-subir';
                            subir.dataset.idDocumento = doc.id_documento;
                            subir.dataset.titulo = doc.titulo || '';
                            subir.append(icono('fa-upload'), document.createTextNode(' Subir'));
                            acciones.appendChild(subir);
                        }
                        archivo.appendChild(acciones);
                        cuerpoEmpleado.appendChild(archivo);
                    });
                    grupoEmpleado.appendChild(cuerpoEmpleado);
                    cuerpoCarpeta.appendChild(grupoEmpleado);
                });
                carpeta.appendChild(cuerpoCarpeta);
                tbody.appendChild(carpeta);
            });
        }

        function texto(valor, clase) {
            const span = document.createElement('span');
            span.className = clase;
            span.textContent = valor;
            return span;
        }

        function icono(clase, adicional) {
            const i = document.createElement('i');
            i.className = 'fa-solid ' + clase + (adicional ? ' ' + adicional : '');
            ariaHidden(i);
            return i;
        }

        function ariaHidden(elemento) {
            elemento.setAttribute('aria-hidden', 'true');
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

        cargarLista();
        cargarPendientes();
    });
})();
