<?php

$moduleId = 4;
$requiredAction = 'eliminar';
require_once __DIR__ . '/../../app/src_guard.php';

// Validate CSRF token before processing mutation.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate();
}
if (!isset($_POST['id_usuario'])) {
    header('Location: listar_empleados.php?error=datos');
    exit();
}

$id_usuario = trim((string) $_POST['id_usuario']);

if ($id_usuario === '') {
    header('Location: listar_empleados.php?error=id');
    exit();
}

require_once __DIR__ . '/../../app/conexion.php';

$sentencia = $conexion->prepare('SELECT fun_softdelete_empleados(?);');
$sentencia->execute([$id_usuario]);
$resultado = $sentencia->fetchColumn();
$ok = $resultado === true || $resultado === 1 || $resultado === '1' || $resultado === 't' || $resultado === 'true';

if ($ok) {
    header('Location: listar_empleados.php?success=' . urlencode('Empleado eliminado correctamente.'));
    exit();
}

header('Location: listar_empleados.php?error=delete');
exit();
