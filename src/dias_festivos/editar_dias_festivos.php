<?php

$moduleId = 28;
$requiredAction = 'actualizar';
require_once __DIR__ . '/../../app/src_guard.php';
if (!isset($_GET['id_festivo'])) {
    echo 'No existe el registro a editar';
    exit();
}

$id_festivo = (int) $_GET['id_festivo'];

if ($id_festivo <= 0) {
    echo 'ID invalido';
    exit();
}

require_once __DIR__ . '/../../app/conexion.php';

$sentencia = $conexion->prepare('
    SELECT id_festivo, fecha, descripcion, descuenta_salario
    FROM t_dias_festivos
    WHERE id_festivo = ?
      AND tipo_festivo = \'EMPRESA\'
      AND fec_delete IS NULL
');
$sentencia->execute([$id_festivo]);
$registro = $sentencia->fetch(PDO::FETCH_OBJ);

if (!$registro) {
    echo 'No se encontró el festivo de empresa activo (los festivos nacionales no son editables)';
    exit();
}
?>

<?php include_once 'encab_dias_festivos.php'; ?>
<?php require_once __DIR__ . '/../../app/alert_helper.php'; ?>
<?php render_alert_banner(); ?>


<main class="main-container">
    <section class="editor-shell">
        <div class="editor-card">
            <header class="editor-header">
                <h1 class="editor-title">Editar Festivo de Empresa</h1>
                <p class="editor-subtitle">Actualiza el festivo de empresa y guarda los cambios de forma segura.</p>
            </header>

            <div class="editor-body">
                <form action="update_dias_festivos.php" method="POST" novalidate data-validate>
                    <?php echo csrf_input(); ?>

                    <input type="hidden" name="id_festivo" value="<?php echo (int) $registro->id_festivo; ?>">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="field-wrap">
                                <label for="fecha" class="form-label">Fecha</label>
                                <input type="date" name="fecha" id="fecha" class="form-control" required value="<?php echo htmlspecialchars((string) $registro->fecha); ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="field-wrap">
                                <label for="descripcion" class="form-label">Descripción</label>
                                <input type="text" name="descripcion" id="descripcion" class="form-control" required minlength="3" maxlength="100" value="<?php echo htmlspecialchars((string) $registro->descripcion, ENT_QUOTES, 'UTF-8'); ?>">
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="field-wrap form-check">
                                <input type="checkbox" name="descuenta_salario" id="descuenta_salario" class="form-check-input" value="1" <?php echo filter_var($registro->descuenta_salario, FILTER_VALIDATE_BOOLEAN) ? 'checked' : ''; ?>>
                                <label for="descuenta_salario" class="form-check-label">Este día se descuenta del sueldo</label>
                            </div>
                        </div>
                    </div>

                    <div class="cta-row">
                        <a href="listar_dias_festivos.php" class="btn btn-soft">Cancelar y volver</a>
                        <button type="submit" class="btn btn-save">Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </section>
</main>

<?php include_once 'pie_dias_festivos.php'; ?>
