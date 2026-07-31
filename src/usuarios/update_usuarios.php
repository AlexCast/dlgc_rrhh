<?php

$moduleId = 20;
$requiredAction = 'actualizar';
require_once __DIR__ . '/../../app/src_guard.php';

// Validate CSRF token before processing mutation.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate();
}
if (
    !isset($_POST['id_usuario']) ||
    !isset($_POST['id_rol']) ||
    !isset($_POST['username']) ||
    !isset($_POST['tipo_documento']) ||
    !isset($_POST['primer_nombre']) ||
    !isset($_POST['primer_apellido']) ||
    !isset($_POST['correo']) ||
    !isset($_POST['contrasena'])
) {
    header('Location: listar_usuarios.php?error=datos');
    exit();
}

$id_usuario = htmlspecialchars(trim($_POST['id_usuario'] ?? ''));
$id_rol = trim($_POST['id_rol'] ?? '');
$username = trim($_POST['username'] ?? '');
$tipo_documento = trim($_POST['tipo_documento'] ?? '');
$primer_nombre = trim($_POST['primer_nombre'] ?? '');
$segundo_nombre = trim($_POST['segundo_nombre'] ?? '');
$primer_apellido = trim($_POST['primer_apellido'] ?? '');
$segundo_apellido = trim($_POST['segundo_apellido'] ?? '');
$correo = trim($_POST['correo'] ?? '');
$contrasena = trim($_POST['contrasena'] ?? '');

require_once __DIR__ . '/../../app/conexion.php';

$sentencia = $conexion->prepare('SELECT fun_update_usuarios(:id_usuario, :id_rol, :username, :tipo_documento, :primer_nombre, :segundo_nombre, :primer_apellido, :segundo_apellido, :correo, :contrasena);');
$sentencia->execute([
    ':id_usuario' => $id_usuario,
        ':id_rol' => $id_rol,
        ':username' => $username,
        ':tipo_documento' => $tipo_documento,
        ':primer_nombre' => $primer_nombre,
        ':segundo_nombre' => $segundo_nombre,
        ':primer_apellido' => $primer_apellido,
        ':segundo_apellido' => $segundo_apellido,
        ':correo' => $correo,
        ':contrasena' => $contrasena,
]);
$resultado = (string) $sentencia->fetchColumn();

if (stripos($resultado, 'funcion') !== false || stripos($resultado, 'correctamente') !== false || stripos($resultado, 'Esta vaina funcionó') !== false) {
    header('Location: listar_usuarios.php?success=' . urlencode('Usuario actualizado correctamente.'));
    exit();
}

header('Location: listar_usuarios.php?error=' . urlencode($resultado));
exit();
