<?php

$moduleId = 13;
$requiredAction = 'eliminar';
require_once __DIR__ . '/../../app/src_guard.php';

// Validate CSRF token before processing mutation.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate();
}
if (!isset($_POST['id_contrato'])) {
    header('Location: listar_contratos_empleados.php?error=datos');
    exit();
}

$id_contrato = trim((string) $_POST['id_contrato']);

if ($id_contrato === '') {
    header('Location: listar_contratos_empleados.php?error=id');
    exit();
}

require_once __DIR__ . '/../../app/conexion.php';

$sentencia = $conexion->prepare('SELECT fun_softdelete_contratos_empleados(?);');
$sentencia->execute([$id_contrato]);
$resultado = $sentencia->fetchColumn();
$ok = $resultado === true || $resultado === 1 || $resultado === '1' || $resultado === 't' || $resultado === 'true';

if ($ok) {
    header('Location: listar_contratos_empleados.php');
    exit();
}

header('Location: listar_contratos_empleados.php?error=delete');
exit();
