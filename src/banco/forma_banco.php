<?php

$moduleId = 10;
require_once __DIR__ . '/../../app/src_guard.php';
require_once __DIR__ . '/../../app/conexion.php';
?>

<?php include_once 'encab_banco.php'; ?>
<?php require_once __DIR__ . '/../../app/alert_helper.php'; ?>
<?php render_alert_banner(); ?>


<main class="main-container">
    <section class="editor-shell">
        <div class="editor-card">
            <header class="editor-header">
                <h1 class="editor-title">Registrar Banco</h1>
                <p class="editor-subtitle">Nombre del Banco.</p>
            </header>

            <div class="editor-body">
                <form action="insertar_banco.php" method="POST" novalidate data-validate>
                    <?php echo csrf_input(); ?>

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
                                        placeholder="Ej: DAVIVIENDA"
                                    >
                                </div>
                                <p class="field-tip">Ingresa el nombre del Banco.</p>
                            </div>
                        </div>
                    </div>

                    <div class="cta-row">
                        <a href="listar_banco.php" class="btn btn-soft">Cancelar y volver</a>
                        <button type="submit" class="btn btn-save">Registrar Banco</button>
                    </div>
                </form>
            </div>
        </div>
    </section>
</main>

<?php include_once 'pie_banco.php'; ?>
