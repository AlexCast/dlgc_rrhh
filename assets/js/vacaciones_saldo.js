// Página "Gestión de Vacaciones": consulta de saldo por ciclo aniversario y ajustes manuales.
(function () {
    document.addEventListener('DOMContentLoaded', () => {
        const selectEmpleado = document.getElementById('vacaciones-empleado');
        const form = document.getElementById('vacaciones-ajuste-form');
        if (!selectEmpleado) return;

        cargarEmpleados();

        selectEmpleado.addEventListener('change', () => {
            actualizarEnlacesExportacion(selectEmpleado.value);
            if (selectEmpleado.value) {
                cargarSaldo(selectEmpleado.value);
            }
        });

        if (form) {
            form.addEventListener('submit', (event) => {
                event.preventDefault();
                registrarAjuste();
            });
        }
    });

    function actualizarLimitesFechaAjuste(saldo) {
        const inputFecha = document.getElementById('vacaciones-fecha-ajuste');
        const ayuda = document.getElementById('vacaciones-fecha-ajuste-ayuda');
        if (!inputFecha) return;

        if (!saldo) {
            inputFecha.disabled = true;
            inputFecha.removeAttribute('min');
            inputFecha.removeAttribute('max');
            if (ayuda) ayuda.textContent = 'Sin ciclo vigente: este empleado aún no puede recibir ajustes.';
            return;
        }

        inputFecha.disabled = false;
        inputFecha.min = saldo.periodo_inicio;
        inputFecha.max = saldo.periodo_fin;
        if (inputFecha.value < saldo.periodo_inicio || inputFecha.value > saldo.periodo_fin) {
            inputFecha.value = new Date() < new Date(saldo.periodo_fin) && new Date() > new Date(saldo.periodo_inicio)
                ? new Date().toISOString().slice(0, 10)
                : saldo.periodo_inicio;
        }
        if (ayuda) ayuda.textContent = `Debe estar dentro del ciclo vigente: ${saldo.periodo_inicio} a ${saldo.periodo_fin}.`;
    }

    function actualizarEnlacesExportacion(idEmpleado) {
        const enlaceExcel = document.getElementById('vacaciones-exportar-empleado-excel');
        const enlacePdf = document.getElementById('vacaciones-exportar-empleado-pdf');
        if (!enlaceExcel || !enlacePdf) return;

        if (!idEmpleado) {
            enlaceExcel.hidden = true;
            enlacePdf.hidden = true;
            return;
        }

        enlaceExcel.href = '/dlgc_rrhh/app/vacaciones_reporte.php?formato=excel&id_empleado=' + encodeURIComponent(idEmpleado);
        enlacePdf.href = '/dlgc_rrhh/app/vacaciones_reporte.php?formato=pdf&id_empleado=' + encodeURIComponent(idEmpleado);
        enlaceExcel.hidden = false;
        enlacePdf.hidden = false;
    }

    function cargarEmpleados() {
        const selectEmpleado = document.getElementById('vacaciones-empleado');
        fetch('/dlgc_rrhh/app/vacaciones_saldo.php?accion=empleados', { credentials: 'same-origin' })
            .then((response) => response.json())
            .then((data) => {
                if (!data.success) {
                    alert(data.message || 'No se pudo cargar la lista de empleados.');
                    return;
                }
                if (!data.empleados.length) {
                    alert('No hay empleados con ficha activa en t_empleados (créalos desde el módulo Empleados).');
                    return;
                }
                data.empleados.forEach((emp) => {
                    const option = document.createElement('option');
                    option.value = emp.id_usuario;
                    option.textContent = `${emp.primer_nombre} ${emp.primer_apellido} (${emp.id_usuario})`;
                    selectEmpleado.appendChild(option);
                });
            })
            .catch(() => {
                alert('Error de conexión al cargar la lista de empleados.');
            });
    }

    function cargarSaldo(idEmpleado) {
        const resumen = document.getElementById('vacaciones-saldo-resumen');
        const listaAjustes = document.getElementById('vacaciones-ajustes-lista');
        resumen.innerHTML = '<p class="vac-cycle">Cargando...</p>';
        listaAjustes.innerHTML = '';

        fetch('/dlgc_rrhh/app/vacaciones_saldo.php?id_empleado=' + encodeURIComponent(idEmpleado), {
            credentials: 'same-origin'
        })
            .then((response) => response.json())
            .then((data) => {
                if (!data.success) {
                    resumen.innerHTML = '<div class="vac-empty">' + (data.message || 'No se pudo cargar el saldo.') + '</div>';
                    return;
                }
                renderSaldo(resumen, data.saldo);
                renderAjustes(listaAjustes, data.ajustes || []);
            })
            .catch(() => {
                resumen.innerHTML = '<div class="vac-empty">Error de conexión al consultar el saldo.</div>';
            });
    }

    function renderSaldo(resumen, saldo) {
        if (!saldo || saldo.anios_servicio < 1) {
            resumen.innerHTML = '<div class="vac-empty">Este empleado aún no cumple su primer año de servicio; no ha causado vacaciones todavía.</div>';
            actualizarLimitesFechaAjuste(null);
            return;
        }

        actualizarLimitesFechaAjuste(saldo);

        const alerta = saldo.alerta_vencimiento_proximo
            ? `<div class="vac-alert">⚠ Le quedan ${saldo.dias_para_vencer} día(s) antes de perder el saldo no disfrutado en el próximo aniversario.</div>`
            : '';

        resumen.innerHTML = `
            <div class="vac-stats-grid">
                <div class="vac-stat">
                    <span class="vac-stat-label">Días causados</span>
                    <span class="vac-stat-value">${saldo.dias_causados}</span>
                </div>
                <div class="vac-stat">
                    <span class="vac-stat-label">Días disfrutados</span>
                    <span class="vac-stat-value">${saldo.dias_disfrutados}</span>
                </div>
                <div class="vac-stat vac-stat--highlight">
                    <span class="vac-stat-label">Saldo disponible</span>
                    <span class="vac-stat-value">${saldo.saldo_disponible}</span>
                </div>
            </div>
            <p class="vac-cycle">Ciclo vigente: ${saldo.periodo_inicio} a ${saldo.periodo_fin}</p>
            ${alerta}
        `;
    }

    function renderAjustes(contenedor, ajustes) {
        if (!ajustes.length) {
            contenedor.innerHTML = '<div class="vac-empty">Sin ajustes registrados para este empleado.</div>';
            return;
        }

        const puedeEliminar = document.body.dataset.puedeEliminar === '1';
        const puedeRestaurar = document.body.dataset.puedeRestaurar === '1';

        contenedor.innerHTML = '';
        ajustes.forEach((aj) => {
            const card = document.createElement('div');
            card.className = 'vac-item';
            const botonAccion = aj.activo
                ? (puedeEliminar ? `<button type="button" class="vac-btn vac-btn-outline btn-anular-ajuste" data-id="${aj.id_ajuste}">Anular</button>` : '')
                : (puedeRestaurar ? `<button type="button" class="vac-btn vac-btn-outline btn-restaurar-ajuste" data-id="${aj.id_ajuste}">Restaurar</button>` : '');
            card.innerHTML = `
                <div class="vac-item-header">
                    <span class="vac-item-dias">${aj.dias_ajuste > 0 ? '+' : ''}${aj.dias_ajuste} día(s) · ${aj.fecha_ajuste}</span>
                    <span class="vac-badge ${aj.activo ? 'vac-badge--activo' : 'vac-badge--anulado'}">${aj.activo ? 'Activo' : 'Anulado'}</span>
                </div>
                <p class="vac-item-motivo">${aj.motivo}</p>
                <div class="vac-item-meta">
                    <span>Registrado por: ${aj.usr_insert}</span>
                    <span>Fecha registro: ${aj.fec_insert}</span>
                </div>
                ${botonAccion ? `<div class="vac-item-actions">${botonAccion}</div>` : ''}
            `;
            contenedor.appendChild(card);
        });

        contenedor.querySelectorAll('.btn-anular-ajuste').forEach((btn) => {
            btn.addEventListener('click', () => anularAjuste(btn));
        });
        contenedor.querySelectorAll('.btn-restaurar-ajuste').forEach((btn) => {
            btn.addEventListener('click', () => restaurarAjuste(btn));
        });
    }

    function restaurarAjuste(btn) {
        const selectEmpleado = document.getElementById('vacaciones-empleado');
        const formData = new FormData();
        formData.append('id_ajuste', btn.dataset.id);
        formData.append('csrf_token', document.body.dataset.csrf);

        btn.disabled = true;
        fetch('/dlgc_rrhh/app/vacaciones_ajuste_restaurar.php', {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        })
            .then((response) => response.json())
            .then((data) => {
                if (!data.success) {
                    alert(data.message || 'No se pudo restaurar el ajuste.');
                    btn.disabled = false;
                    return;
                }
                cargarSaldo(selectEmpleado.value);
            })
            .catch(() => {
                alert('Error de conexión al restaurar el ajuste.');
                btn.disabled = false;
            });
    }

    function anularAjuste(btn) {
        if (!window.confirm('¿Anular este ajuste?')) return;

        const selectEmpleado = document.getElementById('vacaciones-empleado');
        const formData = new FormData();
        formData.append('id_ajuste', btn.dataset.id);
        formData.append('csrf_token', document.body.dataset.csrf);

        btn.disabled = true;
        fetch('/dlgc_rrhh/app/vacaciones_ajuste_anular.php', {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        })
            .then((response) => response.json())
            .then((data) => {
                if (!data.success) {
                    alert(data.message || 'No se pudo anular el ajuste.');
                    btn.disabled = false;
                    return;
                }
                cargarSaldo(selectEmpleado.value);
            })
            .catch(() => {
                alert('Error de conexión al anular el ajuste.');
                btn.disabled = false;
            });
    }

    function registrarAjuste() {
        const selectEmpleado = document.getElementById('vacaciones-empleado');
        const feedback = document.getElementById('vacaciones-ajuste-feedback');
        const idEmpleado = selectEmpleado.value;

        feedback.textContent = '';
        feedback.className = 'vac-feedback';

        if (!idEmpleado) {
            feedback.textContent = 'Selecciona un empleado primero.';
            feedback.className = 'vac-feedback error';
            return;
        }

        const diasARestar = Number(document.getElementById('vacaciones-dias-ajuste').value);

        const formData = new FormData();
        formData.append('id_empleado', idEmpleado);
        // El usuario ingresa cuántos días restar (positivo); el backend guarda el ajuste en negativo.
        formData.append('dias_ajuste', String(-Math.abs(diasARestar)));
        formData.append('fecha_ajuste', document.getElementById('vacaciones-fecha-ajuste').value);
        formData.append('motivo', document.getElementById('vacaciones-motivo').value);
        formData.append('csrf_token', document.body.dataset.csrf);

        fetch('/dlgc_rrhh/app/vacaciones_ajuste_insertar.php', {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        })
            .then((response) => response.json())
            .then((data) => {
                feedback.textContent = data.message || '';
                feedback.className = 'vac-feedback ' + (data.success ? 'success' : 'error');
                if (data.success) {
                    document.getElementById('vacaciones-ajuste-form').reset();
                    document.getElementById('vacaciones-fecha-ajuste').value = new Date().toISOString().slice(0, 10);
                    cargarSaldo(idEmpleado);
                }
            })
            .catch(() => {
                feedback.textContent = 'Error de conexión al registrar el ajuste.';
                feedback.className = 'vac-feedback error';
            });
    }
})();
