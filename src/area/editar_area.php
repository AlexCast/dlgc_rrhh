<?php

$moduleId = 8;
$requiredAction = 'actualizar';
require_once __DIR__ . '/../../app/src_guard.php';
if (!isset($_GET['id_area'])) {
    echo 'No existe el registro a editar';
    exit();
}

$id_area = (int) $_GET['id_area'];

if ($id_area <= 0) {
    echo 'ID invalido';
    exit();
}

require_once __DIR__ . '/../../app/conexion.php';

$sentencia = $conexion->prepare('
    SELECT id_area, nombre_area
    FROM t_areas
    WHERE id_area = ?
      AND fec_delete IS NULL
');
$sentencia->execute([$id_area]);
$registro = $sentencia->fetch(PDO::FETCH_OBJ);

if (!$registro) {
    echo 'No se encontro la area activa';
    exit();
}
?>

<?php include_once 'encab_area.php'; ?>
<?php require_once __DIR__ . '/../../app/alert_helper.php'; ?>
<?php render_alert_banner(); ?>


<main class="main-container">
    <section class="editor-shell">
        <div class="editor-card">
            <header class="editor-header">
                <h1 class="editor-title">Editar Área</h1>
                <p class="editor-subtitle">Actualiza la Área y guarda los cambios de forma segura.</p>
            </header>

            <div class="editor-body">
                <form action="update_area.php" method="POST" novalidate data-validate>
                    <?php echo csrf_input(); ?>

                    <input type="hidden" name="id_area" value="<?php echo (int) $registro->id_area; ?>">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="field-wrap">
                                <label for="nombre_area" class="form-label">Nombre Área</label>
                                <div class="input-group">
                                    <span class="input-group-text">Área</span>
                                    <input
                                        type="text"
                                        name="nombre_area"
                                        id="nombre_area"
                                        class="form-control"
                                        required
                                        maxlength="50"
                                        value="<?php echo htmlspecialchars((string) $registro->nombre_area, ENT_QUOTES, 'UTF-8'); ?>"
                                        placeholder="Ej: Área de Recursos Humanos"
                                    >
                                </div>
                                <p class="field-tip">Ingresa el nombre de la Área.</p>
                            </div>
                        </div>
                    </div>

                    <div class="cta-row">
                        <a href="listar_area.php" class="btn btn-soft">Cancelar y volver</a>
                        <button type="submit" class="btn btn-save">Guardar cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </section>
</main>

<?php include_once 'pie_area.php'; ?>
