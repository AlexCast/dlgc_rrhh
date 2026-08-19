<?php

$moduleId = 14;
$requiredAction = 'eliminar';
require_once __DIR__ . '/../../app/src_guard.php';

// Validate CSRF token before processing mutation.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate();
}
if (!isset($_POST['id_eps'])) {
    header('Location: listar_eps.php?error=datos');
    exit();
}

$id_eps = (int) $_POST['id_eps'];

if ($id_eps <= 0) {
    header('Location: listar_eps.php?error=id');
    exit();
}

require_once __DIR__ . '/../../app/conexion.php';

$sentencia = $conexion->prepare('SELECT fun_softdelete_eps(?);');
$sentencia->execute([$id_eps]);
$resultado = $sentencia->fetchColumn();
$ok = $resultado === true || $resultado === 1 || $resultado === '1' || $resultado === 't' || $resultado === 'true';

if ($ok) {
    header('Location: listar_eps.php?success=' . urlencode('EPS eliminada correctamente.'));
    exit();
}

header('Location: listar_eps.php?error=delete');
exit();
