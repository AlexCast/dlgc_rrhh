<?php

declare(strict_types=1);

$moduleId = 24;
$requiredAction = 'actualizar';
require_once __DIR__ . '/../../app/src_guard.php';

if (!isset($_GET['id_comunicado']) || empty(trim($_GET['id_comunicado']))) {
    echo 'No existe el registro a editar';
    exit();
}

$id_comunicado = (int) trim($_GET['id_comunicado']);

require_once __DIR__ . '/../../app/conexion.php';

$sentencia = $conexion->prepare('
    SELECT id_comunicado, titulo, contenido, categoria
    FROM t_comunicados
    WHERE id_comunicado = ?
      AND fec_delete IS NULL
');
$sentencia->execute([$id_comunicado]);
$registro = $sentencia->fetch(PDO::FETCH_OBJ);

if (!$registro) {
    echo 'No se encontró el comunicado para este registro';
    exit();
}

$categorias = ['GENERAL', 'URGENTE', 'EVENTO', 'INFORMACION', 'INSTITUCIONAL'];
?>

<?php include_once 'encab_comunicados.php'; ?>
<?php require_once __DIR__ . '/../../app/alert_helper.php'; ?>
<?php render_alert_banner(); ?>

<main class="main-container">
    <section class="editor-shell">
        <div class="editor-card">
            <header class="editor-header">
                <h1 class="editor-title">Editar Comunicado</h1>
                <p class="editor-subtitle">Actualiza la información del comunicado.</p>
            </header>

            <div class="editor-body">
                <form action="update_comunicados.php" method="POST" novalidate data-validate data-min-length-field="#contenido" data-min-length="10">
                    <?php echo csrf_input(); ?>

                    <input type="hidden" name="id_comunicado" value="<?php echo htmlspecialchars((string) $registro->id_comunicado, ENT_QUOTES, 'UTF-8'); ?>">

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
                                        value="<?php echo htmlspecialchars((string) $registro->titulo, ENT_QUOTES, 'UTF-8'); ?>"
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
                                        <option value="" disabled>Seleccione categoría</option>
                                        <?php foreach ($categorias as $cat): ?>
                                            <option value="<?php echo htmlspecialchars($cat, ENT_QUOTES, 'UTF-8'); ?>" <?php echo (string) $registro->categoria === $cat ? 'selected' : ''; ?>>
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
                                    ><?php echo htmlspecialchars((string) $registro->contenido, ENT_QUOTES, 'UTF-8'); ?></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="editor-actions">
                        <a href="listar_comunicados.php" class="btn btn-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-primary">Guardar cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </section>
</main>

<?php include_once 'pie_comunicados.php'; ?>
