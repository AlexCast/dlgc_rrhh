<?php

declare(strict_types=1);

$moduleId = 26;
$requiredAction = 'crear';
require_once __DIR__ . '/../../app/src_guard.php';

$tiposComite = ['COPASST', 'COMITE_CONVIVENCIA'];

include_once 'encab_sst.php';
require_once __DIR__ . '/../../app/alert_helper.php';
render_alert_banner();
?>

<main class="main-container">
    <section class="editor-shell">
        <div class="editor-card">
            <header class="editor-header">
                <h1 class="editor-title">Agregar Miembro al Comité</h1>
                <p class="editor-subtitle">Registra un nuevo integrante del COPASST o Comité de Convivencia.</p>
            </header>

            <div class="editor-body">
                <form action="insertar_comite.php" method="POST" novalidate data-validate>
                    <?php echo csrf_input(); ?>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="field-wrap">
                                <label for="nombre_completo" class="form-label">Nombre completo</label>
                                <input type="text" name="nombre_completo" id="nombre_completo" class="form-control" maxlength="100" required>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="field-wrap">
                                <label for="cargo" class="form-label">Cargo</label>
                                <input type="text" name="cargo" id="cargo" class="form-control" maxlength="50" required>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="field-wrap">
                                <label for="tipo_comite" class="form-label">Comité</label>
                                <select name="tipo_comite" id="tipo_comite" class="form-select" required>
                                    <option value="" disabled selected>Seleccione...</option>
                                    <?php foreach ($tiposComite as $tipo): ?>
                                        <option value="<?php echo htmlspecialchars($tipo, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($tipo); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="field-wrap">
                                <label for="correo" class="form-label">Correo</label>
                                <input type="email" name="correo" id="correo" class="form-control" maxlength="40">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="field-wrap">
                                <label for="telefono" class="form-label">Teléfono</label>
                                <input type="text" name="telefono" id="telefono" class="form-control" maxlength="15">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="field-wrap">
                                <label for="orden_visualizacion" class="form-label">Orden de visualización</label>
                                <input type="number" name="orden_visualizacion" id="orden_visualizacion" class="form-control" value="0" min="0">
                            </div>
                        </div>
                    </div>

                    <div class="editor-actions">
                        <a href="listar_comite.php" class="btn btn-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-primary">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </section>
</main>

<?php include_once 'pie_sst.php'; ?>
