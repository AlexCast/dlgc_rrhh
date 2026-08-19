<?php

$moduleId = 17;
$requiredAction = 'crear';
require_once __DIR__ . '/../../app/src_guard.php';

// Validate CSRF token before processing mutation.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate();
}
if (
    !isset($_POST['id_rol']) ||
    !isset($_POST['nombre_rol'])
) {
    header('Location: forma_roles.php?error=datos');
    exit();
}

$id_rol = (int) trim($_POST['id_rol'] ?? 0);
$nombre_rol = trim($_POST['nombre_rol'] ?? '');

require_once __DIR__ . '/../../app/conexion.php';

$sentencia = $conexion->prepare('SELECT fun_insert_roles(:id_rol, :nombre_rol);');
$sentencia->execute([
    ':id_rol' => id_rol,
        ':nombre_rol' => nombre_rol,
]);
$resultado = (string) $sentencia->fetchColumn();

if (stripos($resultado, 'funcion') !== false || stripos($resultado, 'correctamente') !== false || stripos($resultado, 'Esta vaina funcionó') !== false) {
    header('Location: listar_roles.php?success=' . urlencode('Rol creado correctamente.'));
    exit();
}

header('Location: forma_roles.php?error=' . urlencode($resultado));
exit();
