<?php

$moduleId = 21;
$requiredAction = 'actualizar';
require_once __DIR__ . '/../../app/src_guard.php';
if (!isset($_GET['id_usuario']) || !isset($_GET['id_operacion'])) {
    echo 'No existe el registro a editar';
    exit();
}

$id_usuario = trim($_GET['id_usuario']);
$id_operacion = (int) $_GET['id_operacion'];

if ($id_usuario === '' || $id_operacion <= 0) {
    echo 'IDs invalidos';
    exit();
}

require_once __DIR__ . '/../../app/conexion.php';

$sentencia = $conexion->prepare('
    SELECT id_usuario, id_operacion
    FROM t_usuarios_operaciones
    WHERE id_usuario = ?
      AND id_operacion = ?
      AND fec_delete IS NULL
');
$sentencia->execute([$id_usuario, $id_operacion]);
$registro = $sentencia->fetch(PDO::FETCH_OBJ);

if (!$registro) {
    echo 'No se encontro el permiso activo';
    exit();
}

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

<main class="main-container">
    <section class="editor-shell">
        <div class="editor-card">
            <header class="editor-header">
                <h1 class="editor-title">Editar Permiso Individual</h1>
                <p class="editor-subtitle">Actualiza el usuario y/o la operación asignada.</p>
            </header>

            <div class="editor-body">
                <form action="update_permisos_usuarios.php" method="POST" novalidate>
                    <?php echo csrf_input(); ?>

                    <input type="hidden" name="id_usuario_actual" value="<?php echo htmlspecialchars((string) $registro->id_usuario, ENT_QUOTES, 'UTF-8'); ?>">
                    <input type="hidden" name="id_operacion_actual" value="<?php echo (int) $registro->id_operacion; ?>">

                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="field-wrap">
                                <label for="id_usuario_nuevo" class="form-label">Usuario</label>
                                <div class="input-group">
                                    <span class="input-group-text">USR</span>
                                    <select name="id_usuario_nuevo" id="id_usuario_nuevo" class="form-select" required>
                                        <option value="" disabled>Seleccione un usuario</option>
                                        <?php foreach ($usuarios as $u): ?>
                                            <option value="<?php echo htmlspecialchars((string) $u->id_usuario, ENT_QUOTES, 'UTF-8'); ?>" <?php echo (string) $u->id_usuario === (string) $registro->id_usuario ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars((string) ($u->nombre_usuario ?? $u->username ?? $u->id_usuario), ENT_QUOTES, 'UTF-8'); ?> (<?php echo htmlspecialchars((string) $u->id_usuario, ENT_QUOTES, 'UTF-8'); ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="field-wrap">
                                <label for="id_operacion_nueva" class="form-label">Operación</label>
                                <div class="input-group">
                                    <span class="input-group-text">OP</span>
                                    <select name="id_operacion_nueva" id="id_operacion_nueva" class="form-select" required>
                                        <option value="" disabled>Seleccione una operación</option>
                                        <?php
                                        $moduloActual = null;
                                        foreach ($operaciones as $op):
                                            $seleccionado = (int) $op->id_operacion === (int) $registro->id_operacion;
                                            if ($moduloActual !== $op->nombre_modulo):
                                                if ($moduloActual !== null) echo '</optgroup>';
                                                $moduloActual = $op->nombre_modulo;
                                                echo '<optgroup label="' . htmlspecialchars((string) $moduloActual, ENT_QUOTES, 'UTF-8') . '">';
                                            endif;
                                        ?>
                                            <option value="<?php echo (int) $op->id_operacion; ?>" <?php echo $seleccionado ? 'selected' : ''; ?>>
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
                        <button type="submit" class="btn btn-save">Guardar cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </section>
</main>

<?php include_once 'pie_permisos_usuarios.php'; ?>
