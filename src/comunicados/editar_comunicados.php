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
$autorNombre = htmlspecialchars($_SESSION['nombre_completo'] ?? 'Usuario', ENT_QUOTES, 'UTF-8');

require_once __DIR__ . '/../../app/helpers/HtmlSanitizer.php';
$contenidoRaw = (string) $registro->contenido;
$contenidoLimpio = HtmlSanitizer::clean($contenidoRaw);

// Si el contenido histórico es texto plano, conservar los saltos de línea.
if ($contenidoLimpio === '' || $contenidoLimpio === strip_tags($contenidoLimpio)) {
    $contenidoLimpio = nl2br(htmlspecialchars($contenidoRaw, ENT_QUOTES, 'UTF-8'), false);
}
?>

<?php include_once 'encab_comunicados.php'; ?>
<?php require_once __DIR__ . '/../../app/alert_helper.php'; ?>
<?php render_alert_banner(); ?>

<main class="main-container forma-comunicados-app">
    <header class="fc-editor-header">
        <div class="fc-editor-brand" aria-hidden="true">
            <i class="fas fa-pen-fancy"></i>
        </div>
        <h1>Editar Comunicado</h1>
        <p>Actualiza la información y visualiza los cambios antes de guardarlos.</p>
    </header>

    <div class="fc-layout">
        <section class="fc-editor-col">
            <div class="fc-card">
                <div class="fc-card-body">
                    <form action="update_comunicados.php" method="POST" class="fc-form" novalidate>
                        <?php echo csrf_input(); ?>

                        <input type="hidden" name="id_comunicado" value="<?php echo htmlspecialchars((string) $registro->id_comunicado, ENT_QUOTES, 'UTF-8'); ?>">

                        <div class="fc-field">
                            <label for="titulo" class="fc-label">
                                <i class="fas fa-heading"></i> Título
                            </label>
                            <input
                                type="text"
                                name="titulo"
                                id="titulo"
                                class="fc-input fc-title-input"
                                maxlength="100"
                                required
                                value="<?php echo htmlspecialchars((string) $registro->titulo, ENT_QUOTES, 'UTF-8'); ?>"
                                autocomplete="off"
                            >
                        </div>

                        <div class="fc-field">
                            <label for="categoria" class="fc-label">
                                <i class="fas fa-tag"></i> Categoría
                            </label>
                            <div class="fc-select-wrap">
                                <select name="categoria" id="categoria" class="fc-select" required>
                                    <option value="" disabled>Seleccione categoría</option>
                                    <?php foreach ($categorias as $cat): ?>
                                        <option value="<?php echo htmlspecialchars($cat, ENT_QUOTES, 'UTF-8'); ?>" <?php echo (string) $registro->categoria === $cat ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($cat, ENT_QUOTES, 'UTF-8'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="fc-field">
                            <label for="contenido" id="contenido-label" class="fc-label">
                                <i class="fas fa-pen-nib"></i> Contenido
                            </label>

                            <div class="fc-toolbar" role="toolbar" aria-label="Herramientas de formato">
                                <div class="fc-toolbar-group">
                                    <button type="button" class="fc-tool-btn" data-action="bold" title="Negrita (Ctrl+B)" aria-label="Negrita">
                                        <i class="fas fa-bold"></i>
                                    </button>
                                    <button type="button" class="fc-tool-btn" data-action="italic" title="Itálica (Ctrl+I)" aria-label="Itálica">
                                        <i class="fas fa-italic"></i>
                                    </button>
                                    <button type="button" class="fc-tool-btn" data-action="underline" title="Subrayado (Ctrl+U)" aria-label="Subrayado">
                                        <i class="fas fa-underline"></i>
                                    </button>
                                </div>

                                <div class="fc-toolbar-group">
                                    <button type="button" class="fc-tool-btn" data-action="justifyLeft" title="Alinear a la izquierda" aria-label="Alinear a la izquierda">
                                        <i class="fas fa-align-left"></i>
                                    </button>
                                    <button type="button" class="fc-tool-btn" data-action="justifyCenter" title="Centrar" aria-label="Centrar">
                                        <i class="fas fa-align-center"></i>
                                    </button>
                                    <button type="button" class="fc-tool-btn" data-action="justifyRight" title="Alinear a la derecha" aria-label="Alinear a la derecha">
                                        <i class="fas fa-align-right"></i>
                                    </button>
                                    <button type="button" class="fc-tool-btn" data-action="justifyFull" title="Justificar" aria-label="Justificar">
                                        <i class="fas fa-align-justify"></i>
                                    </button>
                                </div>

                                <div class="fc-toolbar-group">
                                    <button type="button" class="fc-tool-btn" data-action="insertUnorderedList" title="Lista con viñetas" aria-label="Lista con viñetas">
                                        <i class="fas fa-list-ul"></i>
                                    </button>
                                    <button type="button" class="fc-tool-btn" data-action="insertOrderedList" title="Lista numerada" aria-label="Lista numerada">
                                        <i class="fas fa-list-ol"></i>
                                    </button>
                                </div>

                                <div class="fc-toolbar-group">
                                    <button type="button" class="fc-tool-btn" data-action="heading" data-tag="h2" title="Título grande" aria-label="Título grande">
                                        <i class="fas fa-heading"></i>
                                    </button>
                                    <button type="button" class="fc-tool-btn" data-action="heading" data-tag="h3" title="Subtítulo" aria-label="Subtítulo">
                                        <i class="fas fa-font"></i>
                                    </button>
                                    <button type="button" class="fc-tool-btn" data-action="formatBlock" data-tag="blockquote" title="Cita" aria-label="Cita">
                                        <i class="fas fa-quote-right"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="fc-editor-area">
                                <div
                                    id="contenido"
                                    class="fc-editor"
                                    contenteditable="true"
                                    role="textbox"
                                    aria-multiline="true"
                                    aria-labelledby="contenido-label"
                                    data-placeholder="Escribe aquí el cuerpo del comunicado..."
                                ><?php echo $contenidoLimpio; ?></div>
                            </div>

                            <input type="hidden" name="contenido" class="fc-hidden-content">

                            <div class="fc-editor-footer">
                                <span>Mínimo 10 caracteres</span>
                                <span class="fc-char-count">0 caracteres</span>
                            </div>
                        </div>

                        <div class="fc-actions">
                            <a href="listar_comunicados.php" class="fc-btn fc-btn-secondary">
                                <i class="fas fa-times"></i> Cancelar
                            </a>
                            <button type="submit" class="fc-btn fc-btn-primary">
                                <i class="fas fa-save"></i> Guardar cambios
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </section>

        <aside class="fc-preview-col">
            <div class="fc-preview-label">
                <i class="fas fa-eye"></i> Vista previa
            </div>
            <div class="fc-preview-card">
                <article class="fc-post">
                    <span class="fc-post-category is-general">Categoría</span>
                    <h2 class="fc-post-title"></h2>

                    <div class="fc-post-meta">
                        <div class="fc-post-avatar">
                            <i class="fas fa-user"></i>
                        </div>
                        <div class="fc-post-info">
                            <span class="fc-post-author"><?php echo $autorNombre; ?></span>
                            <span class="fc-post-date">Ahora mismo</span>
                        </div>
                    </div>

                    <div class="fc-post-content">
                        <div class="fc-preview-empty">
                            <i class="fas fa-newspaper"></i>
                            <span>El contenido del comunicado aparecerá aquí mientras escribes.</span>
                        </div>
                    </div>
                </article>
            </div>
        </aside>
    </div>
</main>

<?php include_once 'pie_comunicados.php'; ?>
