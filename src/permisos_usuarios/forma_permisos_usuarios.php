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
    ORDER BY m.nombre_modulo, o.nombre_operacion, o.id_operacion
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
                        <div class="col-md-6">
                            <div class="field-wrap">
                                <label for="id_usuario" class="form-label">Usuario</label>
                                <div class="input-group">
                                    <span class="input-group-text">USR</span>
                                    <select name="id_usuario" id="id_usuario" class="form-select" required>
                                        <option value="" disabled selected>Seleccione un usuario</option>
                                        <?php foreach ($usuarios as $u): ?>
                                            <option value="<?php echo htmlspecialchars((string) $u->id_usuario, ENT_QUOTES, 'UTF-8'); ?>">
                                                <?php echo htmlspecialchars((string) ($u->nombre_usuario ?? $u->username ?? $u->id_usuario), ENT_QUOTES, 'UTF-8'); ?> (<?php echo htmlspecialchars((string) $u->id_usuario, ENT_QUOTES, 'UTF-8'); ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="field-wrap">
                                <label for="id_operacion" class="form-label">Operación</label>
                                <div class="input-group">
                                    <span class="input-group-text">OP</span>
                                    <select name="id_operacion" id="id_operacion" class="form-select" required>
                                        <option value="" disabled selected>Seleccione una operación</option>
                                        <?php
                                        $moduloActual = null;
                                        foreach ($operaciones as $op):
                                            if ($moduloActual !== $op->nombre_modulo):
                                                if ($moduloActual !== null) echo '</optgroup>';
                                                $moduloActual = $op->nombre_modulo;
                                                echo '<optgroup label="' . htmlspecialchars((string) $moduloActual, ENT_QUOTES, 'UTF-8') . '">';
                                            endif;
                                        ?>
                                            <option value="<?php echo (int) $op->id_operacion; ?>">
                                                <?php echo htmlspecialchars((string) $op->nombre_operacion, ENT_QUOTES, 'UTF-8'); ?>
                                            </option>
                                        <?php endforeach;
                                        if ($moduloActual !== null) echo '</optgroup>';
                                        ?>
                                    </select>
                                </div>
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

<?php include_once 'pie_permisos_usuarios.php'; ?>
