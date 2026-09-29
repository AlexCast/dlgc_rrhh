<?php

$moduleId = 6;
$requiredAction = 'restaurar';
require_once __DIR__ . '/../../app/src_guard.php';

// Validate CSRF token before processing mutation.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate();
}
if (!isset($_POST['id_rol']) || !isset($_POST['id_operacion'])) {
    header('Location: listar_roles_operaciones.php?error=datos');
    exit();
}

$id_rol = (int) $_POST['id_rol'];
$id_operacion = (int) $_POST['id_operacion'];

if ($id_rol <= 0 || $id_operacion <= 0) {
    header('Location: listar_roles_operaciones.php?error=ids');
    exit();
}

require_once __DIR__ . '/../../app/conexion.php';

$sentencia = $conexion->prepare('SELECT fun_restore_roles_operaciones(?, ?);');
$sentencia->execute([$id_rol, $id_operacion]);
$resultado = $sentencia->fetchColumn();
$ok = $resultado === true || $resultado === 1 || $resultado === '1' || $resultado === 't' || $resultado === 'true';

if ($ok) {
    header('Location: listar_roles_operaciones.php?success=' . urlencode('Rol-Operación restaurado correctamente.'));
    exit();
}

header('Location: listar_roles_operaciones.php?error=restore');
exit();
