<?php

$moduleId = 12;
$requiredAction = 'actualizar';
require_once __DIR__ . '/../../app/src_guard.php';
if (!isset($_GET['id_cesantia'])) {
    echo 'No existe el registro a editar';
    exit();
}

$id_cesantia = (int) $_GET['id_cesantia'];

if ($id_cesantia <= 0) {
    echo 'ID invalido';
    exit();
}

require_once __DIR__ . '/../../app/conexion.php';

$sentencia = $conexion->prepare('
    SELECT id_cesantia, nombre_cesantia
    FROM t_cesantias
    WHERE id_cesantia = ?
      AND fec_delete IS NULL
');
$sentencia->execute([$id_cesantia]);
$registro = $sentencia->fetch(PDO::FETCH_OBJ);

if (!$registro) {
    echo 'No se encontro la cesantia activa';
    exit();
}
?>

<?php include_once 'encab_cesantias.php'; ?>

<main class="main-container">
    <section class="editor-shell">
        <div class="editor-card">
            <header class="editor-header">
                <h1 class="editor-title">Editar Cesantía</h1>
                <p class="editor-subtitle">Actualiza la Cesantía y guarda los cambios de forma segura.</p>
            </header>

            <div class="editor-body">
                <form action="update_cesantias.php" method="POST" novalidate>
                    <?php echo csrf_input(); ?>

                    <input type="hidden" name="id_cesantia" value="<?php echo (int) $registro->id_cesantia; ?>">
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
                                        value="<?php echo htmlspecialchars((string) $registro->nombre_cesantia, ENT_QUOTES, 'UTF-8'); ?>"
                                        placeholder="Ej: Cesantía"
                                    >
                                </div>
                                <p class="field-tip">Ingresa el nombre de la Cesantía.</p>
                            </div>
                        </div>
                    </div>

                    <div class="cta-row">
                        <a href="listar_cesantias.php" class="btn btn-soft">Cancelar y volver</a>
                        <button type="submit" class="btn btn-save">Guardar cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </section>
</main>

<?php include_once 'pie_cesantias.php'; ?>
