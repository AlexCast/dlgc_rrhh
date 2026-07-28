<?php

$moduleId = 19;
require_once __DIR__ . '/../../app/src_guard.php';
require_once __DIR__ . '/../../app/conexion.php';

$sentencia = $conexion->query("
    SELECT id_modulo, nombre_modulo
    FROM t_modulos
    WHERE fec_delete IS NULL
    ORDER BY nombre_modulo, id_modulo
");
$t_modulos_registros = $sentencia->fetchAll(PDO::FETCH_OBJ);

?>

<?php include_once 'encab_operaciones.php'; ?>

<main class="main-container">
    <section class="editor-shell">
        <div class="editor-card">
            <header class="editor-header">
                <h1 class="editor-title">Registrar Operacione</h1>
                <p class="editor-subtitle">Completa los campos para crear un nuevo registro.</p>
            </header>

            <div class="editor-body">
                <form action="insertar_operaciones.php" method="POST" novalidate>
                    <?php echo csrf_input(); ?>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="field-wrap">
                                <label for="id_operacion" class="form-label">ID Operación</label>
                                <div class="input-group">
                                    <span class="input-group-text">ID O</span>
                                    <input
                                        type="number"
                                        name="id_operacion"
                                        id="id_operacion"
                                        class="form-control"
                                         required min="1" step="1"
                                    >
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="field-wrap">
                                <label for="id_modulo" class="form-label">Módulo</label>
                                <div class="input-group">
                                    <span class="input-group-text">Módu</span>
                                    <select name="id_modulo" id="id_modulo" class="form-select" required>
                                        <option value="" disabled selected>Seleccione módulo</option>
                                        <?php foreach ($t_modulos_registros as $item): ?>
                                            <option value="<?php echo htmlspecialchars((string) $item->id_modulo, ENT_QUOTES, 'UTF-8'); ?>">
                                                <?php echo htmlspecialchars((string) $item->nombre_modulo, ENT_QUOTES, 'UTF-8'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="field-wrap">
                                <label for="nombre_operacion" class="form-label">Nombre Operación</label>
                                <div class="input-group">
                                    <span class="input-group-text">Nomb</span>
                                    <input
                                        type="text"
                                        name="nombre_operacion"
                                        id="nombre_operacion"
                                        class="form-control"
                                         required maxlength="50"
                                    >
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="cta-row">
                        <a href="listar_operaciones.php" class="btn btn-soft">Cancelar y volver</a>
                        <button type="submit" class="btn btn-save">Registrar</button>
                    </div>
                </form>
            </div>
        </div>
    </section>
</main>

<?php include_once 'pie_operaciones.php'; ?>
