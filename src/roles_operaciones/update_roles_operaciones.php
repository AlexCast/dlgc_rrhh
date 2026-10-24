<?php

$moduleId = 6;
$requiredAction = 'actualizar';
require_once __DIR__ . '/../../app/src_guard.php';

// Validate CSRF token before processing mutation.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate();
}
if (
    !isset($_POST['id_rol_actual']) ||
    !isset($_POST['id_operacion_actual']) ||
    !isset($_POST['id_rol_nuevo']) ||
    !isset($_POST['id_operacion_nueva'])
) {
    header('Location: listar_roles_operaciones.php?error=datos');
    exit();
}

require_once __DIR__ . '/../../app/helpers/InputSanitizer.php';

$id_rol_actual = InputSanitizer::intValue($_POST['id_rol_actual'] ?? null, 1);
$id_operacion_actual = InputSanitizer::intValue($_POST['id_operacion_actual'] ?? null, 1);
$id_rol_nuevo = InputSanitizer::intValue($_POST['id_rol_nuevo'] ?? null, 1);
$id_operacion_nueva = InputSanitizer::intValue($_POST['id_operacion_nueva'] ?? null, 1);

if ($id_rol_actual === null || $id_operacion_actual === null || $id_rol_nuevo === null || $id_operacion_nueva === null) {
    header('Location: listar_roles_operaciones.php?error=ids');
    exit();
}

require_once __DIR__ . '/../../app/conexion.php';

$sentencia = $conexion->prepare('SELECT fun_update_roles_operaciones(?, ?, ?, ?);');
$sentencia->execute([$id_rol_actual, $id_operacion_actual, $id_rol_nuevo, $id_operacion_nueva]);
$resultado = (string) $sentencia->fetchColumn();

if (stripos($resultado, 'correctamente') !== false) {
    header('Location: listar_roles_operaciones.php?success=' . urlencode('Rol-Operación actualizado correctamente.'));
    exit();
}

header('Location: listar_roles_operaciones.php?error=' . urlencode($resultado));
exit();
