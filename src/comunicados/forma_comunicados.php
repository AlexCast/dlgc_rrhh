<?php

declare(strict_types=1);

$moduleId = 24;
$requiredAction = 'crear';
require_once __DIR__ . '/../../app/src_guard.php';

$categorias = ['GENERAL', 'URGENTE', 'EVENTO', 'INFORMACION', 'INSTITUCIONAL'];
?>

<?php include_once 'encab_comunicados.php'; ?>
<?php require_once __DIR__ . '/../../app/alert_helper.php'; ?>
<?php render_alert_banner(); ?>

<main class="main-container">
    <section class="editor-shell">
        <div class="editor-card">
            <header class="editor-header">
                <h1 class="editor-title">Publicar Comunicado</h1>
                <p class="editor-subtitle">Ingresa el título, contenido y categoría del nuevo comunicado.</p>
            </header>

            <div class="editor-body">
                <form action="insertar_comunicados.php" method="POST" novalidate data-validate data-min-length-field="#contenido" data-min-length="10">
                    <?php echo csrf_input(); ?>

                    <div class="row g-3">
                        <div class="col-12">
                            <div class="field-wrap">
                                <label for="titulo" class="form-label">Título</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-heading"></i></span>
                                    <input
                                        type="text"
                                        name="titulo"
                                        id="titulo"
                                        class="form-control"
                                        maxlength="100"
                                        required
                                        placeholder="Ej: Nueva Política de Teletrabajo"
                                    >
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="field-wrap">
                                <label for="categoria" class="form-label">Categoría</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-tag"></i></span>
                                    <select name="categoria" id="categoria" class="form-select" required>
                                        <option value="" disabled selected>Seleccione categoría</option>
                                        <?php foreach ($categorias as $cat): ?>
                                            <option value="<?php echo htmlspecialchars($cat, ENT_QUOTES, 'UTF-8'); ?>">
                                                <?php echo htmlspecialchars($cat, ENT_QUOTES, 'UTF-8'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="field-wrap">
                                <label for="contenido" class="form-label">Contenido</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-align-left"></i></span>
                                    <textarea
                                        name="contenido"
                                        id="contenido"
                                        class="form-control"
                                        rows="8"
                                        required
                                        placeholder="Escribe aquí el cuerpo del comunicado..."
                                    ></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="editor-actions">
                        <a href="listar_comunicados.php" class="btn btn-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-primary">Publicar</button>
                    </div>
                </form>
            </div>
        </div>
    </section>
</main>

<?php include_once 'pie_comunicados.php'; ?>
