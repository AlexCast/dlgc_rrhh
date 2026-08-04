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
    ORDER BY m.id_modulo::integer, o.id_operacion
")->fetchAll(PDO::FETCH_OBJ);
?>

<?php include_once 'encab_permisos_usuarios.php'; ?>
<?php require_once __DIR__ . '/../../app/alert_helper.php'; ?>
<?php render_alert_banner(); ?>


<main class="main-container">
    <section class="editor-shell">
        <div class="editor-card">
            <header class="editor-header">
                <h1 class="editor-title">Registrar Permiso Individual</h1>
                <p class="editor-subtitle">Selecciona el usuario y la operación que deseas asignar.</p>
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
                                        <option data-id="<?php echo (int) $u->id_usuario; ?>"
                                                value="<?php echo htmlspecialchars((string) $etiquetaUsuario, ENT_QUOTES, 'UTF-8'); ?>">
                                        </option>
                                    <?php endforeach; ?>
                                </datalist>
                                <small class="field-tip">Empiece a escribir el nombre o el usuario para buscar.</small>
                            </div>
                        </div>

                        <div class="col-md-6 field-wrap-operacion">
                            <div class="field-wrap">
                                <label for="busqueda_operacion" class="form-label">Operación</label>
                                <div class="input-group">
                                    <span class="input-group-text">OP</span>
                                    <input type="text"
                                           id="busqueda_operacion"
                                           class="form-control"
                                           list="datalist-operaciones"
                                           placeholder="Escriba operación o módulo..."
                                           autocomplete="off"
                                           required>
                                    <input type="hidden" name="id_operacion" id="id_operacion">
                                </div>
                                <datalist id="datalist-operaciones">
                                    <?php foreach ($operaciones as $op): ?>
                                        <?php
                                            $etiquetaOperacion = '[' . $op->id_modulo . '] ' . $op->nombre_modulo . ' - ' . $op->nombre_operacion;
                                        ?>
                                        <option data-id="<?php echo (int) $op->id_operacion; ?>"
                                                value="<?php echo htmlspecialchars((string) $etiquetaOperacion, ENT_QUOTES, 'UTF-8'); ?>">
                                        </option>
                                    <?php endforeach; ?>
                                </datalist>
                                <small class="field-tip">Escriba la operación o el módulo para filtrar las sugerencias.</small>
                            </div>
                        </div>
                    </div>

                    <div class="cta-row">
                        <a href="listar_permisos_usuarios.php" class="btn btn-soft">Cancelar y volver</a>
                        <button type="submit" class="btn btn-save">Registrar permiso</button>
                    </div>
                </form>
            </div>
        </div>
    </section>
</main>

<style>
    .field-wrap-operacion {
        position: relative;
        z-index: 20;
    }

    .field-wrap-usuario {
        position: relative;
        z-index: 10;
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
        vincularDatalist('busqueda_operacion', 'id_operacion');

        var form = document.querySelector('form[data-validate]');
        if (!form) return;

        form.addEventListener('submit', function (e) {
            var inputUsuario = document.getElementById('busqueda_usuario');
            var inputOperacion = document.getElementById('busqueda_operacion');
            var hiddenUsuario = form.querySelector('input[name="id_usuario"]');
            var hiddenOperacion = form.querySelector('input[name="id_operacion"]');

            if (inputUsuario && inputUsuario.value.trim() !== '' && (!hiddenUsuario || hiddenUsuario.value === '')) {
                e.preventDefault();
                if (window.AlertBanner) {
                    AlertBanner.show('danger', 'Seleccione un usuario válido de las sugerencias.');
                }
                inputUsuario.focus();
                return;
            }

            if (inputOperacion && inputOperacion.value.trim() !== '' && (!hiddenOperacion || hiddenOperacion.value === '')) {
                e.preventDefault();
                if (window.AlertBanner) {
                    AlertBanner.show('danger', 'Seleccione una operación válida de las sugerencias.');
                }
                inputOperacion.focus();
                return;
            }
        });
    });
})();
</script>
<?php include_once 'pie_permisos_usuarios.php'; ?>
