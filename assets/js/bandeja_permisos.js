// =============================================
// Bandeja de Aprobaciones (Jefe Directo + RRHH) — módulo independiente
// =============================================

document.addEventListener('DOMContentLoaded', function () {
    initializeSidebar();
    initializeTabs();
    initializeReportesRrhh();

    const primeraVista = document.querySelector('.sst-tab-panel.active');
    if (primeraVista) {
        cargarBandeja(primeraVista.id.replace('tab-', ''));
    }
});

function getBodyFlags() {
    const body = document.body;
    return {
        esJefe: body.dataset.esJefe === '1',
        esRrhh: body.dataset.esRrhh === '1',
        csrf: body.dataset.csrf || ''
    };
}

// -------------------------------------------------
// Pestañas (Jefe / RRHH)
// -------------------------------------------------
function initializeTabs() {
    const panels = document.querySelectorAll('.sst-tab-panel');
    const switcher = document.querySelector('.tab-switch');
    const cargadas = new Set();

    if (switcher) {
        initializeTabSwitch(switcher, (tab) => {
            const target = tab.dataset.tab;

            panels.forEach(p => {
                p.classList.remove('active');
                p.setAttribute('hidden', '');
            });

            const panel = document.getElementById('tab-' + target);
            if (panel) {
                panel.classList.add('active');
                panel.removeAttribute('hidden');
            }

            if (!cargadas.has(target)) {
                cargadas.add(target);
                cargarBandeja(target);
            }
        });
    }

    document.querySelectorAll('.btn-toggle-historial').forEach(btn => {
        btn.addEventListener('click', () => {
            const vista = btn.dataset.vista;
            const enHistorial = btn.dataset.historial === '1';
            btn.dataset.historial = enHistorial ? '0' : '1';
            btn.textContent = enHistorial ? 'Ver historial completo' : 'Ver pendientes';
            cargarBandeja(vista, !enHistorial);
        });
    });
}

// -------------------------------------------------
// Filtros de reporte (Nómina / RRHH)
// -------------------------------------------------
function initializeReportesRrhh() {
    const enlaces = document.querySelectorAll('.reporte-link');
    if (enlaces.length === 0) return;

    const mesSelect = document.getElementById('reporte-mes');
    const anioSelect = document.getElementById('reporte-anio');
    const modal = document.getElementById('modal-exportar-informe');
    const btnAbrir = document.getElementById('btn-exportar-informe');
    const btnCerrar = document.getElementById('cerrar-modal-exportar');

    if (btnAbrir && modal) {
        btnAbrir.addEventListener('click', () => modal.removeAttribute('hidden'));
    }
    if (btnCerrar && modal) {
        btnCerrar.addEventListener('click', () => modal.setAttribute('hidden', ''));
    }
    if (modal) {
        modal.addEventListener('click', (event) => {
            if (event.target === modal) modal.setAttribute('hidden', '');
        });
    }

    enlaces.forEach(enlace => {
        enlace.addEventListener('click', (event) => {
            event.preventDefault();
            const params = new URLSearchParams({
                vista: enlace.dataset.vista,
                formato: enlace.dataset.formato,
                mes: mesSelect ? mesSelect.value : '',
                anio: anioSelect ? anioSelect.value : ''
            });
            window.location.href = '/app/permiso_reporte.php?' + params.toString();
        });
    });
}

// -------------------------------------------------
// Bandejas (Jefe / RRHH)
// -------------------------------------------------
const ETIQUETAS_ESTADO = {
    PENDIENTE: 'Pendiente',
    EN_REVISION: 'En revisión',
    APROBADO: 'Aprobado',
    RECHAZADO: 'Rechazado',
    CANCELADO: 'Cancelado'
};

const ETIQUETAS_METODO_DESCUENTO = {
    DINERO: 'Remunerado (se paga normal)',
    VACACIONES: 'Vacaciones (remuneradas)',
    'N/A': 'No remunerado (se descuenta del sueldo)'
};

function claseEstado(estado) {
    const mapa = {
        PENDIENTE: 'status-pendiente',
        EN_REVISION: 'status-en-proceso',
        APROBADO: 'status-resuelto',
        RECHAZADO: 'status-rechazado',
        CANCELADO: 'status-cancelado-encargado'
    };
    return mapa[estado] || 'status-pendiente';
}

function cargarBandeja(vista, historial) {
    const contenedor = document.getElementById('lista-' + vista);
    if (!contenedor) return;

    contenedor.innerHTML = '<p class="sst-section-desc">Cargando...</p>';

    const query = 'vista=' + encodeURIComponent(vista) + (historial ? '&historial=1' : '');
    fetch('/app/permiso_listar.php?' + query, {
        credentials: 'same-origin'
    })
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                contenedor.innerHTML = '<div class="sst-empty">' + (data.message || 'No se pudo cargar la información.') + '</div>';
                return;
            }
            renderBandeja(contenedor, data.solicitudes, vista, historial);
        })
        .catch(() => {
            contenedor.innerHTML = '<div class="sst-empty">Error de conexión al cargar las solicitudes.</div>';
        });
}

function escapeHtmlEv(texto) {
    return String(texto).replace(/[&<>"']/g, function (c) {
        return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
    });
}

function formatearTamanoEvidencia(bytes) {
    const b = Number(bytes) || 0;
    if (b >= 1048576) return (b / 1048576).toFixed(1) + ' MB';
    return Math.max(1, Math.round(b / 1024)) + ' KB';
}

// -------------------------------------------------
// Formato de fechas en español y duración de la solicitud
// -------------------------------------------------
const DIAS_SEMANA_ES = ['domingo', 'lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado'];
const MESES_ES = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];

function parseFechaPermiso(valor) {
    if (!valor) return null;
    const texto = String(valor).trim();
    const soloFecha = texto.match(/^(\d{4})-(\d{2})-(\d{2})$/);
    // Las fechas sin hora se construyen en hora local para evitar el desfase UTC.
    const fecha = soloFecha
        ? new Date(Number(soloFecha[1]), Number(soloFecha[2]) - 1, Number(soloFecha[3]))
        : new Date(texto.replace(' ', 'T'));
    return isNaN(fecha.getTime()) ? null : fecha;
}

function capitalizar(texto) {
    return texto.charAt(0).toUpperCase() + texto.slice(1);
}

function formatearFechaLarga(valor) {
    const fecha = parseFechaPermiso(valor);
    if (!fecha) return '—';
    return capitalizar(DIAS_SEMANA_ES[fecha.getDay()] + ' ' + fecha.getDate() + ' de ' + MESES_ES[fecha.getMonth()] + ' de ' + fecha.getFullYear());
}

function formatearFechaHora(valor) {
    const fecha = parseFechaPermiso(valor);
    if (!fecha) return '—';
    const hh = String(fecha.getHours()).padStart(2, '0');
    const mm = String(fecha.getMinutes()).padStart(2, '0');
    return formatearFechaLarga(valor) + ', ' + hh + ':' + mm;
}

function formatearHoraPermiso(valor) {
    return valor ? String(valor).slice(0, 5) : '';
}

function calcularDuracionPermiso(sol, esPorHoras) {
    if (esPorHoras) {
        const inicio = String(sol.hora_inicio || '').slice(0, 5).split(':').map(Number);
        const fin = String(sol.hora_fin || '').slice(0, 5).split(':').map(Number);
        if (inicio.length === 2 && fin.length === 2 && !isNaN(inicio[0]) && !isNaN(fin[0])) {
            const minutos = (fin[0] * 60 + fin[1]) - (inicio[0] * 60 + inicio[1]);
            if (minutos > 0) {
                const horas = Math.floor(minutos / 60);
                const restante = minutos % 60;
                if (horas === 0) return restante + ' min';
                return horas + ' h' + (restante > 0 ? ' ' + restante + ' min' : '');
            }
        }
        return '';
    }
    const ini = parseFechaPermiso(sol.fecha_inicio);
    const fin = parseFechaPermiso(sol.fecha_fin);
    if (!ini || !fin) return '';
    const dias = Math.round((fin - ini) / 86400000) + 1;
    return dias === 1 ? '1 día' : dias + ' días';
}

function inicialesEmpleado(nombre, apellido) {
    const ini = ((nombre || '').charAt(0) + (apellido || '').charAt(0)).trim().toUpperCase();
    return ini || 'U';
}

function iconoPasoPermiso(estado) {
    const comun = 'width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"';
    if (estado === 'APROBADO') return '<svg ' + comun + '><polyline points="4 12 10 18 20 6"></polyline></svg>';
    if (estado === 'RECHAZADO') return '<svg ' + comun + '><line x1="6" y1="6" x2="18" y2="18"></line><line x1="18" y1="6" x2="6" y2="18"></line></svg>';
    return '<svg ' + comun + '><circle cx="12" cy="12" r="9"></circle><polyline points="12 7 12 12 15 14"></polyline></svg>';
}

function pasoAprobacionPermiso(numero, nivel, estado, fechaResolucion, observacion, lineaExtra) {
    const estadoClase = estado === 'APROBADO' ? 'aprobado' : (estado === 'RECHAZADO' ? 'rechazado' : 'pendiente');
    const etiquetaEstado = ETIQUETAS_ESTADO[estado] || 'Pendiente';
    const lineas = [];
    if (lineaExtra) lineas.push(lineaExtra);
    if (estado && estado !== 'PENDIENTE' && fechaResolucion) {
        lineas.push('Resuelto el ' + formatearFechaHora(fechaResolucion).toLowerCase());
    }
    const obs = (observacion || '').trim();
    return `
        <li class="pc-paso pc-paso--${estadoClase}">
            <span class="pc-paso-icono" aria-hidden="true">${iconoPasoPermiso(estado)}</span>
            <div class="pc-paso-contenido">
                <div class="pc-paso-cabecera">
                    <span class="pc-paso-nivel">Nivel ${numero} · ${escapeHtmlEv(nivel)}</span>
                    <span class="pc-paso-estado pc-paso-estado--${estadoClase}">${etiquetaEstado}</span>
                </div>
                ${lineas.length > 0 ? `<span class="pc-paso-info">${lineas.map(escapeHtmlEv).join(' · ')}</span>` : ''}
                ${obs ? `<span class="pc-paso-obs">${escapeHtmlEv(obs)}</span>` : ''}
            </div>
        </li>`;
}

function renderBandeja(contenedor, solicitudes, vista, historial) {
    if (!solicitudes || solicitudes.length === 0) {
        contenedor.innerHTML = '<div class="sst-empty">' + (historial ? 'Sin solicitudes resueltas todavía.' : 'No hay solicitudes pendientes.') + '</div>';
        return;
    }

    contenedor.innerHTML = '';
    solicitudes.forEach(sol => {
        const card = document.createElement('div');
        card.className = 'sst-queja-card permiso-card';
        if (sol.metodo_descuento === 'VACACIONES') {
            card.dataset.metodoPredefinido = 'VACACIONES';
        }

        const esPorHoras = String(sol.es_por_horas) === '1' || sol.es_por_horas === true;
        const puedeResolverJefe = !historial && vista === 'jefe' && sol.estado_jefe === 'PENDIENTE';
        const puedeResolverRrhh = !historial && vista === 'rrhh' && sol.estado_rrhh === 'PENDIENTE';
        const metodoDescuentoTexto = sol.metodo_descuento ? (ETIQUETAS_METODO_DESCUENTO[sol.metodo_descuento] || sol.metodo_descuento) : 'Pendiente por definir (RRHH)';
        const esVacaciones = sol.metodo_descuento === 'VACACIONES';
        const tituloTramite = esVacaciones ? 'Vacaciones' : (sol.motivos || 'Sin motivo registrado');
        const detalleMotivo = (sol.detalle_motivo || '').trim();
        const nombreEmpleado = [sol.empleado_nombre, sol.empleado_apellido].filter(Boolean).join(' ').trim();
        const nombreJefe = [sol.jefe_nombre, sol.jefe_apellido].filter(Boolean).join(' ').trim();
        const nombreAprobadorRrhh = [sol.aprobador_rrhh_nombre, sol.aprobador_rrhh_apellido].filter(Boolean).join(' ').trim();
        const duracion = calcularDuracionPermiso(sol, esPorHoras);

        card.innerHTML = `
            <div class="pc-cabecera">
                <div class="pc-empleado">
                    <span class="pc-avatar" aria-hidden="true">${escapeHtmlEv(inicialesEmpleado(sol.empleado_nombre, sol.empleado_apellido))}</span>
                    <div class="pc-empleado-info">
                        <span class="pc-nombre">${escapeHtmlEv(nombreEmpleado)}</span>
                        <span class="pc-solicitud">Solicitud #${sol.id_permiso} · Radicada el ${formatearFechaLarga(sol.fec_insert).toLowerCase()}</span>
                    </div>
                </div>
                <span class="sst-queja-status ${claseEstado(sol.estado)}">${ETIQUETAS_ESTADO[sol.estado] || sol.estado}</span>
            </div>

            <div class="pc-motivo">
                <h3 class="pc-motivo-titulo">${escapeHtmlEv(tituloTramite)}</h3>
                ${detalleMotivo
                    ? `<p class="pc-motivo-detalle">“${escapeHtmlEv(detalleMotivo)}”</p>`
                    : `<p class="pc-motivo-detalle pc-motivo-detalle--vacio">El empleado no detalló el motivo.</p>`}
            </div>

            <div class="pc-fechas">
                ${esPorHoras ? `
                <div class="pc-fecha">
                    <span class="pc-fecha-etiqueta">Fecha</span>
                    <span class="pc-fecha-valor">${formatearFechaLarga(sol.fecha_inicio)}</span>
                </div>
                <div class="pc-fecha">
                    <span class="pc-fecha-etiqueta">Horario</span>
                    <span class="pc-fecha-valor pc-fecha-valor--hora">${formatearHoraPermiso(sol.hora_inicio)} – ${formatearHoraPermiso(sol.hora_fin)}</span>
                </div>` : `
                <div class="pc-fecha">
                    <span class="pc-fecha-etiqueta">Inicio</span>
                    <span class="pc-fecha-valor">${formatearFechaLarga(sol.fecha_inicio)}</span>
                </div>
                <span class="pc-flecha" aria-hidden="true">→</span>
                <div class="pc-fecha">
                    <span class="pc-fecha-etiqueta">Fin</span>
                    <span class="pc-fecha-valor">${formatearFechaLarga(sol.fecha_fin)}</span>
                </div>`}
                ${duracion ? `<span class="pc-duracion">${duracion}</span>` : ''}
            </div>

            <div class="pc-aprobaciones">
                <h4 class="pc-seccion-titulo">Flujo de aprobación</h4>
                <ol class="pc-pasos">
                    ${pasoAprobacionPermiso(1, 'Jefe Directo', sol.estado_jefe, sol.fec_resolucion_jefe, sol.observacion_jefe, nombreJefe ? 'A cargo de ' + nombreJefe : '')}
                    ${pasoAprobacionPermiso(2, 'RRHH', sol.estado_rrhh, sol.fec_resolucion_rrhh, sol.observacion_rrhh, nombreAprobadorRrhh ? 'Resuelto por ' + nombreAprobadorRrhh : '')}
                </ol>
            </div>

            <div class="pc-info">
                <div class="pc-info-fila">
                    <span class="pc-info-etiqueta">Reconocimiento</span>
                    <span class="pc-info-valor${sol.metodo_descuento ? '' : ' pc-info-valor--pendiente'}">${metodoDescuentoTexto}</span>
                </div>
                <div class="pc-info-fila">
                    <span class="pc-info-etiqueta">Evidencias</span>
                    ${sol.evidencias && sol.evidencias.length > 0
                        ? `<div class="pc-evidencias">${sol.evidencias.map(ev => `
                            <a class="pc-evidencia" href="${ev.url}" target="_blank" rel="noopener">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"></path></svg>
                                ${escapeHtmlEv(ev.nombre_original)} · ${formatearTamanoEvidencia(ev.tamano_bytes)}
                            </a>`).join('')}</div>`
                        : `<span class="pc-sin-evidencias">Sin evidencias adjuntas</span>`}
                </div>
            </div>

            ${(puedeResolverJefe || puedeResolverRrhh) ? `
            <div class="pc-acciones">
                ${puedeResolverRrhh ? (esVacaciones ? `
                <p class="pc-nota">Vacaciones remuneradas: el sueldo se paga normal y los días se descuentan del saldo de vacaciones.</p>` : `
                <div class="perm-field pc-reconocimiento">
                    <label>¿Cómo se reconoce este permiso?</label>
                    <select class="metodo-descuento-select" data-id="${sol.id_permiso}">
                        <option value="">-- Selecciona una opción --</option>
                        <option value="DINERO">Remunerado (se paga normal)</option>
                        <option value="VACACIONES">Con cargo a vacaciones (remunerado)</option>
                        <option value="NO">No remunerado (se descuenta del sueldo)</option>
                    </select>
                </div>`) : ''}
                <div class="pc-acciones-fila">
                    <input type="text" class="observacion-input" placeholder="Observación (opcional)" data-id="${sol.id_permiso}">
                    <button class="btn btn-primary btn-aprobar" data-id="${sol.id_permiso}" data-nivel="${vista === 'jefe' ? 'JEFE' : 'RRHH'}">Aprobar</button>
                    <button class="btn btn-outline btn-rechazar" data-id="${sol.id_permiso}" data-nivel="${vista === 'jefe' ? 'JEFE' : 'RRHH'}">Rechazar</button>
                </div>
            </div>` : ''}
        `;

        contenedor.appendChild(card);
    });

    contenedor.querySelectorAll('.btn-aprobar, .btn-rechazar').forEach(btn => {
        btn.addEventListener('click', () => resolverSolicitud(btn, contenedor, vista));
    });
}

function resolverSolicitud(btn, contenedor, vista) {
    const idPermiso = btn.dataset.id;
    const nivel = btn.dataset.nivel;
    const accion = btn.classList.contains('btn-aprobar') ? 'APROBADO' : 'RECHAZADO';
    const observacionInput = contenedor.querySelector(`.observacion-input[data-id="${idPermiso}"]`);
    const observacion = observacionInput ? observacionInput.value : '';
    const csrf = getBodyFlags().csrf;

    // Las solicitudes de vacaciones llegan con metodo_descuento = 'VACACIONES' predefinido;
    // no se le pide a RRHH que vuelva a elegir el método de descuento.
    const card = btn.closest('.sst-queja-card');
    const metodoPredefinido = card ? card.dataset.metodoPredefinido || '' : '';

    let metodoDescuento = '';
    if (nivel === 'RRHH' && accion === 'APROBADO') {
        if (metodoPredefinido === 'VACACIONES') {
            metodoDescuento = 'VACACIONES';
        } else {
            const metodoSelect = contenedor.querySelector(`.metodo-descuento-select[data-id="${idPermiso}"]`);
            metodoDescuento = metodoSelect ? metodoSelect.value : '';
            if (!metodoDescuento) {
                alert('Selecciona cómo se reconoce el permiso antes de aprobar.');
                return;
            }
        }
    }

    btn.disabled = true;

    const formData = new FormData();
    formData.append('id_permiso', idPermiso);
    formData.append('nivel', nivel);
    formData.append('accion', accion);
    formData.append('observacion', observacion);
    formData.append('metodo_descuento', metodoDescuento);
    formData.append('csrf_token', csrf);

    fetch('/app/permiso_resolver.php', {
        method: 'POST',
        body: formData,
        credentials: 'same-origin'
    })
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                alert(data.message || 'No se pudo procesar la acción.');
                btn.disabled = false;
                return;
            }
            cargarBandeja(vista, false); // vaciar la bandeja de pendientes tras resolver
        })
        .catch(() => {
            alert('Error de conexión al procesar la acción.');
            btn.disabled = false;
        });
}

// -------------------------------------------------
// Sidebar
// -------------------------------------------------
function initializeSidebar() {
    const openSidebarBtn = document.getElementById('open-sidebar');
    const closeSidebarBtn = document.getElementById('close-sidebar');
    const sidebar = document.getElementById('sidebar');

    if (openSidebarBtn && sidebar) {
        openSidebarBtn.addEventListener('click', function () {
            sidebar.classList.add('open');
        });
    }

    if (closeSidebarBtn && sidebar) {
        closeSidebarBtn.addEventListener('click', function () {
            sidebar.classList.remove('open');
        });
    }

    document.addEventListener('click', function (event) {
        const isSidebarClick = sidebar && sidebar.contains(event.target);
        const isOpenBtnClick = openSidebarBtn && openSidebarBtn.contains(event.target);

        if (!isSidebarClick && !isOpenBtnClick && window.innerWidth < 768) {
            if (sidebar) sidebar.classList.remove('open');
        }
    });
}
