<?php

$moduleId = 11;
$requiredAction = 'actualizar';
require_once __DIR__ . '/../../app/src_guard.php';
if (!isset($_GET['id_caja'])) {
    echo 'No existe el registro a editar';
    exit();
}

$id_caja = (int) $_GET['id_caja'];

if ($id_caja <= 0) {
    echo 'ID invalido';
    exit();
}

require_once __DIR__ . '/../../app/conexion.php';

$sentencia = $conexion->prepare('
    SELECT id_caja, nombre_caja
    FROM t_caja_compensacion
    WHERE id_caja = ?
      AND fec_delete IS NULL
');
$sentencia->execute([$id_caja]);
$registro = $sentencia->fetch(PDO::FETCH_OBJ);

if (!$registro) {
    echo 'No se encontro la caja de compensacion activa';
    exit();
}
?>

<?php include_once 'encab_cajacompensacion.php'; ?>
<?php require_once __DIR__ . '/../../app/alert_helper.php'; ?>
<?php render_alert_banner(); ?>


<main class="main-container">
    <section class="editor-shell">
        <div class="editor-card">
            <header class="editor-header">
                <h1 class="editor-title">Editar caja de compensación</h1>
                <p class="editor-subtitle">Actualiza la caja de compensación y guarda los cambios de forma segura.</p>
            </header>

            <div class="editor-body">
                <form action="update_cajacompensacion.php" method="POST" novalidate data-validate>
                    <?php echo csrf_input(); ?>

                    <input type="hidden" name="id_caja" value="<?php echo (int) $registro->id_caja; ?>">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="field-wrap">
                                <label for="nombre_caja" class="form-label">Nombre caja de compensación</label>
                                <div class="input-group">
                                    <span class="input-group-text">Caja</span>
                                    <input
                                        type="text"
                                        name="nombre_caja"
                                        id="nombre_caja"
                                        class="form-control"
                                        required
                                        maxlength="50"
                                        value="<?php echo htmlspecialchars((string) $registro->nombre_caja, ENT_QUOTES, 'UTF-8'); ?>"
                                        placeholder="Ej: Compensar"
                                    >
                                </div>
                                <p class="field-tip">Ingresa el nombre comercial de la caja de compensación.</p>
                            </div>
                        </div>
                    </div>

                    <div class="cta-row">
                        <a href="listar_cajacompensacion.php" class="btn btn-soft">Cancelar y volver</a>
                        <button type="submit" class="btn btn-save">Guardar cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </section>
</main>

<?php include_once 'pie_cajacompensacion.php'; ?>