<?php

$moduleId = 2;
require_once __DIR__ . '/../../app/src_guard.php';
require_once __DIR__ . '/../../app/conexion.php';
?>

<?php include_once 'encab_soat.php'; ?>
<?php require_once __DIR__ . '/../../app/alert_helper.php'; ?>
<?php render_alert_banner(); ?>


<main class="main-container">
    <section class="editor-shell">
        <div class="editor-card">
            <header class="editor-header">
                <h1 class="editor-title">Registrar SOAT</h1>
                <p class="editor-subtitle">Nombre del SOAT.</p>
            </header>

            <div class="editor-body">
                <form action="insertar_soat.php" method="POST" novalidate data-validate>
                    <?php echo csrf_input(); ?>

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
                                        placeholder="Ej: SOAT"
                                    >
                                </div>
                                <p class="field-tip">Ingresa el nombre del SOAT.</p>
                            </div>
                        </div>
                    </div>

                    <div class="cta-row">
                        <a href="listar_soat.php" class="btn btn-soft">Cancelar y volver</a>
                        <button type="submit" class="btn btn-save">Registrar SOAT</button>
                    </div>
                </form>
            </div>
        </div>
    </section>
</main>

<?php include_once 'pie_soat.php'; ?>
