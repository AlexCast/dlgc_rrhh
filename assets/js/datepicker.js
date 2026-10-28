// =============================================
// Selector de fechas personalizado para CRUDs de /src.
// Mismo estilo visual que solicitud_permiso, pero sin excluir
// domingos/festivos (fechas genéricas de formularios administrativos).
// =============================================

(function () {
    'use strict';

    var NOMBRES_MES = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
    var NOMBRES_DIA = ['Lu', 'Ma', 'Mi', 'Ju', 'Vi', 'Sa', 'Do'];

    function pad2(n) {
        return String(n).padStart(2, '0');
    }

    function formatearISO(date) {
        return date.getFullYear() + '-' + pad2(date.getMonth() + 1) + '-' + pad2(date.getDate());
    }

    function formatearDisplay(iso) {
        var partes = String(iso || '').split('-');
        if (partes.length !== 3) {
            return '';
        }
        return pad2(partes[2]) + '/' + pad2(partes[1]) + '/' + partes[0];
    }

    function parseDisplay(texto) {
        var m = /^(\d{2})\/(\d{2})\/(\d{4})$/.exec(String(texto || '').trim());
        if (!m) {
            return null;
        }
        var dia = Number(m[1]);
        var mes = Number(m[2]);
        var anio = Number(m[3]);
        var fecha = new Date(anio, mes - 1, dia);
        if (fecha.getFullYear() !== anio || fecha.getMonth() !== mes - 1 || fecha.getDate() !== dia) {
            return null;
        }
        return formatearISO(fecha);
    }

    function obtenerRangoAnios(wrapper) {
        var anioActual = new Date().getFullYear();
        var min = parseInt(wrapper.getAttribute('data-min-year'), 10);
        var max = parseInt(wrapper.getAttribute('data-max-year'), 10);
        if (isNaN(min)) {
            min = 1900;
        }
        if (isNaN(max)) {
            max = anioActual + 10;
        }
        if (min > max) {
            var aux = min;
            min = max;
            max = aux;
        }
        return { min: min, max: max };
    }

    function initDatePicker(wrapper) {
        var targetId = wrapper.getAttribute('data-target');
        var hiddenInput = document.getElementById(targetId);
        var displayInput = wrapper.querySelector('.date-picker-input');
        var icono = wrapper.querySelector('.date-picker-icon');

        if (!hiddenInput || !displayInput) {
            return;
        }

        var panel = null;
        var vistaAnio = new Date().getFullYear();
        var vistaMes = new Date().getMonth();

        // Valor inicial en formato dd/mm/aaaa.
        displayInput.value = formatearDisplay(hiddenInput.value);

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
            if (panel) {
                panel.remove();
            }
            document.removeEventListener('click', manejarClickFuera);

            panel = document.createElement('div');
            panel.className = 'date-picker-panel';

            var header = document.createElement('div');
            header.className = 'date-picker-header';

            function crearNav(texto, titulo, onClick) {
                var btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'date-picker-nav';
                btn.textContent = texto;
                btn.title = titulo;
                btn.setAttribute('aria-label', titulo);
                btn.addEventListener('click', function (e) {
                    e.stopPropagation();
                    onClick();
                });
                return btn;
            }

            var btnAnioAnt = crearNav('\u00ab', 'Año anterior', function () {
                vistaAnio--;
                renderPanel();
            });

            var btnMesAnt = crearNav('\u2039', 'Mes anterior', function () {
                vistaMes--;
                if (vistaMes < 0) {
                    vistaMes = 11;
                    vistaAnio--;
                }
                renderPanel();
            });

            var selectMes = document.createElement('select');
            selectMes.className = 'date-picker-select date-picker-select--mes';
            selectMes.setAttribute('aria-label', 'Mes');
            NOMBRES_MES.forEach(function (nombre, indice) {
                var opcion = document.createElement('option');
                opcion.value = String(indice);
                opcion.textContent = nombre;
                selectMes.appendChild(opcion);
            });
            selectMes.value = String(vistaMes);
            selectMes.addEventListener('change', function (e) {
                e.stopPropagation();
                vistaMes = Number(selectMes.value);
                renderPanel();
            });

            var selectAnio = document.createElement('select');
            selectAnio.className = 'date-picker-select date-picker-select--anio';
            selectAnio.setAttribute('aria-label', 'Año');
            var rango = obtenerRangoAnios(wrapper);
            for (var anio = rango.max; anio >= rango.min; anio--) {
                var opcion = document.createElement('option');
                opcion.value = String(anio);
                opcion.textContent = String(anio);
                selectAnio.appendChild(opcion);
            }
            selectAnio.value = String(vistaAnio);
            selectAnio.addEventListener('change', function (e) {
                e.stopPropagation();
                vistaAnio = Number(selectAnio.value);
                renderPanel();
            });

            var btnMesSig = crearNav('\u203a', 'Mes siguiente', function () {
                vistaMes++;
                if (vistaMes > 11) {
                    vistaMes = 0;
                    vistaAnio++;
                }
                renderPanel();
            });

            var btnAnioSig = crearNav('\u00bb', 'Año siguiente', function () {
                vistaAnio++;
                renderPanel();
            });

            header.append(btnAnioAnt, btnMesAnt, selectMes, selectAnio, btnMesSig, btnAnioSig);

            var grid = document.createElement('div');
            grid.className = 'date-picker-grid';

            NOMBRES_DIA.forEach(function (dia) {
                var celdaDia = document.createElement('span');
                celdaDia.className = 'date-picker-dow';
                celdaDia.textContent = dia;
                grid.appendChild(celdaDia);
            });

            var primerDia = new Date(vistaAnio, vistaMes, 1);
            var offset = (primerDia.getDay() + 6) % 7; // lunes=0 ... domingo=6
            var diasEnMes = new Date(vistaAnio, vistaMes + 1, 0).getDate();
            var hoyIso = formatearISO(new Date());
            var minISO = wrapper.getAttribute('data-min') || '';
            var maxISO = wrapper.getAttribute('data-max') || '';

            for (let i = 0; i < offset; i++) {
                grid.appendChild(document.createElement('span'));
            }

            for (let dia = 1; dia <= diasEnMes; dia++) {
                const fecha = new Date(vistaAnio, vistaMes, dia);
                const iso = formatearISO(fecha);
                const fueraRango = (minISO && iso < minISO) || (maxISO && iso > maxISO);

                const boton = document.createElement('button');
                boton.type = 'button';
                boton.className = 'date-picker-day';
                boton.textContent = String(dia);

                if (iso === hoyIso) {
                    boton.classList.add('date-picker-day--hoy');
                }
                if (iso === hiddenInput.value) {
                    boton.classList.add('date-picker-day--seleccionado');
                }

                if (fueraRango) {
                    boton.disabled = true;
                    boton.classList.add('date-picker-day--disabled');
                } else {
                    boton.addEventListener('click', function () {
                        const texto = pad2(dia) + '/' + pad2(vistaMes + 1) + '/' + vistaAnio;
                        seleccionarFecha(iso, texto);
                    });
                }

                grid.appendChild(boton);
            }

            panel.append(header, grid);
            wrapper.appendChild(panel);

            setTimeout(function () {
                document.addEventListener('click', manejarClickFuera);
            }, 0);
        }

        function abrirPanel() {
            if (panel) {
                cerrarPanel();
                return;
            }

            if (hiddenInput.value) {
                var partes = hiddenInput.value.split('-').map(Number);
                vistaAnio = partes[0];
                vistaMes = partes[1] - 1;
            }
            renderPanel();
        }

        displayInput.addEventListener('click', abrirPanel);
        if (icono) {
            icono.addEventListener('click', abrirPanel);
        }

        // Permite escribir la fecha a mano (dd/mm/aaaa) además de usar el calendario.
        displayInput.addEventListener('change', function () {
            var iso = parseDisplay(displayInput.value);
            var minISO = wrapper.getAttribute('data-min') || '';
            var maxISO = wrapper.getAttribute('data-max') || '';
            if (iso && (!minISO || iso >= minISO) && (!maxISO || iso <= maxISO)) {
                hiddenInput.value = iso;
                displayInput.value = formatearDisplay(iso);
            } else if (displayInput.value.trim() === '') {
                hiddenInput.value = '';
            } else {
                displayInput.value = formatearDisplay(hiddenInput.value);
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.date-picker').forEach(initDatePicker);
    });
})();
