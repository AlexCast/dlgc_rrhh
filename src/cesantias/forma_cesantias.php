<?php

$moduleId = 12;
require_once __DIR__ . '/../../app/src_guard.php';
require_once __DIR__ . '/../../app/conexion.php';
?>

<?php include_once 'encab_cesantias.php'; ?>

<main class="main-container">
    <section class="editor-shell">
        <div class="editor-card">
            <header class="editor-header">
                <h1 class="editor-title">Registrar Cesantía</h1>
                <p class="editor-subtitle">Nombre de la Cesantía.</p>
            </header>

            <div class="editor-body">
                <form action="insertar_cesantias.php" method="POST" novalidate>
                    <?php echo csrf_input(); ?>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="field-wrap">
                                <label for="nombre_cesantia" class="form-label">Nombre Cesantía</label>
                                <div class="input-group">
                                    <span class="input-group-text">Cesantía</span>
                                    <input
                                        type="text"
                                        name="nombre_cesantia"
                                        id="nombre_cesantia"
                                        class="form-control"
                                        required
                                        maxlength="50"
                                        placeholder="Ej: Cesantía"
                                    >
                                </div>
                                <p class="field-tip">Ingresa el nombre de la Cesantía.</p>
                            </div>
                        </div>
                    </div>

                    <div class="cta-row">
                        <a href="listar_cesantias.php" class="btn btn-soft">Cancelar y volver</a>
                        <button type="submit" class="btn btn-save">Registrar Cesantía</button>
                    </div>
                </form>
            </div>
        </div>
    </section>
</main>

<?php include_once 'pie_cesantias.php'; ?>
