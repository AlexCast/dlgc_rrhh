<?php

$moduleId = 2;
$requiredAction = 'restaurar';
require_once __DIR__ . '/../../app/src_guard.php';

// Validate CSRF token before processing mutation.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate();
}
if (!isset($_POST['id_soat'])) {
    header('Location: listar_soat.php?error=datos');
    exit();
}

$id_soat = (int) $_POST['id_soat'];

if ($id_soat <= 0) {
    header('Location: listar_soat.php?error=id');
    exit();
}

require_once __DIR__ . '/../../app/conexion.php';

$sentencia = $conexion->prepare('SELECT fun_restore_soat(?);');
$sentencia->execute([$id_soat]);
$resultado = $sentencia->fetchColumn();
$ok = $resultado === true || $resultado === 1 || $resultado === '1' || $resultado === 't' || $resultado === 'true';

if ($ok) {
    header('Location: listar_soat.php?success=' . urlencode('SOAT restaurado correctamente.'));
    exit();
}

header('Location: listar_soat.php?error=restore');
exit();
