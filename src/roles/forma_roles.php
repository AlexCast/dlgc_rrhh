<?php

$moduleId = 17;
require_once __DIR__ . '/../../app/src_guard.php';
require_once __DIR__ . '/../../app/conexion.php';

?>

<?php include_once 'encab_roles.php'; ?>
<?php require_once __DIR__ . '/../../app/alert_helper.php'; ?>
<?php render_alert_banner(); ?>


<main class="main-container">
    <section class="editor-shell">
        <div class="editor-card">
            <header class="editor-header">
                <h1 class="editor-title">Registrar Role</h1>
                <p class="editor-subtitle">Completa los campos para crear un nuevo registro.</p>
            </header>

            <div class="editor-body">
                <form action="insertar_roles.php" method="POST" novalidate data-validate>
                    <?php echo csrf_input(); ?>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="field-wrap">
                                <label for="id_rol" class="form-label">ID Rol</label>
                                <div class="input-group">
                                    <span class="input-group-text">ID R</span>
                                    <input
                                        type="number"
                                        name="id_rol"
                                        id="id_rol"
                                        class="form-control"
                                         required min="1" step="1"
                                    >
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="field-wrap">
                                <label for="nombre_rol" class="form-label">Nombre Rol</label>
                                <div class="input-group">
                                    <span class="input-group-text">Nomb</span>
                                    <input
                                        type="text"
                                        name="nombre_rol"
                                        id="nombre_rol"
                                        class="form-control"
                                         required maxlength="50"
                                    >
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="cta-row">
                        <a href="listar_roles.php" class="btn btn-soft">Cancelar y volver</a>
                        <button type="submit" class="btn btn-save">Registrar</button>
                    </div>
                </form>
            </div>
        </div>
    </section>
</main>

<?php include_once 'pie_roles.php'; ?>
