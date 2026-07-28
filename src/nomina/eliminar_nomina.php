<?php

$moduleId = 15;
$requiredAction = 'eliminar';
require_once __DIR__ . '/../../app/src_guard.php';

// Validate CSRF token before processing mutation.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate();
}
if (!isset($_POST['id_nomina'])) {
    header('Location: listar_nomina.php?error=datos');
    exit();
}

$id_nomina = (int) $_POST['id_nomina'];

if ($id_nomina <= 0) {
    header('Location: listar_nomina.php?error=id');
    exit();
}

require_once __DIR__ . '/../../app/conexion.php';

$sentencia = $conexion->prepare('SELECT fun_softdelete_nomina(?);');
$sentencia->execute([$id_nomina]);
$resultado = $sentencia->fetchColumn();
$ok = $resultado === true || $resultado === 1 || $resultado === '1' || $resultado === 't' || $resultado === 'true';

if ($ok) {
    header('Location: listar_nomina.php');
    exit();
}

header('Location: listar_nomina.php?error=delete');
exit();
