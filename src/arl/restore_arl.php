<?php

$moduleId = 9;
$requiredAction = 'restaurar';
require_once __DIR__ . '/../../app/src_guard.php';

// Validate CSRF token before processing mutation.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate();
}
if (!isset($_POST['id_arl'])) {
    header('Location: listar_arl.php?error=datos');
    exit();
}

$id_arl = (int) $_POST['id_arl'];

if ($id_arl <= 0) {
    header('Location: listar_arl.php?error=id');
    exit();
}

require_once __DIR__ . '/../../app/conexion.php';

$sentencia = $conexion->prepare('SELECT fun_restore_arl(?);');
$sentencia->execute([$id_arl]);
$resultado = $sentencia->fetchColumn();
$ok = $resultado === true || $resultado === 1 || $resultado === '1' || $resultado === 't' || $resultado === 'true';

if ($ok) {
    header('Location: listar_arl.php?success=' . urlencode('ARL restaurada correctamente.'));
    exit();
}

header('Location: listar_arl.php?error=restore');
exit();
