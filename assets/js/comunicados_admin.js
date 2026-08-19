(function () {
    'use strict';

    function expandirComunicado(boton) {
        var articulo = boton.closest('.comunicado-card');
        var parrafo = articulo ? articulo.querySelector('.comunicado-content') : null;

        if (!articulo || !parrafo) {
            return;
        }

        var textoCompleto = parrafo.dataset.fullText || parrafo.textContent;
        var decoder = document.createElement('textarea');
        decoder.innerHTML = textoCompleto;
        parrafo.innerHTML = decoder.value;
        parrafo.classList.remove('comunicado-truncado');
        boton.remove();
    }

    function initVerMas() {
        document.addEventListener('click', function (event) {
            var boton = event.target.closest('.btn-ver-mas');
            if (!boton) {
                return;
            }
            event.preventDefault();
            expandirComunicado(boton);
        });
    }

    function initVistoPorModal() {
        var grid = document.querySelector('.comunicados-admin-grid');
        var modal = document.getElementById('modalVistoPor');

        if (!grid || !modal) {
            return;
        }

        var csrfToken = grid.dataset.csrfToken || '';
        var lista = document.getElementById('vistoPorLista');
        var loader = document.getElementById('vistoPorLoader');
        var errorBox = document.getElementById('vistoPorError');
        var info = document.getElementById('vistoPorComunicadoInfo');

        function abrirModal() {
            modal.classList.add('is-open');
            modal.setAttribute('aria-hidden', 'false');
            document.body.classList.add('modal-open');
        }

        function cerrarModal() {
            modal.classList.remove('is-open');
            modal.setAttribute('aria-hidden', 'true');
            document.body.classList.remove('modal-open');
            lista.innerHTML = '';
            loader.style.display = 'none';
            errorBox.style.display = 'none';
            errorBox.textContent = '';
        }

        function renderizarLectores(lectores) {
            lista.innerHTML = '';

            if (lectores.length === 0) {
                errorBox.textContent = 'Nadie ha visto este comunicado aún.';
                errorBox.style.display = 'block';
                return;
            }

            lectores.forEach(function (lector) {
                var li = document.createElement('li');
                li.className = 'lector-item';

                var avatar = document.createElement('div');
                avatar.className = 'lector-avatar';

                if (lector.foto_perfil) {
                    var img = document.createElement('img');
                    img.src = '/dlgc_rrhh/uploads/perfiles/' + encodeURIComponent(lector.foto_perfil);
                    img.alt = lector.nombre_completo;
                    avatar.appendChild(img);
                } else {
                    var icon = document.createElement('i');
                    icon.className = 'fas fa-user';
                    icon.setAttribute('aria-hidden', 'true');
                    avatar.appendChild(icon);
                }

                var info = document.createElement('div');
                info.className = 'lector-info';

                var nombre = document.createElement('span');
                nombre.className = 'lector-name';
                nombre.textContent = lector.nombre_completo;

                var fecha = document.createElement('span');
                fecha.className = 'lector-time';
                fecha.textContent = lector.fec_visto
                    ? formatearFecha(lector.fec_visto)
                    : 'Fecha desconocida';

                info.appendChild(nombre);
                info.appendChild(fecha);

                li.appendChild(avatar);
                li.appendChild(info);
                lista.appendChild(li);
            });
        }

        function formatearFecha(fechaIso) {
            var fecha = new Date(fechaIso.replace(' ', 'T'));
            if (isNaN(fecha.getTime())) {
                return fechaIso;
            }
            return fecha.toLocaleString('es-CO', {
                day: '2-digit',
                month: 'short',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }

        function cargarLectores(idComunicado, titulo) {
            if (info) {
                info.textContent = titulo;
            }
            lista.innerHTML = '';
            errorBox.style.display = 'none';
            loader.style.display = 'flex';
            abrirModal();

            var formData = new URLSearchParams();
            formData.append('csrf_token', csrfToken);
            formData.append('id_comunicado', idComunicado);

            fetch('/dlgc_rrhh/src/comunicados/visto_por.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData.toString()
            })
            .then(function (response) { return response.json(); })
            .then(function (data) {
                loader.style.display = 'none';
                if (data.ok) {
                    renderizarLectores(data.lectores);
                } else {
                    errorBox.textContent = data.error || 'No se pudo cargar la lista de lectores.';
                    errorBox.style.display = 'block';
                }
            })
            .catch(function () {
                loader.style.display = 'none';
                errorBox.textContent = 'Error de conexión al cargar los lectores.';
                errorBox.style.display = 'block';
            });
        }

        grid.addEventListener('click', function (event) {
            var boton = event.target.closest('.btn-visto-por');
            if (!boton) {
                return;
            }
            event.preventDefault();
            var idComunicado = boton.dataset.idComunicado;
            var titulo = boton.dataset.tituloComunicado || 'Comunicado';
            if (idComunicado) {
                cargarLectores(idComunicado, titulo);
            }
        });

        var closeButtons = modal.querySelectorAll('[data-modal-close], .btn-close');
        closeButtons.forEach(function (button) {
            button.addEventListener('click', cerrarModal);
        });

        modal.addEventListener('click', function (event) {
            if (event.target === modal) {
                cerrarModal();
            }
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && modal.classList.contains('is-open')) {
                cerrarModal();
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        initVerMas();
        initVistoPorModal();
    });
})();
