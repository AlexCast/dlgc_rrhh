<?php

$moduleId = 20;
$requiredAction = 'crear';
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
    header('Location: forma_usuarios.php?error=datos');
    exit();
}

require_once __DIR__ . '/../../app/helpers/InputSanitizer.php';

$tipo_documento = InputSanitizer::whitelist($_POST['tipo_documento'] ?? null, ['CC', 'PPT', 'CE']);
$id_usuario = InputSanitizer::document($tipo_documento, $_POST['id_usuario'] ?? null);
$id_rol = InputSanitizer::intValue($_POST['id_rol'] ?? null, 1);
$username = InputSanitizer::username((string) ($_POST['username'] ?? ''));
$primer_nombre = InputSanitizer::letters($_POST['primer_nombre'] ?? null, 30);
$segundo_nombre = InputSanitizer::letters($_POST['segundo_nombre'] ?? null, 30);
$primer_apellido = InputSanitizer::letters($_POST['primer_apellido'] ?? null, 30);
$segundo_apellido = InputSanitizer::letters($_POST['segundo_apellido'] ?? null, 30);
$correo = InputSanitizer::email((string) ($_POST['correo'] ?? ''));
$contrasenaRaw = (string) ($_POST['contrasena'] ?? '');

if ($tipo_documento === null || $id_usuario === null || $id_rol === null || $username === '' || !InputSanitizer::validateUsername($username) || $primer_nombre === null || $primer_apellido === null || !InputSanitizer::validateEmail($correo) || strlen($correo) > 40 || $contrasenaRaw === '') {
    header('Location: forma_usuarios.php?error=datos');
    exit();
}

$contrasena = password_hash($contrasenaRaw, PASSWORD_DEFAULT);

require_once __DIR__ . '/../../app/conexion.php';

$sentencia = $conexion->prepare('SELECT fun_insert_usuarios(?, ?, ?, ?, ?, ?, ?, ?, ?, ?);');
$sentencia->execute([
    $id_usuario,
    $id_rol,
    $username,
    $tipo_documento,
    $primer_nombre,
    $primer_apellido,
    $correo,
    $contrasena,
    $segundo_nombre,
    $segundo_apellido,
]);
$resultado = (string) $sentencia->fetchColumn();

if ($resultado === 'OK') {
    header('Location: listar_usuarios.php?success=' . urlencode('Usuario creado correctamente.'));
    exit();
}

header('Location: forma_usuarios.php?error=' . urlencode($resultado));
exit();
