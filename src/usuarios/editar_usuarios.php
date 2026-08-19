<?php

$moduleId = 20;
$requiredAction = 'actualizar';
require_once __DIR__ . '/../../app/src_guard.php';
if (!isset($_GET['id_usuario']) || trim($_GET['id_usuario']) === '') {
    echo 'No existe el registro a editar';
    exit();
}

$id_usuario = trim($_GET['id_usuario']);

require_once __DIR__ . '/../../app/conexion.php';

$sentencia = $conexion->prepare('
    SELECT *
    FROM t_usuarios
    WHERE id_usuario = ?
      AND fec_delete IS NULL
');
$sentencia->execute([$id_usuario]);
$registro = $sentencia->fetch(PDO::FETCH_OBJ);

if (!$registro) {
    echo 'No se encontró el registro activo';
    exit();
}

$sentencia = $conexion->query("
    SELECT id_rol, nombre_rol
    FROM t_roles
    WHERE fec_delete IS NULL
    ORDER BY nombre_rol, id_rol
");
$t_roles_registros = $sentencia->fetchAll(PDO::FETCH_OBJ);

?>

<?php include_once 'encab_usuarios.php'; ?>
<?php require_once __DIR__ . '/../../app/alert_helper.php'; ?>
<?php render_alert_banner(); ?>


<main class="main-container">
    <section class="editor-shell">
        <div class="editor-card">
            <header class="editor-header">
                <h1 class="editor-title">Editar Usuario</h1>
                <p class="editor-subtitle">Actualiza la información y guarda los cambios.</p>
            </header>

            <div class="editor-body">
                <form action="update_usuarios.php" method="POST" novalidate data-validate>
                    <?php echo csrf_input(); ?>

                    <input type="hidden" name="id_usuario" value="<?php echo htmlspecialchars((string) $registro->id_usuario, ENT_QUOTES, 'UTF-8'); ?>">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="field-wrap">
                                <label for="id_usuario" class="form-label">ID Usuario</label>
                                <div class="input-group">
                                    <span class="input-group-text">ID U</span>
                                    <input
                                        type="text"
                                        name="id_usuario"
                                        id="id_usuario"
                                        class="form-control"
                                         required maxlength="20" value="<?php echo htmlspecialchars((string) $registro->id_usuario, ENT_QUOTES, 'UTF-8'); ?>"
                                    >
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="field-wrap">
                                <label for="id_rol" class="form-label">Rol</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rol</span>
                                    <select name="id_rol" id="id_rol" class="form-select" required>
                                        <option value="" disabled>Seleccione rol</option>
                                        <?php foreach ($t_roles_registros as $item): ?>
                                            <option value="<?php echo htmlspecialchars((string) $item->id_rol, ENT_QUOTES, 'UTF-8'); ?>" <?php echo (string) $item->id_rol === (string) $registro->id_rol ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars((string) $item->nombre_rol, ENT_QUOTES, 'UTF-8'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="field-wrap">
                                <label for="username" class="form-label">Username</label>
                                <div class="input-group">
                                    <span class="input-group-text">User</span>
                                    <input
                                        type="text"
                                        name="username"
                                        id="username"
                                        class="form-control"
                                         required maxlength="30" value="<?php echo htmlspecialchars((string) $registro->username, ENT_QUOTES, 'UTF-8'); ?>"
                                    >
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="field-wrap">
                                <label for="tipo_documento" class="form-label">Tipo Documento</label>
                                <div class="input-group">
                                    <span class="input-group-text">Doc</span>
                                    <select name="tipo_documento" id="tipo_documento" class="form-select" required>
                                        <option value="" disabled selected>Seleccione</option>
                                        <option value="CC">Cédula de Ciudadanía</option><option value="PPT">Permiso de Protección Temporal</option><option value="CE">Cédula de Extranjería</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="field-wrap">
                                <label for="primer_nombre" class="form-label">Primer Nombre</label>
                                <div class="input-group">
                                    <span class="input-group-text">Prim</span>
                                    <input
                                        type="text"
                                        name="primer_nombre"
                                        id="primer_nombre"
                                        class="form-control"
                                         required maxlength="30" value="<?php echo htmlspecialchars((string) $registro->primer_nombre, ENT_QUOTES, 'UTF-8'); ?>"
                                    >
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="field-wrap">
                                <label for="segundo_nombre" class="form-label">Segundo Nombre</label>
                                <div class="input-group">
                                    <span class="input-group-text">Segu</span>
                                    <input
                                        type="text"
                                        name="segundo_nombre"
                                        id="segundo_nombre"
                                        class="form-control"
                                         maxlength="30" value="<?php echo htmlspecialchars((string) $registro->segundo_nombre, ENT_QUOTES, 'UTF-8'); ?>"
                                    >
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="field-wrap">
                                <label for="primer_apellido" class="form-label">Primer Apellido</label>
                                <div class="input-group">
                                    <span class="input-group-text">Prim</span>
                                    <input
                                        type="text"
                                        name="primer_apellido"
                                        id="primer_apellido"
                                        class="form-control"
                                         required maxlength="30" value="<?php echo htmlspecialchars((string) $registro->primer_apellido, ENT_QUOTES, 'UTF-8'); ?>"
                                    >
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="field-wrap">
                                <label for="segundo_apellido" class="form-label">Segundo Apellido</label>
                                <div class="input-group">
                                    <span class="input-group-text">Segu</span>
                                    <input
                                        type="text"
                                        name="segundo_apellido"
                                        id="segundo_apellido"
                                        class="form-control"
                                         maxlength="30" value="<?php echo htmlspecialchars((string) $registro->segundo_apellido, ENT_QUOTES, 'UTF-8'); ?>"
                                    >
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="field-wrap">
                                <label for="correo" class="form-label">Correo</label>
                                <div class="input-group">
                                    <span class="input-group-text">Corr</span>
                                    <input
                                        type="email"
                                        name="correo"
                                        id="correo"
                                        class="form-control"
                                         required maxlength="40"
                                         pattern="^[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$"
                                         title="Ingresa un correo electrónico válido sin comas ni espacios."
                                         value="<?php echo htmlspecialchars((string) $registro->correo, ENT_QUOTES, 'UTF-8'); ?>"
                                    >
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="field-wrap">
                                <label for="contrasena" class="form-label">Contraseña</label>
                                <div class="input-group">
                                    <span class="input-group-text">Cont</span>
                                    <input
                                        type="password"
                                        name="contrasena"
                                        id="contrasena"
                                        class="form-control"
                                         required maxlength="255"
                                    >
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="cta-row">
                        <a href="listar_usuarios.php" class="btn btn-soft">Cancelar y volver</a>
                        <button type="submit" class="btn btn-save">Guardar cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </section>
</main>

<?php include_once 'pie_usuarios.php'; ?>
