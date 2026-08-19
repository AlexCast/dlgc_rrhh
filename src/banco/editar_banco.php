<?php

$moduleId = 10;
$requiredAction = 'actualizar';
require_once __DIR__ . '/../../app/src_guard.php';
if (!isset($_GET['id_banco'])) {
    echo 'No existe el registro a editar';
    exit();
}

$id_banco = (int) $_GET['id_banco'];

if ($id_banco <= 0) {
    echo 'ID invalido';
    exit();
}

require_once __DIR__ . '/../../app/conexion.php';

$sentencia = $conexion->prepare('
    SELECT id_banco, nombre_banco
    FROM t_bancos
    WHERE id_banco = ?
      AND fec_delete IS NULL
');
$sentencia->execute([$id_banco]);
$registro = $sentencia->fetch(PDO::FETCH_OBJ);

if (!$registro) {
    echo 'No se encontro el banco activo';
    exit();
}
?>

<?php include_once 'encab_banco.php'; ?>
<?php require_once __DIR__ . '/../../app/alert_helper.php'; ?>
<?php render_alert_banner(); ?>


<main class="main-container">
    <section class="editor-shell">
        <div class="editor-card">
            <header class="editor-header">
                <h1 class="editor-title">Editar Banco</h1>
                <p class="editor-subtitle">Actualiza el Banco y guarda los cambios de forma segura.</p>
            </header>

            <div class="editor-body">
                <form action="update_banco.php" method="POST" novalidate data-validate>
                    <?php echo csrf_input(); ?>

                    <input type="hidden" name="id_banco" value="<?php echo (int) $registro->id_banco; ?>">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="field-wrap">
                                <label for="nombre_banco" class="form-label">Nombre Banco</label>
                                <div class="input-group">
                                    <span class="input-group-text">Banco</span>
                                    <input
                                        type="text"
                                        name="nombre_banco"
                                        id="nombre_banco"
                                        class="form-control"
                                        required
                                        maxlength="50"
                                        value="<?php echo htmlspecialchars((string) $registro->nombre_banco, ENT_QUOTES, 'UTF-8'); ?>"
                                        placeholder="Ej: DAVIVIENDA"
                                    >
                                </div>
                                <p class="field-tip">Ingresa el nombre comercial del Banco.</p>
                            </div>
                        </div>
                    </div>

                    <div class="cta-row">
                        <a href="listar_banco.php" class="btn btn-soft">Cancelar y volver</a>
                        <button type="submit" class="btn btn-save">Guardar cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </section>
</main>

<?php include_once 'pie_banco.php'; ?>
