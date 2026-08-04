// =============================================
// Script para Solicitud de Permiso y Vacaciones
// (Nueva solicitud + Mis solicitudes + Bandeja de Jefe + Bandeja de RRHH)
// =============================================

document.addEventListener('DOMContentLoaded', function () {
    initializeSidebar();
    initializeTabs();
    initializeForm();
    initializeReportesRrhh();
});

// -------------------------------------------------
// Filtros de reporte (Nómina / RRHH) en la bandeja de RRHH
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

function getBodyFlags() {
    const body = document.body;
    return {
        puedeCrear: body.dataset.puedeCrear === '1',
        esJefe: body.dataset.esJefe === '1',
        esRrhh: body.dataset.esRrhh === '1',
        csrf: body.dataset.csrf || ''
    };
}

// -------------------------------------------------
// Festivos (para excluirlos, junto con los domingos, del conteo de días hábiles)
// -------------------------------------------------
const festivosCargados = new Set(); // fechas 'YYYY-MM-DD'
const festivosNombres = new Map(); // fecha 'YYYY-MM-DD' -> descripción
const festivosTipos = new Map(); // fecha 'YYYY-MM-DD' -> 'NACIONAL' | 'EMPRESA'
const aniosFestivosCargados = new Set();

function formatearFechaISO(date) {
    return date.getFullYear() + '-' + String(date.getMonth() + 1).padStart(2, '0') + '-' + String(date.getDate()).padStart(2, '0');
}

function cargarFestivos(anio) {
    if (aniosFestivosCargados.has(anio)) return Promise.resolve();

    return fetch('/dlgc_rrhh/app/festivos_listar.php?anio=' + encodeURIComponent(anio), {
        credentials: 'same-origin'
    })
        .then(response => response.json())
        .then(data => {
            if (data.success && Array.isArray(data.festivos)) {
                data.festivos.forEach(f => {
                    const fecha = String(f.fecha).substring(0, 10);
                    festivosCargados.add(fecha);
                    festivosNombres.set(fecha, f.descripcion || 'Festivo');
                    festivosTipos.set(fecha, f.tipo_festivo || 'NACIONAL');
                });
            }
            aniosFestivosCargados.add(anio);
            aniosFestivosCargados.add(anio + 1); // el endpoint también trae el año siguiente
            document.dispatchEvent(new CustomEvent('festivos-actualizados'));
        })
        .catch(() => {
            // Si falla la carga, el conteo simplemente no excluirá festivos hasta que se reintente.
        });
}

function esDiaNoHabil(date) {
    return date.getDay() === 0 || festivosCargados.has(formatearFechaISO(date));
}

// -------------------------------------------------
// Selector de fechas personalizado (marca domingos y festivos)
// -------------------------------------------------
const NOMBRES_MES = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
const NOMBRES_DIA = ['Lu', 'Ma', 'Mi', 'Ju', 'Vi', 'Sa', 'Do'];

function initDatePicker(wrapper) {
    const targetId = wrapper.dataset.target;
    const hiddenInput = document.getElementById(targetId);
    const displayInput = wrapper.querySelector('.date-picker-input');
    const icono = wrapper.querySelector('.date-picker-icon');
    let panel = null;
    let vistaAnio = new Date().getFullYear();
    let vistaMes = new Date().getMonth();

    function cerrarPanel() {
        if (panel) {
            panel.remove();
            panel = null;
            document.removeEventListener('click', manejarClickFuera);
        }
    }

    function manejarClickFuera(event) {
        if (panel && !panel.contains(event.target) && !wrapper.contains(event.target)) {
            cerrarPanel();
        }
    }

    function seleccionarFecha(iso, texto) {
        hiddenInput.value = iso;
        displayInput.value = texto;
        hiddenInput.dispatchEvent(new Event('change', { bubbles: true }));
        cerrarPanel();
    }

    function renderPanel() {
        if (panel) panel.remove();

        panel = document.createElement('div');
        panel.className = 'date-picker-panel';

        const header = document.createElement('div');
        header.className = 'date-picker-header';

        const btnPrev = document.createElement('button');
        btnPrev.type = 'button';
        btnPrev.className = 'date-picker-nav';
        btnPrev.textContent = '‹';
        btnPrev.addEventListener('click', () => {
            vistaMes--;
            if (vistaMes < 0) { vistaMes = 11; vistaAnio--; }
            renderPanel();
        });

        const titulo = document.createElement('span');
        titulo.className = 'date-picker-title';
        titulo.textContent = `${NOMBRES_MES[vistaMes]} ${vistaAnio}`;

        const btnNext = document.createElement('button');
        btnNext.type = 'button';
        btnNext.className = 'date-picker-nav';
        btnNext.textContent = '›';
        btnNext.addEventListener('click', () => {
            vistaMes++;
            if (vistaMes > 11) { vistaMes = 0; vistaAnio++; }
            renderPanel();
        });

        header.append(btnPrev, titulo, btnNext);

        const grid = document.createElement('div');
        grid.className = 'date-picker-grid';
        NOMBRES_DIA.forEach(dia => {
            const celdaDia = document.createElement('span');
            celdaDia.className = 'date-picker-dow';
            celdaDia.textContent = dia;
            grid.appendChild(celdaDia);
        });

        const primerDia = new Date(vistaAnio, vistaMes, 1);
        const offset = (primerDia.getDay() + 6) % 7; // lunes=0 ... domingo=6
        const diasEnMes = new Date(vistaAnio, vistaMes + 1, 0).getDate();
        const hoyIso = formatearFechaISO(new Date());

        for (let i = 0; i < offset; i++) {
            grid.appendChild(document.createElement('span'));
        }

        for (let dia = 1; dia <= diasEnMes; dia++) {
            const fecha = new Date(vistaAnio, vistaMes, dia);
            const iso = formatearFechaISO(fecha);
            const esDomingo = fecha.getDay() === 0;
            const esFestivo = festivosCargados.has(iso);
            const esFestivoEmpresa = esFestivo && festivosTipos.get(iso) === 'EMPRESA';

            const boton = document.createElement('button');
            boton.type = 'button';
            boton.className = 'date-picker-day';
            boton.textContent = String(dia);

            const noSeleccionable = esFestivo || esDomingo;

            if (esFestivoEmpresa) {
                boton.classList.add('date-picker-day--festivo-empresa');
                boton.title = (festivosNombres.get(iso) || 'Festivo') + ' (festivo de empresa)';
            } else if (esFestivo) {
                boton.classList.add('date-picker-day--festivo');
                boton.title = festivosNombres.get(iso) || 'Festivo';
            } else if (esDomingo) {
                boton.classList.add('date-picker-day--domingo');
                boton.title = 'Domingo';
            }
            if (iso === hoyIso) boton.classList.add('date-picker-day--hoy');
            if (iso === hiddenInput.value) boton.classList.add('date-picker-day--seleccionado');

            if (noSeleccionable) {
                boton.disabled = true;
                boton.classList.add('date-picker-day--disabled');
            } else {
                boton.addEventListener('click', () => {
                    const texto = String(dia).padStart(2, '0') + '/' + String(vistaMes + 1).padStart(2, '0') + '/' + vistaAnio;
                    seleccionarFecha(iso, texto);
                });
            }


            grid.appendChild(boton);
        }

        panel.append(header, grid);
        wrapper.appendChild(panel);

        cargarFestivos(vistaAnio); // refresca la vista si llegan festivos nuevos (año no cargado aún)

        setTimeout(() => document.addEventListener('click', manejarClickFuera), 0);
    }

    function abrirPanel() {
        if (panel) { cerrarPanel(); return; }
        if (hiddenInput.value) {
            const [anioSel, mesSel] = hiddenInput.value.split('-').map(Number);
            vistaAnio = anioSel;
            vistaMes = mesSel - 1;
        }
        renderPanel();
    }

    displayInput.addEventListener('click', abrirPanel);
    icono.addEventListener('click', abrirPanel);

    document.addEventListener('festivos-actualizados', () => {
        if (panel) renderPanel();
    });
}

function initializeDatePickers() {
    document.querySelectorAll('.date-picker').forEach(initDatePicker);
}

// -------------------------------------------------
// Pestañas
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

            if (!cargadas.has(target) && ['mias', 'jefe', 'rrhh'].includes(target)) {
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
// Formulario de nueva solicitud
// -------------------------------------------------
function initializeForm() {
    const tipoRadios = document.querySelectorAll('input[name="tipo_solicitud"]');
    tipoRadios.forEach(radio => radio.addEventListener('change', handleTipoSolicitudChange));

    initializeDatePickers();

    const fechaInicio = document.getElementById('fecha_inicio');
    const fechaFin = document.getElementById('fecha_fin');
    const horaInicio = document.getElementById('hora_inicio');
    const horaFin = document.getElementById('hora_fin');

    if (fechaInicio) fechaInicio.addEventListener('change', calculateTotalDias);
    if (fechaFin) fechaFin.addEventListener('change', calculateTotalDias);
    if (horaInicio) horaInicio.addEventListener('change', calculateTotalHoras);
    if (horaFin) horaFin.addEventListener('change', calculateTotalHoras);

    const motivoSelect = document.getElementById('motivo');
    if (motivoSelect) {
        motivoSelect.addEventListener('change', handleMotivoChange);
        handleMotivoChange();
    }

    const form = document.getElementById('permiso-form');
    if (form) form.addEventListener('submit', handleFormSubmit);
}

function handleTipoSolicitudChange(event) {
    const tipo = event.target.value;
    const datesSection = document.getElementById('dates-section');
    const hoursSection = document.getElementById('hours-section');

    if (tipo === 'dias') {
        datesSection.style.display = 'block';
        hoursSection.style.display = 'none';
        document.getElementById('hora_inicio').value = '';
        document.getElementById('hora_fin').value = '';
        document.getElementById('total_horas').value = '';
    } else if (tipo === 'horas') {
        datesSection.style.display = 'none';
        hoursSection.style.display = 'block';
        document.getElementById('fecha_fin').value = '';
        document.getElementById('fecha_fin_display').value = '';
        document.getElementById('total_dias').value = '';
    }
}

function calculateTotalDias() {
    const fechaInicio = document.getElementById('fecha_inicio');
    const fechaFin = document.getElementById('fecha_fin');
    const totalDias = document.getElementById('total_dias');

    if (fechaInicio.value && fechaFin.value) {
        const inicio = new Date(fechaInicio.value + 'T00:00:00');
        const fin = new Date(fechaFin.value + 'T00:00:00');

        if (inicio > fin) {
            totalDias.value = 0;
            return;
        }

        Promise.all([
            cargarFestivos(inicio.getFullYear()),
            cargarFestivos(fin.getFullYear())
        ]).then(() => {
            let dias = 0;
            const cursor = new Date(inicio);
            while (cursor <= fin) {
                if (!esDiaNoHabil(cursor)) dias++;
                cursor.setDate(cursor.getDate() + 1);
            }
            totalDias.value = dias;
        });
    } else {
        totalDias.value = '';
    }
}

function calculateTotalHoras() {
    const horaInicio = document.getElementById('hora_inicio');
    const horaFin = document.getElementById('hora_fin');
    const totalHoras = document.getElementById('total_horas');

    if (horaInicio.value && horaFin.value) {
        const inicio = horaInicio.value.split(':');
        const fin = horaFin.value.split(':');
        const inicioMinutos = parseInt(inicio[0]) * 60 + parseInt(inicio[1]);
        const finMinutos = parseInt(fin[0]) * 60 + parseInt(fin[1]);

        if (finMinutos >= inicioMinutos) {
            totalHoras.value = ((finMinutos - inicioMinutos) / 60).toFixed(1);
        } else {
            totalHoras.value = 0;
        }
    } else {
        totalHoras.value = '';
    }
}

/**
 * Muestra el campo de detalle por motivo y el bloque de evidencia si algún
 * motivo marcado la requiere (data-requiere-evidencia="1").
 */
function handleMotivoChange() {
    const select = document.getElementById('motivo');
    const detalleGroup = document.getElementById('detalle-motivo-group');
    const evidenciaSection = document.getElementById('evidencia-section');
    const selected = select && select.value ? select.options[select.selectedIndex] : null;

    if (selected) {
        if (detalleGroup) detalleGroup.style.display = 'block';
        if (evidenciaSection) evidenciaSection.style.display = selected.dataset.requiereEvidencia === '1' ? 'block' : 'none';
    } else {
        if (detalleGroup) {
            detalleGroup.style.display = 'none';
            const input = detalleGroup.querySelector('input');
            if (input) input.value = '';
        }
        if (evidenciaSection) evidenciaSection.style.display = 'none';
    }
}

function mostrarFeedbackForm(mensaje, tipo) {
    const feedback = document.getElementById('permiso-form-feedback');
    if (!feedback) return;
    feedback.textContent = mensaje;
    feedback.className = 'sst-feedback ' + tipo;
}

function handleFormSubmit(event) {
    event.preventDefault();

    const motivoSelect = document.getElementById('motivo');
    if (!motivoSelect || !motivoSelect.value) {
        mostrarFeedbackForm('Selecciona un motivo para tu solicitud.', 'error');
        return;
    }

    const tipoSolicitud = document.querySelector('input[name="tipo_solicitud"]:checked');
    if (!tipoSolicitud) {
        mostrarFeedbackForm('Selecciona si es permiso por días u horas.', 'error');
        return;
    }

    if (tipoSolicitud.value === 'dias') {
        if (!document.getElementById('fecha_inicio').value || !document.getElementById('fecha_fin').value) {
            mostrarFeedbackForm('Completa las fechas de inicio y fin.', 'error');
            return;
        }
    } else if (tipoSolicitud.value === 'horas') {
        if (!document.getElementById('hora_inicio').value || !document.getElementById('hora_fin').value) {
            mostrarFeedbackForm('Completa las horas de inicio y fin.', 'error');
            return;
        }
    }

    const evidenciaSection = document.getElementById('evidencia-section');
    const evidenciasInput = document.getElementById('evidencias');
    const requiereEvidencia = evidenciaSection && evidenciaSection.style.display === 'block';
    if (requiereEvidencia && (!evidenciasInput.files || evidenciasInput.files.length === 0)) {
        mostrarFeedbackForm('El motivo seleccionado requiere al menos un archivo de evidencia (JPG, PNG o PDF).', 'error');
        return;
    }

    const formData = new FormData(event.target);
    const submitBtn = event.target.querySelector('button[type="submit"]');
    if (submitBtn) submitBtn.disabled = true;

    fetch('/dlgc_rrhh/app/solicitud_permiso.php', {
        method: 'POST',
        body: formData,
        credentials: 'same-origin'
    })
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                mostrarFeedbackForm(data.message || 'No se pudo enviar la solicitud.', 'error');
                if (submitBtn) submitBtn.disabled = false;
                return;
            }
            mostrarFeedbackForm(data.message || 'Solicitud enviada exitosamente.', 'success');
            event.target.reset();
            document.getElementById('dates-section').style.display = 'none';
            document.getElementById('hours-section').style.display = 'none';
            document.getElementById('evidencia-section').style.display = 'none';
            const detalleGroup = document.getElementById('detalle-motivo-group');
            if (detalleGroup) detalleGroup.style.display = 'none';
            if (submitBtn) submitBtn.disabled = false;
        })
        .catch(error => {
            console.error('Error:', error);
            mostrarFeedbackForm('Error de conexión al enviar la solicitud.', 'error');
            if (submitBtn) submitBtn.disabled = false;
        });
}

// -------------------------------------------------
// Bandejas (Mis Solicitudes / Jefe / RRHH)
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
            <div class="form-group">
                <label>¿El permiso es remunerado?</label>
                <select class="metodo-descuento-select" data-id="${sol.id_permiso}">
                    <option value="">-- Selecciona una opción --</option>
                    <option value="DINERO">Sí</option>
                    <option value="VACACIONES">Vacaciones</option>
                    <option value="NO">No</option>
                </select>
            </div>` : ''}
            ${(puedeResolverJefe || puedeResolverRrhh) ? `
            <div class="form-actions">
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
// Sidebar y tema
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
