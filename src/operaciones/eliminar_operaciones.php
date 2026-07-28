<?php

$moduleId = 19;
$requiredAction = 'eliminar';
require_once __DIR__ . '/../../app/src_guard.php';

// Validate CSRF token before processing mutation.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate();
}
if (!isset($_POST['id_operacion'])) {
    header('Location: listar_operaciones.php?error=datos');
    exit();
}

$id_operacion = trim($_POST['id_operacion']);

require_once __DIR__ . '/../../app/conexion.php';

$sentencia = $conexion->prepare('SELECT fun_softdelete_operaciones(?);');
$sentencia->execute([$id_operacion]);
$resultado = $sentencia->fetchColumn();
$ok = $resultado === true || $resultado === 1 || $resultado === '1' || $resultado === 't' || $resultado === 'true';

if ($ok) {
    header('Location: listar_operaciones.php');
    exit();
}

header('Location: listar_operaciones.php?error=delete');
exit();
