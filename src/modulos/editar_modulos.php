<?php

$moduleId = 18;
$requiredAction = 'actualizar';
require_once __DIR__ . '/../../app/src_guard.php';
if (!isset($_GET['id_modulo']) || trim($_GET['id_modulo']) === '') {
    echo 'No existe el registro a editar';
    exit();
}

$id_modulo = trim($_GET['id_modulo']);

require_once __DIR__ . '/../../app/conexion.php';

$sentencia = $conexion->prepare('
    SELECT *
    FROM t_modulos
    WHERE id_modulo = ?
      AND fec_delete IS NULL
');
$sentencia->execute([$id_modulo]);
$registro = $sentencia->fetch(PDO::FETCH_OBJ);

if (!$registro) {
    echo 'No se encontró el registro activo';
    exit();
}

?>

<?php include_once 'encab_modulos.php'; ?>
<?php require_once __DIR__ . '/../../app/alert_helper.php'; ?>
<?php render_alert_banner(); ?>


<main class="main-container">
    <section class="editor-shell">
        <div class="editor-card">
            <header class="editor-header">
                <h1 class="editor-title">Editar Módulo</h1>
                <p class="editor-subtitle">Actualiza la información y guarda los cambios.</p>
            </header>

            <div class="editor-body">
                <form action="update_modulos.php" method="POST" novalidate data-validate>
                    <?php echo csrf_input(); ?>

                    <input type="hidden" name="id_modulo" value="<?php echo htmlspecialchars((string) $registro->id_modulo, ENT_QUOTES, 'UTF-8'); ?>">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="field-wrap">
                                <label for="id_modulo" class="form-label">ID Módulo</label>
                                <div class="input-group">
                                    <span class="input-group-text">ID M</span>
                                    <input
                                        type="number"
                                        name="id_modulo"
                                        id="id_modulo"
                                        class="form-control"
                                         required min="1" step="1" value="<?php echo htmlspecialchars((string) $registro->id_modulo, ENT_QUOTES, 'UTF-8'); ?>"
                                    >
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="field-wrap">
                                <label for="nombre_modulo" class="form-label">Nombre Módulo</label>
                                <div class="input-group">
                                    <span class="input-group-text">Nomb</span>
                                    <input
                                        type="text"
                                        name="nombre_modulo"
                                        id="nombre_modulo"
                                        class="form-control"
                                         required maxlength="30" value="<?php echo htmlspecialchars((string) $registro->nombre_modulo, ENT_QUOTES, 'UTF-8'); ?>"
                                    >
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="cta-row">
                        <a href="listar_modulos.php" class="btn btn-soft">Cancelar y volver</a>
                        <button type="submit" class="btn btn-save">Guardar cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </section>
</main>

<?php include_once 'pie_modulos.php'; ?>
