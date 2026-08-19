document.addEventListener('DOMContentLoaded', () => {
    let employees = [];

    const employeeGrid = document.getElementById('employee-grid');
    const searchInput = document.getElementById('employee-search');
    const emptyState = document.getElementById('empty-state');
    const calendarContainer = document.getElementById('calendar-container');
    const selectedName = document.getElementById('selected-name');
    const selectedRole = document.getElementById('selected-role');
    const calendarDays = document.getElementById('calendar-days');
    const currentMonthLabel = document.getElementById('current-month');
    const prevMonthBtn = document.getElementById('prev-month');
    const nextMonthBtn = document.getElementById('next-month');
    const availabilityPanel = document.getElementById('availability-panel');

    // -------------------------------------------------
    // Festivos reales (mismo calendario que Solicitudes y Permisos)
    // -------------------------------------------------
    const NOMBRES_MES = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
    const festivosCargados = new Set(); // fechas 'YYYY-MM-DD'
    const festivosTipos = new Map(); // fecha 'YYYY-MM-DD' -> 'NACIONAL' | 'EMPRESA'
    const aniosFestivosCargados = new Set();

    let vistaAnio = new Date().getFullYear();
    let vistaMes = new Date().getMonth();
    let empleadoSeleccionado = null;

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
                        festivosTipos.set(fecha, f.tipo_festivo || 'NACIONAL');
                    });
                }
                aniosFestivosCargados.add(anio);
                aniosFestivosCargados.add(anio + 1); // el endpoint también trae el año siguiente
            })
            .catch(() => {
                // Si falla la carga, el calendario solo mostrará domingos hasta reintentar.
            });
    }

    // -------------------------------------------------
    // Ocupación real por empleado (permisos/vacaciones APROBADOS)
    // -------------------------------------------------
    const ocupacionCache = new Map(); // 'idEmpleado-anio-mes' -> Set de fechas 'YYYY-MM-DD' ocupadas

    function cargarOcupacion(idEmpleado, anio, mes) {
        const clave = `${idEmpleado}-${anio}-${mes}`;
        if (ocupacionCache.has(clave)) return Promise.resolve(ocupacionCache.get(clave));

        const parametros = new URLSearchParams({ id_empleado: idEmpleado, anio: String(anio), mes: String(mes + 1) });

        return fetch('/dlgc_rrhh/app/empleado_ocupacion.php?' + parametros.toString(), {
            credentials: 'same-origin'
        })
            .then(response => response.json())
            .then(data => {
                const ocupados = new Set();
                if (data.success && Array.isArray(data.rangos)) {
                    data.rangos.forEach(rango => {
                        let cursor = new Date(rango.fecha_inicio + 'T00:00:00');
                        const fin = new Date(rango.fecha_fin + 'T00:00:00');
                        while (cursor <= fin) {
                            ocupados.add(formatearFechaISO(cursor));
                            cursor.setDate(cursor.getDate() + 1);
                        }
                    });
                }
                ocupacionCache.set(clave, ocupados);
                return ocupados;
            })
            .catch(() => new Set());
    }

    // Posición original del panel para restaurarlo en escritorio
    const panelOriginalParent = availabilityPanel.parentElement;
    const panelOriginalNext = availabilityPanel.nextElementSibling;

    function isMobileLayout() {
        return window.matchMedia('(max-width: 1199px)').matches;
    }

    function restorePanelPosition() {
        availabilityPanel.classList.remove('mobile-inline');
        document.querySelectorAll('.employee-cell.expanded').forEach(c => c.classList.remove('expanded'));
        if (panelOriginalNext) {
            panelOriginalParent.insertBefore(availabilityPanel, panelOriginalNext);
        } else {
            panelOriginalParent.appendChild(availabilityPanel);
        }
    }

    function placePanelInline(cardElement) {
        document.querySelectorAll('.employee-cell.expanded').forEach(c => c.classList.remove('expanded'));
        // El panel se anida dentro de la misma celda del grid, no como hermano suelto,
        // así el gap del grid nunca queda entre la tarjeta y el calendario.
        const cell = cardElement.parentElement;
        cell.classList.add('expanded');
        availabilityPanel.classList.add('mobile-inline');
        cell.appendChild(availabilityPanel);
    }

    function escapeHtml(texto) {
        const div = document.createElement('div');
        div.textContent = texto;
        return div.innerHTML;
    }

    // Inicializar lista de empleados
    function renderEmployees(filteredList) {
        employeeGrid.innerHTML = '';

        if (filteredList.length === 0) {
            employeeGrid.innerHTML = '<p class="employee-grid-empty">No se encontraron empleados.</p>';
            return;
        }

        filteredList.forEach(emp => {
            const cell = document.createElement('div');
            cell.className = 'employee-cell';

            const card = document.createElement('button');
            card.className = 'employee-card';
            card.setAttribute('aria-label', `Ver disponibilidad de ${emp.name}`);
            const avatarHtml = emp.foto_perfil
                ? `<img src="/dlgc_rrhh/uploads/perfiles/${encodeURIComponent(emp.foto_perfil)}" alt="" style="width:100%;height:100%;object-fit:cover;border-radius:50%;">`
                : '<svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>';
            card.innerHTML = `
                <div class="emp-avatar">${avatarHtml}</div>
                <div class="emp-info">
                    <span class="emp-name">${escapeHtml(emp.name)}</span>
                    <span class="emp-role">${escapeHtml(emp.role)}</span>
                </div>
            `;
            card.addEventListener('click', () => {
                if (card.classList.contains('active')) {
                    collapseAvailability(card);
                } else {
                    showAvailability(emp, card);
                }
            });
            cell.appendChild(card);
            employeeGrid.appendChild(cell);
        });
    }

    // Filtrar búsqueda
    searchInput.addEventListener('input', (e) => {
        const term = e.target.value.toLowerCase();
        const filtered = employees.filter(emp =>
            emp.name.toLowerCase().includes(term) ||
            emp.role.toLowerCase().includes(term)
        );
        renderEmployees(filtered);
    });

    // Carga real del directorio (antes era un arreglo quemado en este archivo)
    function cargarEmpleados() {
        employeeGrid.innerHTML = '<p class="employee-grid-empty">Cargando empleados…</p>';

        return fetch('/dlgc_rrhh/app/empleados_listar.php', { credentials: 'same-origin' })
            .then(response => response.json())
            .then(data => {
                if (data.success && Array.isArray(data.empleados)) {
                    employees = data.empleados.map(emp => ({
                        id: emp.id_usuario,
                        name: emp.nombre_corto,
                        fullName: emp.nombre_completo,
                        role: emp.cargo,
                        foto_perfil: emp.foto_perfil || null,
                        phone: emp.numero_celular,
                        email: emp.correo
                    }));
                } else {
                    employees = [];
                }
                renderEmployees(employees);
            })
            .catch(() => {
                employees = [];
                employeeGrid.innerHTML = '<p class="employee-grid-empty">No se pudo cargar el directorio de empleados.</p>';
            });
    }

    // Mostrar disponibilidad en el panel
    function showAvailability(employee, cardElement) {
        // Marcado visual de selección
        document.querySelectorAll('.employee-card').forEach(c => c.classList.remove('active'));
        cardElement.classList.add('active');

        // Mostrar el panel de calendario
        emptyState.classList.add('hidden');
        calendarContainer.classList.remove('hidden');

        // En móvil, el calendario se despliega justo debajo de la tarjeta elegida
        if (isMobileLayout()) {
            placePanelInline(cardElement);
            requestAnimationFrame(() => {
                availabilityPanel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            });
        } else {
            restorePanelPosition();
        }

        // Actualizar info del empleado seleccionado
        selectedName.textContent = employee.name;
        selectedRole.textContent = employee.role;

        empleadoSeleccionado = employee;
        vistaAnio = new Date().getFullYear();
        vistaMes = new Date().getMonth();
        generateCalendar();
    }

    // Colapsar/cerrar el panel de disponibilidad de una tarjeta activa
    function collapseAvailability(cardElement) {
        cardElement.classList.remove('active');
        calendarContainer.classList.add('hidden');
        emptyState.classList.remove('hidden');
        empleadoSeleccionado = null;
        restorePanelPosition();
    }

    // Navegación de mes (mismo calendario real que Solicitudes y Permisos)
    prevMonthBtn.addEventListener('click', () => {
        vistaMes--;
        if (vistaMes < 0) { vistaMes = 11; vistaAnio--; }
        generateCalendar();
    });

    nextMonthBtn.addEventListener('click', () => {
        vistaMes++;
        if (vistaMes > 11) { vistaMes = 0; vistaAnio++; }
        generateCalendar();
    });

    // Reubicar el panel al cruzar el punto de quiebre responsive
    let wasMobileLayout = isMobileLayout();
    window.addEventListener('resize', () => {
        const nowMobile = isMobileLayout();
        if (nowMobile === wasMobileLayout) return;
        wasMobileLayout = nowMobile;

        const activeCard = document.querySelector('.employee-card.active');
        if (!activeCard || calendarContainer.classList.contains('hidden')) return;

        if (nowMobile) {
            placePanelInline(activeCard);
        } else {
            restorePanelPosition();
        }
    });

    // Calendario real: mismos festivos nacionales/de empresa y domingos que
    // usa Solicitudes y Permisos (fuente: app/festivos_listar.php), más los
    // permisos/vacaciones APROBADOS del empleado (fuente: app/empleado_ocupacion.php).
    function generateCalendar() {
        if (!empleadoSeleccionado) return;

        currentMonthLabel.textContent = `${NOMBRES_MES[vistaMes]} ${vistaAnio}`;

        const idEmpleado = empleadoSeleccionado.id;
        const anioVista = vistaAnio;
        const mesVista = vistaMes;

        Promise.all([
            cargarFestivos(vistaAnio),
            cargarOcupacion(idEmpleado, anioVista, mesVista)
        ]).then(([, ocupados]) => {
            // Evita pintar un mes/empleado viejo si el usuario ya cambió de selección antes de que responda el fetch.
            if (!empleadoSeleccionado || empleadoSeleccionado.id !== idEmpleado || vistaAnio !== anioVista || vistaMes !== mesVista) return;
            renderCalendarGrid(ocupados);
        });
    }

    function renderCalendarGrid(ocupados) {
        calendarDays.innerHTML = '';

        const primerDia = new Date(vistaAnio, vistaMes, 1);
        const diasEnMes = new Date(vistaAnio, vistaMes + 1, 0).getDate();
        const offset = primerDia.getDay(); // domingo=0, igual que el encabezado Do Lu Ma Mi Ju Vi Sa

        for (let i = 0; i < offset; i++) {
            const empty = document.createElement('div');
            empty.className = 'calendar-day not-current';
            calendarDays.appendChild(empty);
        }

        for (let day = 1; day <= diasEnMes; day++) {
            const fecha = new Date(vistaAnio, vistaMes, day);
            const iso = formatearFechaISO(fecha);
            const esDomingo = fecha.getDay() === 0;
            const esFestivo = festivosCargados.has(iso);
            const esFestivoEmpresa = esFestivo && festivosTipos.get(iso) === 'EMPRESA';
            const esOcupado = ocupados.has(iso);

            const dayEl = document.createElement('div');
            dayEl.className = 'calendar-day';
            dayEl.textContent = String(day);

            if (esFestivoEmpresa) {
                dayEl.classList.add('festivo-empresa');
                dayEl.title = 'Festivo de empresa';
            } else if (esFestivo) {
                dayEl.classList.add('festivo');
                dayEl.title = 'Festivo nacional';
            } else if (esDomingo) {
                dayEl.classList.add('off');
                dayEl.title = 'Domingo';
            } else if (esOcupado) {
                dayEl.classList.add('occupied');
                dayEl.title = 'Permiso o vacaciones aprobados';
            } else {
                dayEl.classList.add('available');
            }

            calendarDays.appendChild(dayEl);
        }
    }

    // Modal de datos de contacto
    const infoModal = document.getElementById('employee-info-modal');
    const infoBtn = document.getElementById('employee-info-btn');
    const infoClose = document.getElementById('employee-info-close');
    const modalBackdrop = infoModal ? infoModal.querySelector('.employee-modal-backdrop') : null;

    function openInfoModal() {
        if (!empleadoSeleccionado || !infoModal) return;
        document.getElementById('employee-modal-name').textContent = empleadoSeleccionado.fullName;
        document.getElementById('employee-modal-role').textContent = empleadoSeleccionado.role;
        document.getElementById('employee-modal-phone').textContent = empleadoSeleccionado.phone || 'No registrado';
        document.getElementById('employee-modal-email').textContent = empleadoSeleccionado.email || 'No registrado';
        const avatarContainer = document.getElementById('employee-modal-avatar');
        if (empleadoSeleccionado.foto_perfil) {
            avatarContainer.innerHTML = `<img src="/dlgc_rrhh/uploads/perfiles/${encodeURIComponent(empleadoSeleccionado.foto_perfil)}" alt="" style="width:100%;height:100%;object-fit:cover;border-radius:50%;">`;
        } else {
            avatarContainer.innerHTML = '<svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>';
        }
        infoModal.hidden = false;
        document.body.style.overflow = 'hidden';
    }

    function closeInfoModal() {
        if (!infoModal) return;
        infoModal.hidden = true;
        document.body.style.overflow = '';
    }

    if (infoBtn) infoBtn.addEventListener('click', openInfoModal);
    if (infoClose) infoClose.addEventListener('click', closeInfoModal);
    if (modalBackdrop) modalBackdrop.addEventListener('click', closeInfoModal);
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && infoModal && !infoModal.hidden) closeInfoModal();
    });

    // Render inicial
    cargarEmpleados();
});
