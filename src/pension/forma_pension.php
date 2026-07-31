<?php

$moduleId = 16;
require_once __DIR__ . '/../../app/src_guard.php';
require_once __DIR__ . '/../../app/conexion.php';
?>

<?php include_once 'encab_pension.php'; ?>
<?php require_once __DIR__ . '/../../app/alert_helper.php'; ?>
<?php render_alert_banner(); ?>


<main class="main-container">
    <section class="editor-shell">
        <div class="editor-card">
            <header class="editor-header">
                <h1 class="editor-title">Registrar Pensión</h1>
                <p class="editor-subtitle">Nombre de la Pensión.</p>
            </header>

            <div class="editor-body">
                <form action="insertar_pension.php" method="POST" novalidate data-validate>
                    <?php echo csrf_input(); ?>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="field-wrap">
                                <label for="nombre_pension" class="form-label">Nombre Pensión</label>
                                <div class="input-group">
                                    <span class="input-group-text">Pensión</span>
                                    <input
                                        type="text"
                                        name="nombre_pension"
                                        id="nombre_pension"
                                        class="form-control"
                                        required
                                        maxlength="50"
                                        placeholder="Ej: Pensión"
                                    >
                                </div>
                                <p class="field-tip">Ingresa el nombre de la Pensión.</p>
                            </div>
                        </div>
                    </div>

                    <div class="cta-row">
                        <a href="listar_pension.php" class="btn btn-soft">Cancelar y volver</a>
                        <button type="submit" class="btn btn-save">Registrar Pensión</button>
                    </div>
                </form>
            </div>
        </div>
    </section>
</main>

<?php include_once 'pie_pension.php'; ?>
