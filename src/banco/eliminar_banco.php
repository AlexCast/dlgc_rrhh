<?php

$moduleId = 10;
$requiredAction = 'eliminar';
require_once __DIR__ . '/../../app/src_guard.php';

// Validate CSRF token before processing mutation.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate();
}
if (!isset($_POST['id_banco'])) {
    header('Location: listar_banco.php?error=datos');
    exit();
}

$id_banco = (int) $_POST['id_banco'];

if ($id_banco <= 0) {
    header('Location: listar_banco.php?error=id');
    exit();
}

require_once __DIR__ . '/../../app/conexion.php';

$sentencia = $conexion->prepare('SELECT fun_softdelete_banco(?);');
$sentencia->execute([$id_banco]);
$resultado = $sentencia->fetchColumn();
$ok = $resultado === true || $resultado === 1 || $resultado === '1' || $resultado === 't' || $resultado === 'true';

if ($ok) {
    header('Location: listar_banco.php');
    exit();
}

header('Location: listar_banco.php?error=delete');
exit();
