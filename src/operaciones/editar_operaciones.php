<?php

$moduleId = 19;
$requiredAction = 'actualizar';
require_once __DIR__ . '/../../app/src_guard.php';
if (!isset($_GET['id_operacion']) || trim($_GET['id_operacion']) === '') {
    echo 'No existe el registro a editar';
    exit();
}

$id_operacion = trim($_GET['id_operacion']);

require_once __DIR__ . '/../../app/conexion.php';

$sentencia = $conexion->prepare('
    SELECT *
    FROM t_operaciones
    WHERE id_operacion = ?
      AND fec_delete IS NULL
');
$sentencia->execute([$id_operacion]);
$registro = $sentencia->fetch(PDO::FETCH_OBJ);

if (!$registro) {
    echo 'No se encontró el registro activo';
    exit();
}

$sentencia = $conexion->query("
    SELECT id_modulo, nombre_modulo
    FROM t_modulos
    WHERE fec_delete IS NULL
    ORDER BY nombre_modulo, id_modulo
");
$t_modulos_registros = $sentencia->fetchAll(PDO::FETCH_OBJ);

?>

<?php include_once 'encab_operaciones.php'; ?>
<?php require_once __DIR__ . '/../../app/alert_helper.php'; ?>
<?php render_alert_banner(); ?>


<main class="main-container">
    <section class="editor-shell">
        <div class="editor-card">
            <header class="editor-header">
                <h1 class="editor-title">Editar Operacione</h1>
                <p class="editor-subtitle">Actualiza la información y guarda los cambios.</p>
            </header>

            <div class="editor-body">
                <form action="update_operaciones.php" method="POST" novalidate data-validate>
                    <?php echo csrf_input(); ?>

                    <input type="hidden" name="id_operacion" value="<?php echo htmlspecialchars((string) $registro->id_operacion, ENT_QUOTES, 'UTF-8'); ?>">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="field-wrap">
                                <label for="id_operacion" class="form-label">ID Operación</label>
                                <div class="input-group">
                                    <span class="input-group-text">ID O</span>
                                    <input
                                        type="number"
                                        name="id_operacion"
                                        id="id_operacion"
                                        class="form-control"
                                         required min="1" step="1" value="<?php echo htmlspecialchars((string) $registro->id_operacion, ENT_QUOTES, 'UTF-8'); ?>"
                                    >
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="field-wrap">
                                <label for="id_modulo" class="form-label">Módulo</label>
                                <div class="input-group">
                                    <span class="input-group-text">Módu</span>
                                    <select name="id_modulo" id="id_modulo" class="form-select" required>
                                        <option value="" disabled>Seleccione módulo</option>
                                        <?php foreach ($t_modulos_registros as $item): ?>
                                            <option value="<?php echo htmlspecialchars((string) $item->id_modulo, ENT_QUOTES, 'UTF-8'); ?>" <?php echo (string) $item->id_modulo === (string) $registro->id_modulo ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars((string) $item->nombre_modulo, ENT_QUOTES, 'UTF-8'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="field-wrap">
                                <label for="nombre_operacion" class="form-label">Nombre Operación</label>
                                <div class="input-group">
                                    <span class="input-group-text">Nomb</span>
                                    <input
                                        type="text"
                                        name="nombre_operacion"
                                        id="nombre_operacion"
                                        class="form-control"
                                         required maxlength="50" value="<?php echo htmlspecialchars((string) $registro->nombre_operacion, ENT_QUOTES, 'UTF-8'); ?>"
                                    >
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="cta-row">
                        <a href="listar_operaciones.php" class="btn btn-soft">Cancelar y volver</a>
                        <button type="submit" class="btn btn-save">Guardar cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </section>
</main>

<?php include_once 'pie_operaciones.php'; ?>
