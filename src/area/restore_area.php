<?php

$moduleId = 8;
$requiredAction = 'restaurar';
require_once __DIR__ . '/../../app/src_guard.php';

// Validate CSRF token before processing mutation.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate();
}
if (!isset($_POST['id_area'])) {
    header('Location: listar_area.php?error=datos');
    exit();
}

$id_area = (int) $_POST['id_area'];

if ($id_area <= 0) {
    header('Location: listar_area.php?error=id');
    exit();
}

require_once __DIR__ . '/../../app/conexion.php';

$sentencia = $conexion->prepare('SELECT fun_restore_area(?);');
$sentencia->execute([$id_area]);
$resultado = $sentencia->fetchColumn();
$ok = $resultado === true || $resultado === 1 || $resultado === '1' || $resultado === 't' || $resultado === 'true';

if ($ok) {
    header('Location: listar_area.php?restaurado=1');
    exit();
}

header('Location: listar_area.php?error=restore');
exit();
