<?php

$moduleId = 14;
$requiredAction = 'actualizar';
require_once __DIR__ . '/../../app/src_guard.php';
if (!isset($_GET['id_eps'])) {
    echo 'No existe el registro a editar';
    exit();
}

$id_eps = (int) $_GET['id_eps'];

if ($id_eps <= 0) {
    echo 'ID invalido';
    exit();
}

require_once __DIR__ . '/../../app/conexion.php';

$sentencia = $conexion->prepare('
    SELECT id_eps, nombre_eps
    FROM t_eps
    WHERE id_eps = ?
      AND fec_delete IS NULL
');
$sentencia->execute([$id_eps]);
$registro = $sentencia->fetch(PDO::FETCH_OBJ);

if (!$registro) {
    echo 'No se encontro la EPS activa';
    exit();
}
?>

<?php include_once 'encab_eps.php'; ?>
<?php require_once __DIR__ . '/../../app/alert_helper.php'; ?>
<?php render_alert_banner(); ?>


<main class="main-container">
    <section class="editor-shell">
        <div class="editor-card">
            <header class="editor-header">
                <h1 class="editor-title">Editar EPS</h1>
                <p class="editor-subtitle">Actualiza la EPS y guarda los cambios de forma segura.</p>
            </header>

            <div class="editor-body">
                <form action="update_eps.php" method="POST" novalidate data-validate>
                    <?php echo csrf_input(); ?>

                    <input type="hidden" name="id_eps" value="<?php echo (int) $registro->id_eps; ?>">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="field-wrap">
                                <label for="nombre_eps" class="form-label">Nombre EPS</label>
                                <div class="input-group">
                                    <span class="input-group-text">EPS</span>
                                    <input
                                        type="text"
                                        name="nombre_eps"
                                        id="nombre_eps"
                                        class="form-control"
                                        required
                                        maxlength="50"
                                        value="<?php echo htmlspecialchars((string) $registro->nombre_eps, ENT_QUOTES, 'UTF-8'); ?>"
                                        placeholder="Ej: EPS Salud"
                                    >
                                </div>
                                <p class="field-tip">Ingresa el nombre comercial de la EPS.</p>
                            </div>
                        </div>
                    </div>

                    <div class="cta-row">
                        <a href="listar_eps.php" class="btn btn-soft">Cancelar y volver</a>
                        <button type="submit" class="btn btn-save">Guardar cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </section>
</main>

<?php include_once 'pie_eps.php'; ?>
