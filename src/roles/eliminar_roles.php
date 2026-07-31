<?php

$moduleId = 17;
$requiredAction = 'eliminar';
require_once __DIR__ . '/../../app/src_guard.php';

// Validate CSRF token before processing mutation.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate();
}
if (!isset($_POST['id_rol'])) {
    header('Location: listar_roles.php?error=datos');
    exit();
}

$id_rol = trim($_POST['id_rol']);

require_once __DIR__ . '/../../app/conexion.php';

$sentencia = $conexion->prepare('SELECT fun_softdelete_roles(?);');
$sentencia->execute([$id_rol]);
$resultado = $sentencia->fetchColumn();
$ok = $resultado === true || $resultado === 1 || $resultado === '1' || $resultado === 't' || $resultado === 'true';

if ($ok) {
    header('Location: listar_roles.php?success=' . urlencode('Rol eliminado correctamente.'));
    exit();
}

header('Location: listar_roles.php?error=delete');
exit();
