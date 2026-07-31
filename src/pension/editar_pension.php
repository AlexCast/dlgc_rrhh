<?php

$moduleId = 16;
$requiredAction = 'actualizar';
require_once __DIR__ . '/../../app/src_guard.php';
if (!isset($_GET['id_pension'])) {
    echo 'No existe el registro a editar';
    exit();
}

$id_pension = (int) $_GET['id_pension'];

if ($id_pension <= 0) {
    echo 'ID invalido';
    exit();
}

require_once __DIR__ . '/../../app/conexion.php';

$sentencia = $conexion->prepare('
    SELECT id_pension, nombre_pension
    FROM t_pension
    WHERE id_pension = ?
      AND fec_delete IS NULL
');
$sentencia->execute([$id_pension]);
$registro = $sentencia->fetch(PDO::FETCH_OBJ);

if (!$registro) {
    echo 'No se encontro la pension activa';
    exit();
}
?>

<?php include_once 'encab_pension.php'; ?>
<?php require_once __DIR__ . '/../../app/alert_helper.php'; ?>
<?php render_alert_banner(); ?>


<main class="main-container">
    <section class="editor-shell">
        <div class="editor-card">
            <header class="editor-header">
                <h1 class="editor-title">Editar pension</h1>
                <p class="editor-subtitle">Actualiza la pension y guarda los cambios de forma segura.</p>
            </header>

            <div class="editor-body">
                <form action="update_pension.php" method="POST" novalidate data-validate>
                    <?php echo csrf_input(); ?>

                    <input type="hidden" name="id_pension" value="<?php echo (int) $registro->id_pension; ?>">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="field-wrap">
                                <label for="nombre_pension" class="form-label">Nombre pension</label>
                                <div class="input-group">
                                    <span class="input-group-text">pension</span>
                                    <input
                                        type="text"
                                        name="nombre_pension"
                                        id="nombre_pension"
                                        class="form-control"
                                        required
                                        maxlength="50"
                                        value="<?php echo htmlspecialchars((string) $registro->nombre_pension, ENT_QUOTES, 'UTF-8'); ?>"
                                        placeholder="Ej: pension Salud"
                                    >
                                </div>
                                <p class="field-tip">Ingresa el nombre comercial de la pension.</p>
                            </div>
                        </div>
                    </div>

                    <div class="cta-row">
                        <a href="listar_pension.php" class="btn btn-soft">Cancelar y volver</a>
                        <button type="submit" class="btn btn-save">Guardar cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </section>
</main>

<?php include_once 'pie_pension.php'; ?>
