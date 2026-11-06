<?php

$moduleId = 2;
$requiredAction = 'actualizar';
require_once __DIR__ . '/../../app/src_guard.php';
if (!isset($_GET['id_soat'])) {
    echo 'No existe el registro a editar';
    exit();
}

$id_soat = (int) $_GET['id_soat'];

if ($id_soat <= 0) {
    echo 'ID invalido';
    exit();
}

require_once __DIR__ . '/../../app/conexion.php';

$sentencia = $conexion->prepare('
    SELECT id_soat, nombre_soat
    FROM t_soat
    WHERE id_soat = ?
      AND fec_delete IS NULL
');
$sentencia->execute([$id_soat]);
$registro = $sentencia->fetch(PDO::FETCH_OBJ);

if (!$registro) {
    echo 'No se encontro el SOAT activo';
    exit();
}
?>

<?php include_once 'encab_soat.php'; ?>
<?php require_once __DIR__ . '/../../app/alert_helper.php'; ?>
<?php render_alert_banner(); ?>


<main class="main-container">
    <section class="editor-shell">
        <div class="editor-card">
            <header class="editor-header">
                <h1 class="editor-title">Editar SOAT</h1>
                <p class="editor-subtitle">Actualiza el SOAT y guarda los cambios de forma segura.</p>
            </header>

            <div class="editor-body">
                <form action="update_soat.php" method="POST" novalidate data-validate>
                    <?php echo csrf_input(); ?>

                    <input type="hidden" name="id_soat" value="<?php echo (int) $registro->id_soat; ?>">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="field-wrap">
                                <label for="nombre_soat" class="form-label">Nombre SOAT</label>
                                <div class="input-group">
                                    <span class="input-group-text">SOAT</span>
                                    <input
                                        type="text"
                                        name="nombre_soat"
                                        id="nombre_soat"
                                        class="form-control"
                                        required
                                        maxlength="50"
                                        value="<?php echo htmlspecialchars((string) $registro->nombre_soat, ENT_QUOTES, 'UTF-8'); ?>"
                                        placeholder="Ej: SOAT"
                                    >
                                </div>
                                <p class="field-tip">Ingresa el nombre del SOAT.</p>
                            </div>
                        </div>
                    </div>

                    <div class="cta-row">
                        <a href="listar_soat.php" class="btn btn-soft">Cancelar y volver</a>
                        <button type="submit" class="btn btn-save">Guardar cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </section>
</main>

<?php include_once 'pie_soat.php'; ?>
