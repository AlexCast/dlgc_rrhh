<?php

$moduleId = 17;
$requiredAction = 'actualizar';
require_once __DIR__ . '/../../app/src_guard.php';
if (!isset($_GET['id_rol']) || trim($_GET['id_rol']) === '') {
    echo 'No existe el registro a editar';
    exit();
}

$id_rol = trim($_GET['id_rol']);

require_once __DIR__ . '/../../app/conexion.php';

$sentencia = $conexion->prepare('
    SELECT *
    FROM t_roles
    WHERE id_rol = ?
      AND fec_delete IS NULL
');
$sentencia->execute([$id_rol]);
$registro = $sentencia->fetch(PDO::FETCH_OBJ);

if (!$registro) {
    echo 'No se encontró el registro activo';
    exit();
}

?>

<?php include_once 'encab_roles.php'; ?>

<main class="main-container">
    <section class="editor-shell">
        <div class="editor-card">
            <header class="editor-header">
                <h1 class="editor-title">Editar Role</h1>
                <p class="editor-subtitle">Actualiza la información y guarda los cambios.</p>
            </header>

            <div class="editor-body">
                <form action="update_roles.php" method="POST" novalidate>
                    <?php echo csrf_input(); ?>

                    <input type="hidden" name="id_rol" value="<?php echo htmlspecialchars((string) $registro->id_rol, ENT_QUOTES, 'UTF-8'); ?>">
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
                                         required min="1" step="1" value="<?php echo htmlspecialchars((string) $registro->id_rol, ENT_QUOTES, 'UTF-8'); ?>"
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
                                         required maxlength="50" value="<?php echo htmlspecialchars((string) $registro->nombre_rol, ENT_QUOTES, 'UTF-8'); ?>"
                                    >
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="cta-row">
                        <a href="listar_roles.php" class="btn btn-soft">Cancelar y volver</a>
                        <button type="submit" class="btn btn-save">Guardar cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </section>
</main>

<?php include_once 'pie_roles.php'; ?>
