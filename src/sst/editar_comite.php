<?php

declare(strict_types=1);

$moduleId = 26;
$requiredAction = 'actualizar';
require_once __DIR__ . '/../../app/src_guard.php';

if (!isset($_GET['id_miembro']) || empty(trim($_GET['id_miembro']))) {
    echo 'No existe el registro a editar';
    exit();
}

$idMiembro = (int) trim($_GET['id_miembro']);

require_once __DIR__ . '/../../app/conexion.php';

$sentencia = $conexion->prepare('
    SELECT id_miembro, nombre_completo, cargo, tipo_comite, correo, telefono, orden_visualizacion
    FROM t_sst_comite_miembros
    WHERE id_miembro = ?
      AND fec_delete IS NULL
');
$sentencia->execute([$idMiembro]);
$registro = $sentencia->fetch(PDO::FETCH_OBJ);

if (!$registro) {
    echo 'No se encontró el miembro para este registro';
    exit();
}

$tiposComite = ['COPASST', 'COMITE_CONVIVENCIA'];

include_once 'encab_sst.php';
require_once __DIR__ . '/../../app/alert_helper.php';
render_alert_banner();
?>

<main class="main-container">
    <section class="editor-shell">
        <div class="editor-card">
            <header class="editor-header">
                <h1 class="editor-title">Editar Miembro del Comité</h1>
                <p class="editor-subtitle">Actualiza la información del integrante.</p>
            </header>

            <div class="editor-body">
                <form action="update_comite.php" method="POST" novalidate data-validate>
                    <?php echo csrf_input(); ?>

                    <input type="hidden" name="id_miembro" value="<?php echo htmlspecialchars((string) $registro->id_miembro, ENT_QUOTES, 'UTF-8'); ?>">

                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="field-wrap">
                                <label for="nombre_completo" class="form-label">Nombre completo</label>
                                <input type="text" name="nombre_completo" id="nombre_completo" class="form-control" maxlength="100" required value="<?php echo htmlspecialchars((string) $registro->nombre_completo, ENT_QUOTES, 'UTF-8'); ?>">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="field-wrap">
                                <label for="cargo" class="form-label">Cargo</label>
                                <input type="text" name="cargo" id="cargo" class="form-control" maxlength="50" required value="<?php echo htmlspecialchars((string) $registro->cargo, ENT_QUOTES, 'UTF-8'); ?>">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="field-wrap">
                                <label for="tipo_comite" class="form-label">Comité</label>
                                <select name="tipo_comite" id="tipo_comite" class="form-select" required>
                                    <?php foreach ($tiposComite as $tipo): ?>
                                        <option value="<?php echo htmlspecialchars($tipo, ENT_QUOTES, 'UTF-8'); ?>" <?php echo (string) $registro->tipo_comite === $tipo ? 'selected' : ''; ?>><?php echo htmlspecialchars($tipo); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="field-wrap">
                                <label for="correo" class="form-label">Correo</label>
                                <input type="email" name="correo" id="correo" class="form-control" maxlength="40" value="<?php echo htmlspecialchars((string) ($registro->correo ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="field-wrap">
                                <label for="telefono" class="form-label">Teléfono</label>
                                <input type="text" name="telefono" id="telefono" class="form-control" maxlength="15" value="<?php echo htmlspecialchars((string) ($registro->telefono ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="field-wrap">
                                <label for="orden_visualizacion" class="form-label">Orden de visualización</label>
                                <input type="number" name="orden_visualizacion" id="orden_visualizacion" class="form-control" min="0" value="<?php echo (int) $registro->orden_visualizacion; ?>">
                            </div>
                        </div>
                    </div>

                    <div class="editor-actions">
                        <a href="listar_comite.php" class="btn btn-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-primary">Guardar cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </section>
</main>

<?php include_once 'pie_sst.php'; ?>
