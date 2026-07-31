<?php

$moduleId = 11;
require_once __DIR__ . '/../../app/src_guard.php';
require_once __DIR__ . '/../../app/conexion.php';
?>

<?php include_once 'encab_cajacompensacion.php'; ?>
<?php require_once __DIR__ . '/../../app/alert_helper.php'; ?>
<?php render_alert_banner(); ?>


<main class="main-container">
    <section class="editor-shell">
        <div class="editor-card">
            <header class="editor-header">
                <h1 class="editor-title">Registrar caja de compensación</h1>
                <p class="editor-subtitle">Nombre de la caja de compensación.</p>
            </header>

            <div class="editor-body">
                <form action="insertar_cajacompensacion.php" method="POST" novalidate data-validate>
                    <?php echo csrf_input(); ?>

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
                                        placeholder="Ej: Compensar"
                                    >
                                </div>
                                <p class="field-tip">Ingresa el nombre de la caja de compensación.</p>
                            </div>
                        </div>
                    </div>

                    <div class="cta-row">
                        <a href="listar_cajacompensacion.php" class="btn btn-soft">Cancelar y volver</a>
                        <button type="submit" class="btn btn-save">Registrar caja de compensación</button>
                    </div>
                </form>
            </div>
        </div>
    </section>
</main>

<?php include_once 'pie_cajacompensacion.php'; ?>