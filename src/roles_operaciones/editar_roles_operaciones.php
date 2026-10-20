<?php

$moduleId = 6;
$requiredAction = 'actualizar';
require_once __DIR__ . '/../../app/src_guard.php';
if (!isset($_GET['id_rol']) || !isset($_GET['id_operacion'])) {
    echo 'No existe el registro a editar';
    exit();
}

$id_rol = (int) $_GET['id_rol'];
$id_operacion = (int) $_GET['id_operacion'];

if ($id_rol <= 0 || $id_operacion <= 0) {
    echo 'IDs invalidos';
    exit();
}

require_once __DIR__ . '/../../app/conexion.php';

$sentencia = $conexion->prepare('
    SELECT ro.id_rol, ro.id_operacion
    FROM t_roles_operaciones ro
    WHERE ro.id_rol = ?
      AND ro.id_operacion = ?
      AND ro.fec_delete IS NULL
');
$sentencia->execute([$id_rol, $id_operacion]);
$registro = $sentencia->fetch(PDO::FETCH_OBJ);

if (!$registro) {
    echo 'No se encontro la relacion activa';
    exit();
}

$roles = $conexion->query("SELECT id_rol, nombre_rol FROM t_roles WHERE fec_delete IS NULL ORDER BY nombre_rol, id_rol")
                   ->fetchAll(PDO::FETCH_OBJ);

$operaciones = $conexion->query("
    SELECT o.id_operacion, o.nombre_operacion, m.id_modulo, m.nombre_modulo
    FROM t_operaciones o
    INNER JOIN t_modulos m ON m.id_modulo = o.id_modulo AND m.fec_delete IS NULL
    WHERE o.fec_delete IS NULL
    ORDER BY m.nombre_modulo, o.nombre_operacion, o.id_operacion
")->fetchAll(PDO::FETCH_OBJ);
?>

<?php include_once 'encab_roles_operaciones.php'; ?>
<?php require_once __DIR__ . '/../../app/alert_helper.php'; ?>
<?php render_alert_banner(); ?>


<main class="main-container">
    <section class="editor-shell">
        <div class="editor-card">
            <header class="editor-header">
                <h1 class="editor-title">Editar relacion Rol - Operacion</h1>
                <p class="editor-subtitle">Actualiza los IDs y guarda los cambios de forma segura.</p>
            </header>

            <div class="editor-body">
                <form action="update_roles_operaciones.php" method="POST" novalidate data-validate>
                    <?php echo csrf_input(); ?>

                    <input type="hidden" name="id_rol_actual" value="<?php echo (int) $registro->id_rol; ?>">
                    <input type="hidden" name="id_operacion_actual" value="<?php echo (int) $registro->id_operacion; ?>">

                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="field-wrap">
                                <label for="id_rol_nuevo" class="form-label">Rol</label>
                                <div class="input-group">
                                    <span class="input-group-text">ROL</span>
                                    <select name="id_rol_nuevo" id="id_rol_nuevo" class="form-select" required>
                                        <option value="" disabled>Seleccione un rol</option>
                                        <?php foreach ($roles as $rol): ?>
                                            <option value="<?php echo (int) $rol->id_rol; ?>" <?php echo (int) $rol->id_rol === (int) $registro->id_rol ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars((string) $rol->nombre_rol, ENT_QUOTES, 'UTF-8'); ?>
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
                        <a href="listar_roles_operaciones.php" class="btn btn-soft">Cancelar y volver</a>
                        <button type="submit" class="btn btn-save">Guardar cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </section>
</main>

<?php include_once 'pie_roles_operaciones.php'; ?>
