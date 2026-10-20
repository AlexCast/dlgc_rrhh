<?php

$moduleId = 14;
require_once __DIR__ . '/../../app/src_guard.php';
require_once __DIR__ . '/../../app/conexion.php';
?>

<?php include_once 'encab_eps.php'; ?>
<?php require_once __DIR__ . '/../../app/alert_helper.php'; ?>
<?php render_alert_banner(); ?>


<main class="main-container">
    <section class="editor-shell">
        <div class="editor-card">
            <header class="editor-header">
                <h1 class="editor-title">Registrar EPS</h1>
                <p class="editor-subtitle">Nombre de la EPS.</p>
            </header>

            <div class="editor-body">
                <form action="insertar_eps.php" method="POST" novalidate data-validate>
                    <?php echo csrf_input(); ?>

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
                                        placeholder="Ej: EPS Salud"
                                    >
                                </div>
                                <p class="field-tip">Ingresa el nombre de la EPS.</p>
                            </div>
                        </div>
                    </div>

                    <div class="cta-row">
                        <a href="listar_eps.php" class="btn btn-soft">Cancelar y volver</a>
                        <button type="submit" class="btn btn-save">Registrar EPS</button>
                    </div>
                </form>
            </div>
        </div>
    </section>
</main>

<?php include_once 'pie_eps.php'; ?>
