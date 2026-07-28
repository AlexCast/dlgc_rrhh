<?php

$moduleId = 9;
$requiredAction = 'actualizar';
require_once __DIR__ . '/../../app/src_guard.php';
if (!isset($_GET['id_arl'])) {
    echo 'No existe el registro a editar';
    exit();
}

$id_arl = (int) $_GET['id_arl'];

if ($id_arl <= 0) {
    echo 'ID invalido';
    exit();
}

require_once __DIR__ . '/../../app/conexion.php';

$sentencia = $conexion->prepare('
    SELECT id_arl, nombre_arl
    FROM t_arl
    WHERE id_arl = ?
      AND fec_delete IS NULL
');
$sentencia->execute([$id_arl]);
$registro = $sentencia->fetch(PDO::FETCH_OBJ);

if (!$registro) {
    echo 'No se encontro la arl activa';
    exit();
}
?>

<?php include_once 'encab_arl.php'; ?>

<main class="main-container">
    <section class="editor-shell">
        <div class="editor-card">
            <header class="editor-header">
                <h1 class="editor-title">Editar arl</h1>
                <p class="editor-subtitle">Actualiza la arl y guarda los cambios de forma segura.</p>
            </header>

            <div class="editor-body">
                <form action="update_arl.php" method="POST" novalidate>
                    <?php echo csrf_input(); ?>

                    <input type="hidden" name="id_arl" value="<?php echo (int) $registro->id_arl; ?>">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="field-wrap">
                                <label for="nombre_arl" class="form-label">Nombre arl</label>
                                <div class="input-group">
                                    <span class="input-group-text">arl</span>
                                    <input
                                        type="text"
                                        name="nombre_arl"
                                        id="nombre_arl"
                                        class="form-control"
                                        required
                                        maxlength="50"
                                        value="<?php echo htmlspecialchars((string) $registro->nombre_arl, ENT_QUOTES, 'UTF-8'); ?>"
                                        placeholder="Ej: arl Salud"
                                    >
                                </div>
                                <p class="field-tip">Ingresa el nombre comercial de la arl.</p>
                            </div>
                        </div>
                    </div>

                    <div class="cta-row">
                        <a href="listar_arl.php" class="btn btn-soft">Cancelar y volver</a>
                        <button type="submit" class="btn btn-save">Guardar cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </section>
</main>

<?php include_once 'pie_arl.php'; ?>
