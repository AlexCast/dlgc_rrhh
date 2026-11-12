<?php

$moduleId = 21;
require_once __DIR__ . '/../../app/src_guard.php';
require_once __DIR__ . '/../../app/conexion.php';

$usuarios = $conexion->query("
    SELECT id_usuario,
           username,
           CONCAT_WS(' ', primer_nombre, segundo_nombre, primer_apellido, segundo_apellido) AS nombre_usuario
    FROM t_usuarios
    WHERE fec_delete IS NULL
    ORDER BY nombre_usuario, username, id_usuario
")->fetchAll(PDO::FETCH_OBJ);

$operaciones = $conexion->query("
    SELECT o.id_operacion, o.nombre_operacion, m.id_modulo, m.nombre_modulo
    FROM t_operaciones o
    INNER JOIN t_modulos m ON m.id_modulo = o.id_modulo AND m.fec_delete IS NULL
    WHERE o.fec_delete IS NULL
    ORDER BY m.id_modulo, o.id_operacion
")->fetchAll(PDO::FETCH_OBJ);

$modulosOperaciones = [];
foreach ($operaciones as $op) {
    $claveModulo = (int) $op->id_modulo;
    if (!isset($modulosOperaciones[$claveModulo])) {
        $modulosOperaciones[$claveModulo] = [
            'nombre' => $op->nombre_modulo,
            'operaciones' => [],
        ];
    }
    $modulosOperaciones[$claveModulo]['operaciones'][] = $op;
}
?>

<?php include_once 'encab_permisos_usuarios.php'; ?>
<?php require_once __DIR__ . '/../../app/alert_helper.php'; ?>
<?php render_alert_banner(); ?>


<main class="main-container">
    <section class="editor-shell">
        <div class="editor-card">
            <header class="editor-header">
                <h1 class="editor-title">Registrar Permisos por Usuario</h1>
                <p class="editor-subtitle">Selecciona el usuario y marca una o varias operaciones para asignarlas en un solo paso.</p>
            </header>

            <div class="editor-body">
                <form action="insertar_permisos_usuarios.php" method="POST" novalidate data-validate>
                    <?php echo csrf_input(); ?>

                    <div class="row g-3">
                        <div class="col-md-6 field-wrap-usuario">
                            <div class="field-wrap">
                                <label for="busqueda_usuario" class="form-label">Usuario</label>
                                <div class="input-group">
                                    <span class="input-group-text">USR</span>
                                    <input type="text"
                                           id="busqueda_usuario"
                                           class="form-control"
                                           list="datalist-usuarios"
                                           placeholder="Escriba nombre o usuario..."
                                           autocomplete="off"
                                           required>
                                    <input type="hidden" name="id_usuario" id="id_usuario">
                                </div>
                                <datalist id="datalist-usuarios">
                                    <?php foreach ($usuarios as $u): ?>
                                        <?php
                                            $etiquetaUsuario = ($u->nombre_usuario ?? $u->username ?? $u->id_usuario) . ' (' . $u->username . ')';
                                        ?>
                                        <option data-id="<?php echo htmlspecialchars((string) $u->id_usuario, ENT_QUOTES, 'UTF-8'); ?>"
                                                value="<?php echo htmlspecialchars((string) $etiquetaUsuario, ENT_QUOTES, 'UTF-8'); ?>">
                                        </option>
                                    <?php endforeach; ?>
                                </datalist>
                                <small class="field-tip">Empiece a escribir el nombre o el usuario para buscar.</small>
                            </div>
                        </div>

                        <div class="col-12 field-wrap-operaciones">
                            <div class="field-wrap">
                                <label class="form-label">Operaciones
                                    <span class="op-contador" id="contador_operaciones">0 seleccionadas</span>
                                </label>
                                <div class="op-selector">
                                    <div class="input-group op-buscador">
                                        <span class="input-group-text"><i class="fas fa-filter" aria-hidden="true"></i></span>
                                        <input type="text"
                                               id="filtro_operaciones"
                                               class="form-control"
                                               placeholder="Filtrar por módulo u operación..."
                                               autocomplete="off">
                                    </div>
                                    <label class="op-select-all">
                                        <input type="checkbox" id="check_todas">
                                        <span>Seleccionar todas las visibles</span>
                                    </label>
                                    <div class="op-list" id="lista_operaciones" tabindex="0">
                                        <?php foreach ($modulosOperaciones as $idModulo => $modulo): ?>
                                            <?php
                                                $nombreModulo = htmlspecialchars((string) $modulo['nombre'], ENT_QUOTES, 'UTF-8');
                                            ?>
                                            <div class="op-modulo" data-texto="<?php echo $idModulo . ' ' . $nombreModulo; ?>">
                                                <label class="op-modulo-title">
                                                    <input type="checkbox" class="check-modulo" data-modulo="<?php echo $idModulo; ?>">
                                                    <span>[<?php echo $idModulo; ?>] <?php echo $nombreModulo; ?></span>
                                                </label>
                                                <?php foreach ($modulo['operaciones'] as $op): ?>
                                                    <?php
                                                        $textoOperacion = $nombreModulo . ' ' . htmlspecialchars((string) $op->nombre_operacion, ENT_QUOTES, 'UTF-8');
                                                    ?>
                                                    <label class="op-item" data-texto="<?php echo $textoOperacion; ?>">
                                                        <input type="checkbox" name="id_operaciones[]" value="<?php echo (int) $op->id_operacion; ?>">
                                                        <span><?php echo htmlspecialchars((string) $op->nombre_operacion, ENT_QUOTES, 'UTF-8'); ?></span>
                                                    </label>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <small class="field-tip">Marque una o varias operaciones; puede filtrar y usar "Seleccionar todas las visibles".</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="cta-row">
                        <a href="listar_permisos_usuarios.php" class="btn btn-soft">Cancelar y volver</a>
                        <button type="submit" class="btn btn-save">Registrar permisos</button>
                    </div>
                </form>
            </div>
        </div>
    </section>
</main>

<style>
    .field-wrap-usuario {
        position: relative;
        z-index: 10;
    }

    .field-wrap-operaciones {
        position: relative;
        z-index: 5;
    }

    .op-contador {
        font-weight: 400;
        font-size: 0.85em;
        color: var(--mnt-text-muted);
        margin-left: 0.5rem;
    }

    .op-selector {
        border: 1px solid var(--mnt-border);
        border-radius: var(--mnt-radius-md);
        padding: 0.85rem;
        background: var(--mnt-surface);
    }

    .op-buscador {
        margin-bottom: 0.75rem;
    }

    .op-select-all {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-weight: 600;
        cursor: pointer;
        margin-bottom: 0.6rem;
        color: var(--mnt-text);
    }

    .op-list {
        max-height: 340px;
        overflow-y: auto;
        border: 1px solid var(--mnt-border);
        border-radius: var(--mnt-radius-sm);
        padding: 0.6rem;
        background: var(--mnt-bg);
    }

    .op-modulo {
        margin-bottom: 0.55rem;
        padding: 0.35rem 0.5rem;
        border-radius: var(--mnt-radius-sm);
        background: var(--mnt-surface);
        border: 1px solid var(--mnt-border);
    }

    .op-modulo:last-child {
        margin-bottom: 0;
    }

    .op-modulo-title {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-weight: 700;
        cursor: pointer;
        padding: 0.2rem 0 0.35rem;
        color: var(--mnt-text);
    }

    .op-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-left: 1.3rem;
        padding: 0.12rem 0;
        cursor: pointer;
        color: var(--mnt-text);
    }

    .op-item:hover,
    .op-modulo-title:hover {
        color: var(--mnt-primary-strong);
    }

    .op-selector input[type="checkbox"] {
        width: 1rem;
        height: 1rem;
        flex-shrink: 0;
        accent-color: var(--mnt-primary);
        cursor: pointer;
    }
</style>

<script>
(function () {
    function vincularDatalist(inputId, hiddenId) {
        var input = document.getElementById(inputId);
        var hidden = document.getElementById(hiddenId);
        if (!input || !hidden) return;
        var datalist = document.getElementById(input.getAttribute('list'));
        if (!datalist) return;

        function sincronizar() {
            var opciones = datalist.querySelectorAll('option');
            var id = '';
            for (var i = 0; i < opciones.length; i++) {
                if (opciones[i].value === input.value) {
                    id = opciones[i].getAttribute('data-id') || '';
                    break;
                }
            }
            hidden.value = id;
        }

        input.addEventListener('input', sincronizar);
        input.addEventListener('change', sincronizar);
    }

    document.addEventListener('DOMContentLoaded', function () {
        vincularDatalist('busqueda_usuario', 'id_usuario');

        var filtro = document.getElementById('filtro_operaciones');
        var lista = document.getElementById('lista_operaciones');
        var checkTodas = document.getElementById('check_todas');
        var contador = document.getElementById('contador_operaciones');
        if (!filtro || !lista || !checkTodas || !contador) return;

        function itemsVisibles() {
            return Array.prototype.slice.call(lista.querySelectorAll('.op-item')).filter(function (item) {
                return item.style.display !== 'none';
            });
        }

        function actualizarContador() {
            var marcadas = lista.querySelectorAll('.op-item input:checked').length;
            contador.textContent = marcadas + (marcadas === 1 ? ' seleccionada' : ' seleccionadas');
        }

        function actualizarEstadoGlobal() {
            var visibles = itemsVisibles();
            var marcadas = visibles.filter(function (item) {
                return item.querySelector('input').checked;
            }).length;
            if (marcadas === 0) {
                checkTodas.checked = false;
                checkTodas.indeterminate = false;
            } else if (marcadas === visibles.length && visibles.length > 0) {
                checkTodas.checked = true;
                checkTodas.indeterminate = false;
            } else {
                checkTodas.checked = false;
                checkTodas.indeterminate = true;
            }
        }

        function actualizarEstadoModulos() {
            lista.querySelectorAll('.op-modulo').forEach(function (modulo) {
                var checkModulo = modulo.querySelector('.check-modulo');
                if (!checkModulo) return;
                var items = modulo.querySelectorAll('.op-item input');
                var marcadas = Array.prototype.slice.call(items).filter(function (it) {
                    return it.checked;
                }).length;
                if (marcadas === 0) {
                    checkModulo.checked = false;
                    checkModulo.indeterminate = false;
                } else if (marcadas === items.length) {
                    checkModulo.checked = true;
                    checkModulo.indeterminate = false;
                } else {
                    checkModulo.checked = false;
                    checkModulo.indeterminate = true;
                }
            });
        }

        filtro.addEventListener('input', function () {
            var texto = filtro.value.trim().toLowerCase();
            lista.querySelectorAll('.op-modulo').forEach(function (modulo) {
                var algunoVisible = false;
                modulo.querySelectorAll('.op-item').forEach(function (item) {
                    var coincide = (item.getAttribute('data-texto') || '').toLowerCase().indexOf(texto) !== -1;
                    item.style.display = coincide ? '' : 'none';
                    if (coincide) algunoVisible = true;
                });
                modulo.style.display = (algunoVisible || texto === '') ? '' : 'none';
            });
            actualizarEstadoGlobal();
        });

        checkTodas.addEventListener('change', function () {
            var marcar = checkTodas.checked;
            itemsVisibles().forEach(function (item) {
                item.querySelector('input').checked = marcar;
            });
            actualizarEstadoModulos();
            actualizarContador();
        });

        lista.addEventListener('change', function (e) {
            if (e.target && e.target.classList.contains('check-modulo')) {
                var modulo = e.target.closest('.op-modulo');
                if (modulo) {
                    modulo.querySelectorAll('.op-item').forEach(function (item) {
                        if (item.style.display !== 'none') {
                            item.querySelector('input').checked = e.target.checked;
                        }
                    });
                }
            }
            actualizarEstadoGlobal();
            actualizarEstadoModulos();
            actualizarContador();
        });

        actualizarContador();

        var form = document.querySelector('form[data-validate]');
        if (!form) return;

        form.addEventListener('submit', function (e) {
            var inputUsuario = document.getElementById('busqueda_usuario');
            var hiddenUsuario = form.querySelector('input[name="id_usuario"]');

            if (!inputUsuario || inputUsuario.value.trim() === '' || (!hiddenUsuario || hiddenUsuario.value === '')) {
                e.preventDefault();
                if (window.AlertBanner) {
                    AlertBanner.show('danger', 'Seleccione un usuario válido de las sugerencias.');
                }
                if (inputUsuario) inputUsuario.focus();
                return;
            }

            var marcadas = lista.querySelectorAll('.op-item input:checked').length;
            if (marcadas === 0) {
                e.preventDefault();
                if (window.AlertBanner) {
                    AlertBanner.show('danger', 'Seleccione al menos una operación.');
                }
                filtro.focus();
                return;
            }
        });
    });
})();
</script>
<?php include_once 'pie_permisos_usuarios.php'; ?>
