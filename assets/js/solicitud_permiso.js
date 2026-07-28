// =============================================
// Script para Solicitud de Permiso y Vacaciones
// =============================================

document.addEventListener('DOMContentLoaded', function() {
    initializeForm();
});

function initializeForm() {
    // Auto-rellenar campos
    autoFillEmployeeInfo();
    
    // Event listeners para tipo de solicitud
    const tipoRadios = document.querySelectorAll('input[name="tipo_solicitud"]');
    tipoRadios.forEach(radio => {
        radio.addEventListener('change', handleTipoSolicitudChange);
    });

    // Event listeners para fechas y horas
    const fechaInicio = document.getElementById('fecha_inicio');
    const fechaFin = document.getElementById('fecha_fin');
    const horaInicio = document.getElementById('hora_inicio');
    const horaFin = document.getElementById('hora_fin');

    if (fechaInicio) fechaInicio.addEventListener('change', calculateTotalDias);
    if (fechaFin) fechaFin.addEventListener('change', calculateTotalDias);
    if (horaInicio) horaInicio.addEventListener('change', calculateTotalHoras);
    if (horaFin) horaFin.addEventListener('change', calculateTotalHoras);

    // Event listeners para motivos
    const motivoCheckboxes = document.querySelectorAll('input[name="motivo"]');
    motivoCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', handleMotivoChange);
    });

    // Event listener para el formulario
    const form = document.getElementById('permiso-form');
    if (form) {
        form.addEventListener('submit', handleFormSubmit);
    }

    // Cerrar/Abrir sidebar (reutilizar del firstpage.js)
    initializeSidebar();
    
    // Theme toggle
    const themeToggle = document.getElementById('theme-toggle');
    if (themeToggle) {
        themeToggle.addEventListener('click', toggleTheme);
    }
}

/**
 * Auto-rellenar información del empleado
 * En producción, estos datos vendrían del servidor
 */
function autoFillEmployeeInfo() {
    const today = new Date();
    const dateStr = formatDate(today);
    
    // Establecer fecha de solicitud (hoy)
    const fechaSolicitud = document.getElementById('fecha_solicitud');
    if (fechaSolicitud) {
        fechaSolicitud.value = dateStr;
    }

    // Datos del empleado (simulados - en producción vendrían del servidor)
    const empleadoData = {
        nombre: 'Carlos Mendoza',
        documento: '1098765432',
        cargo: 'Área de Operaciones'
    };

    const nombreEmpleado = document.getElementById('nombre_empleado');
    const documentoEmpleado = document.getElementById('documento_empleado');
    const cargoEmpleado = document.getElementById('cargo_empleado');

    if (nombreEmpleado) nombreEmpleado.value = empleadoData.nombre;
    if (documentoEmpleado) documentoEmpleado.value = empleadoData.documento;
    if (cargoEmpleado) cargoEmpleado.value = empleadoData.cargo;

    // Establecer la fecha de inicio como la fecha de hoy para permisos por días
    const fechaInicio = document.getElementById('fecha_inicio');
    if (fechaInicio) {
        fechaInicio.value = dateStr;
    }
}

/**
 * Formatear fecha a YYYY-MM-DD
 */
function formatDate(date) {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
}

/**
 * Manejar cambio en tipo de solicitud (días vs horas)
 */
function handleTipoSolicitudChange(event) {
    const tipo = event.target.value;
    const datesSection = document.getElementById('dates-section');
    const hoursSection = document.getElementById('hours-section');

    if (tipo === 'dias') {
        datesSection.style.display = 'block';
        hoursSection.style.display = 'none';
        // Limpiar campos de horas
        document.getElementById('hora_inicio').value = '';
        document.getElementById('hora_fin').value = '';
        document.getElementById('total_horas').value = '';
    } else if (tipo === 'horas') {
        datesSection.style.display = 'none';
        hoursSection.style.display = 'block';
        // Limpiar campos de fechas
        document.getElementById('fecha_fin').value = '';
        document.getElementById('total_dias').value = '';
    }
}

/**
 * Calcular total de días
 */
function calculateTotalDias() {
    const fechaInicio = document.getElementById('fecha_inicio');
    const fechaFin = document.getElementById('fecha_fin');
    const totalDias = document.getElementById('total_dias');

    if (fechaInicio.value && fechaFin.value) {
        const inicio = new Date(fechaInicio.value);
        const fin = new Date(fechaFin.value);

        // Asegurar que la fecha de inicio es menor o igual a la fecha de fin
        if (inicio > fin) {
            totalDias.value = 0;
            return;
        }

        // Calcular diferencia en días
        const diffTime = fin - inicio;
        const diffDays = Math.floor(diffTime / (1000 * 60 * 60 * 24)) + 1; // +1 para incluir el día de inicio

        totalDias.value = diffDays > 0 ? diffDays : 0;
    } else {
        totalDias.value = '';
    }
}

/**
 * Calcular total de horas
 */
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
            const diferencia = finMinutos - inicioMinutos;
            const horas = diferencia / 60;
            totalHoras.value = horas.toFixed(1);
        } else {
            totalHoras.value = 0;
        }
    } else {
        totalHoras.value = '';
    }
}

/**
 * Manejar cambios en los checkboxes de motivo
 */
function handleMotivoChange(event) {
    const detalleSection = document.getElementById('detalle-motivo-section');
    const motivoOtro = document.getElementById('motivo_otro');

    // Mostrar campo de detalle si se selecciona "Otro Motivo"
    if (motivoOtro && motivoOtro.checked) {
        detalleSection.style.display = 'block';
    } else {
        detalleSection.style.display = 'none';
        document.getElementById('detalle_motivo').value = '';
    }
}

/**
 * Manejar envío del formulario
 */
function handleFormSubmit(event) {
    event.preventDefault();

    // Validar que se haya seleccionado un motivo
    const motivoSeleccionado = document.querySelector('input[name="motivo"]:checked');
    if (!motivoSeleccionado) {
        alert('Por favor, selecciona un motivo para tu solicitud.');
        return;
    }

    // Validar tipo de solicitud
    const tipoSolicitud = document.querySelector('input[name="tipo_solicitud"]:checked');
    if (!tipoSolicitud) {
        alert('Por favor, selecciona si es permiso por días u horas.');
        return;
    }

    // Validar permiso remunerado
    const permisoRemunerado = document.querySelector('input[name="permiso_remunerado"]:checked');
    if (!permisoRemunerado) {
        alert('Por favor, selecciona si el permiso es remunerado con dinero o vacaciones.');
        return;
    }

    // Validar fechas o horas
    if (tipoSolicitud.value === 'dias') {
        const fechaInicio = document.getElementById('fecha_inicio').value;
        const fechaFin = document.getElementById('fecha_fin').value;
        if (!fechaInicio || !fechaFin) {
            alert('Por favor, completa las fechas de inicio y fin.');
            return;
        }
    } else if (tipoSolicitud.value === 'horas') {
        const horaInicio = document.getElementById('hora_inicio').value;
        const horaFin = document.getElementById('hora_fin').value;
        if (!horaInicio || !horaFin) {
            alert('Por favor, completa las horas de inicio y fin.');
            return;
        }
    }

    // Recopilar datos del formulario como FormData (name="motivo" viaja como valor único)
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
                alert(data.message || 'No se pudo enviar la solicitud.');
                if (submitBtn) submitBtn.disabled = false;
                return;
            }
            alert(data.message || 'Solicitud enviada exitosamente.');
            window.location.href = '/dlgc_rrhh/templates/firstpage.php';
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error de conexión al enviar la solicitud.');
            if (submitBtn) submitBtn.disabled = false;
        });
}

/**
 * Inicializar sidebar
 */
function initializeSidebar() {
    const openSidebarBtn = document.getElementById('open-sidebar');
    const closeSidebarBtn = document.getElementById('close-sidebar');
    const sidebar = document.getElementById('sidebar');

    if (openSidebarBtn) {
        openSidebarBtn.addEventListener('click', function() {
            sidebar.classList.add('open');
        });
    }

    if (closeSidebarBtn) {
        closeSidebarBtn.addEventListener('click', function() {
            sidebar.classList.remove('open');
        });
    }

    // Cerrar sidebar al hacer clic en un enlace de navegación (en móvil)
    const navItems = document.querySelectorAll('.sidebar-nav a');
    navItems.forEach(item => {
        item.addEventListener('click', function() {
            sidebar.classList.remove('open');
        });
    });

    // Cerrar sidebar al hacer clic fuera en móvil
    document.addEventListener('click', function(event) {
        const isSidebarClick = sidebar && sidebar.contains(event.target);
        const isOpenBtnClick = openSidebarBtn && openSidebarBtn.contains(event.target);
        
        if (!isSidebarClick && !isOpenBtnClick && window.innerWidth < 768) {
            if (sidebar) sidebar.classList.remove('open');
        }
    });
}

/**
 * Toggle tema oscuro/claro
 */
function toggleTheme() {
    const html = document.documentElement;
    const isDark = html.getAttribute('data-theme') === 'dark';
    
    if (isDark) {
        html.removeAttribute('data-theme');
        localStorage.setItem('theme', 'light');
    } else {
        html.setAttribute('data-theme', 'dark');
        localStorage.setItem('theme', 'dark');
    }
}

// Cargar tema guardado al iniciar
window.addEventListener('load', function() {
    const savedTheme = localStorage.getItem('theme');
    if (savedTheme === 'dark') {
        document.documentElement.setAttribute('data-theme', 'dark');
    }
});
