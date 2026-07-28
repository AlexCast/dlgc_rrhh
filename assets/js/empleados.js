document.addEventListener('DOMContentLoaded', () => {
    const employees = [
        { id: 1, name: 'Ana Rodríguez', role: 'Auxiliar Contable', availability: 'full' },
        { id: 2, name: 'Luis Morales', role: 'Coordinador Logística', availability: 'partial' },
        { id: 3, name: 'Marta Gómez', role: 'Analista RRHH', availability: 'full' },
        { id: 4, name: 'Roberto Sánchez', role: 'Jefe de Almacén', availability: 'off' },
        { id: 5, name: 'Elena Torres', role: 'Asistente Administrativa', availability: 'full' },
        { id: 6, name: 'Javier Castro', role: 'Conductor Senior', availability: 'partial' },
        { id: 7, name: 'Sofía Méndez', role: 'Directora Comercial', availability: 'full' },
        { id: 8, name: 'Carlos Ortega', role: 'Supervisor de Turno', availability: 'off' },
        { id: 9, name: 'Paula Vargas', role: 'Recepción', availability: 'full' },
        { id: 10, name: 'Andrés López', role: 'Seguridad y Salud', availability: 'partial' }
    ];

    const employeeGrid = document.getElementById('employee-grid');
    const searchInput = document.getElementById('employee-search');
    const emptyState = document.getElementById('empty-state');
    const calendarContainer = document.getElementById('calendar-container');
    const selectedName = document.getElementById('selected-name');
    const selectedRole = document.getElementById('selected-role');
    const calendarDays = document.getElementById('calendar-days');

    // Inicializar lista de empleados
    function renderEmployees(filteredList) {
        employeeGrid.innerHTML = '';
        filteredList.forEach(emp => {
            const card = document.createElement('button');
            card.className = 'employee-card';
            card.setAttribute('aria-label', `Ver disponibilidad de ${emp.name}`);
            card.innerHTML = `
                <div class="emp-avatar">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                </div>
                <div class="emp-info">
                    <span class="emp-name">${emp.name}</span>
                    <span class="emp-role">${emp.role}</span>
                </div>
            `;
            card.addEventListener('click', () => showAvailability(emp, card));
            employeeGrid.appendChild(card);
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

    // Mostrar disponibilidad en el panel
    function showAvailability(employee, cardElement) {
        // Marcado visual de selección
        document.querySelectorAll('.employee-card').forEach(c => c.classList.remove('active'));
        cardElement.classList.add('active');

        // Mostrar el panel de calendario
        emptyState.classList.add('hidden');
        calendarContainer.classList.remove('hidden');

        // Actualizar info del empleado seleccionado
        selectedName.textContent = employee.name;
        selectedRole.textContent = employee.role;

        generateCalendar(employee);
    }

    // Generador simple de calendario (Julio 2026 como ejemplo)
    function generateCalendar(employee) {
        calendarDays.innerHTML = '';
        const daysInMonth = 31;
        const startDay = 3; // Miércoles (por ejemplo)

        // Espaciado inicial sugerido para Julio 2026 (empieza en miércoles=3)
        // Pero para simplificar, pondremos algunos días de relleno
        for (let i = 0; i < startDay; i++) {
            const empty = document.createElement('div');
            empty.className = 'calendar-day not-current';
            calendarDays.appendChild(empty);
        }

        for (let day = 1; day <= daysInMonth; day++) {
            const dayEl = document.createElement('div');
            dayEl.className = 'calendar-day';
            dayEl.textContent = day;

            // Lógica ficticia de disponibilidad basada en el empleado
            // Usamos el ID para variar el patrón
            const status = getFictitiousStatus(day, employee.id);
            dayEl.classList.add(status);
            
            calendarDays.appendChild(dayEl);
        }
    }

    function getFictitiousStatus(day, empId) {
        // Patrón semi-aleatorio pero determinista
        const val = (day + empId) % 7;
        if (val === 0 || val === 6) return 'off'; // Fines de semana o descansos
        if (val === 1 || val === 3) return 'occupied'; // Ocupado
        return 'available'; // Disponible
    }

    // Render inicial
    renderEmployees(employees);
});
