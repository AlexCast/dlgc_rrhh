<?php

$moduleId = 18;
require_once __DIR__ . '/../../app/src_guard.php';
require_once __DIR__ . '/../../app/conexion.php';

?>

<?php include_once 'encab_modulos.php'; ?>
<?php require_once __DIR__ . '/../../app/alert_helper.php'; ?>
<?php render_alert_banner(); ?>


<main class="main-container">
    <section class="editor-shell">
        <div class="editor-card">
            <header class="editor-header">
                <h1 class="editor-title">Registrar Módulo</h1>
                <p class="editor-subtitle">Completa los campos para crear un nuevo registro.</p>
            </header>

            <div class="editor-body">
                <form action="insertar_modulos.php" method="POST" novalidate data-validate>
                    <?php echo csrf_input(); ?>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="field-wrap">
                                <label for="id_modulo" class="form-label">ID Módulo</label>
                                <div class="input-group">
                                    <span class="input-group-text">ID M</span>
                                    <input
                                        type="number"
                                        name="id_modulo"
                                        id="id_modulo"
                                        class="form-control"
                                         required min="1" step="1"
                                    >
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="field-wrap">
                                <label for="nombre_modulo" class="form-label">Nombre Módulo</label>
                                <div class="input-group">
                                    <span class="input-group-text">Nomb</span>
                                    <input
                                        type="text"
                                        name="nombre_modulo"
                                        id="nombre_modulo"
                                        class="form-control"
                                         required maxlength="30"
                                    >
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="cta-row">
                        <a href="listar_modulos.php" class="btn btn-soft">Cancelar y volver</a>
                        <button type="submit" class="btn btn-save">Registrar</button>
                    </div>
                </form>
            </div>
        </div>
    </section>
</main>

<?php include_once 'pie_modulos.php'; ?>
