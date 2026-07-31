<?php

$moduleId = 12;
$requiredAction = 'eliminar';
require_once __DIR__ . '/../../app/src_guard.php';

// Validate CSRF token before processing mutation.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate();
}
if (!isset($_POST['id_cesantia'])) {
    header('Location: listar_cesantias.php?error=datos');
    exit();
}

$id_cesantia = (int) $_POST['id_cesantia'];

if ($id_cesantia <= 0) {
    header('Location: listar_cesantias.php?error=id');
    exit();
}

require_once __DIR__ . '/../../app/conexion.php';

$sentencia = $conexion->prepare('SELECT fun_softdelete_cesantias(?);');
$sentencia->execute([$id_cesantia]);
$resultado = $sentencia->fetchColumn();
$ok = $resultado === true || $resultado === 1 || $resultado === '1' || $resultado === 't' || $resultado === 'true';

if ($ok) {
    header('Location: listar_cesantias.php?success=' . urlencode('Cesantías eliminada correctamente.'));
    exit();
}

header('Location: listar_cesantias.php?error=delete');
exit();
