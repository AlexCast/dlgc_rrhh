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
    const tabs = document.querySelectorAll('.sst-tab');
    const panels = document.querySelectorAll('.sst-tab-panel');
    const cargadas = new Set();

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

            if (!cargadas.has(target)) {
                cargadas.add(target);
                cargarBandeja(target);
            }
        });
    });

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
            window.location.href = '/dlgc_rrhh/app/permiso_reporte.php?' + params.toString();
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
    DINERO: 'Sí',
    VACACIONES: 'Vacaciones',
    'N/A': 'No remunerado'
};

function claseEstado(estado) {
    const mapa = {
        PENDIENTE: 'status-pendiente',
        EN_REVISION: 'status-en-proceso',
        APROBADO: 'status-resuelto',
        RECHAZADO: 'status-cancelado-usuario',
        CANCELADO: 'status-cancelado-encargado'
    };
    return mapa[estado] || 'status-pendiente';
}

function cargarBandeja(vista, historial) {
    const contenedor = document.getElementById('lista-' + vista);
    if (!contenedor) return;

    contenedor.innerHTML = '<p class="sst-section-desc">Cargando...</p>';

    const query = 'vista=' + encodeURIComponent(vista) + (historial ? '&historial=1' : '');
    fetch('/dlgc_rrhh/app/permiso_listar.php?' + query, {
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

function renderBandeja(contenedor, solicitudes, vista, historial) {
    if (!solicitudes || solicitudes.length === 0) {
        contenedor.innerHTML = '<div class="sst-empty">' + (historial ? 'Sin solicitudes resueltas todavía.' : 'No hay solicitudes pendientes.') + '</div>';
        return;
    }

    contenedor.innerHTML = '';
    solicitudes.forEach(sol => {
        const card = document.createElement('div');
        card.className = 'sst-queja-card';

        const rango = sol.es_por_horas === true || sol.es_por_horas === 't'
            ? `${sol.fecha_inicio} (${sol.hora_inicio || ''} - ${sol.hora_fin || ''})`
            : `${sol.fecha_inicio} a ${sol.fecha_fin}`;

        const puedeResolverJefe = !historial && vista === 'jefe' && sol.estado_jefe === 'PENDIENTE';
        const puedeResolverRrhh = !historial && vista === 'rrhh' && sol.estado_rrhh === 'PENDIENTE';
        const metodoDescuentoTexto = sol.metodo_descuento ? (ETIQUETAS_METODO_DESCUENTO[sol.metodo_descuento] || sol.metodo_descuento) : 'Pendiente por definir (RRHH)';

        card.innerHTML = `
            <div class="sst-queja-header">
                <span class="sst-queja-type">Solicitud #${sol.id_permiso} · ${sol.empleado_nombre || ''} ${sol.empleado_apellido || ''}</span>
                <span class="sst-queja-status ${claseEstado(sol.estado)}">${ETIQUETAS_ESTADO[sol.estado] || sol.estado}</span>
            </div>
            <p class="sst-queja-subject">${sol.motivos || 'Sin motivo registrado'}</p>
            <div class="sst-form-meta">
                <span>Fechas: ${rango}</span>
                <span>Evidencias adjuntas: ${sol.total_evidencias}</span>
                <span>Nivel Jefe: ${ETIQUETAS_ESTADO[sol.estado_jefe] || sol.estado_jefe || '—'}</span>
                <span>Nivel RRHH: ${ETIQUETAS_ESTADO[sol.estado_rrhh] || sol.estado_rrhh || '—'}</span>
                <span>Remunerado: ${metodoDescuentoTexto}</span>
            </div>
            ${puedeResolverRrhh ? `
            <div class="perm-field">
                <label>¿El permiso es remunerado?</label>
                <select class="metodo-descuento-select" data-id="${sol.id_permiso}">
                    <option value="">-- Selecciona una opción --</option>
                    <option value="DINERO">Sí</option>
                    <option value="VACACIONES">Vacaciones</option>
                    <option value="NO">No</option>
                </select>
            </div>` : ''}
            ${(puedeResolverJefe || puedeResolverRrhh) ? `
            <div class="perm-actions">
                <input type="text" class="observacion-input" placeholder="Observación (opcional)" data-id="${sol.id_permiso}">
                <button class="btn btn-primary btn-aprobar" data-id="${sol.id_permiso}" data-nivel="${vista === 'jefe' ? 'JEFE' : 'RRHH'}">Aprobar</button>
                <button class="btn btn-outline btn-rechazar" data-id="${sol.id_permiso}" data-nivel="${vista === 'jefe' ? 'JEFE' : 'RRHH'}">Rechazar</button>
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

    let metodoDescuento = '';
    if (nivel === 'RRHH' && accion === 'APROBADO') {
        const metodoSelect = contenedor.querySelector(`.metodo-descuento-select[data-id="${idPermiso}"]`);
        metodoDescuento = metodoSelect ? metodoSelect.value : '';
        if (!metodoDescuento) {
            alert('Selecciona si el permiso es remunerado (Sí, Vacaciones o No) antes de aprobar.');
            return;
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

    fetch('/dlgc_rrhh/app/permiso_resolver.php', {
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
