<?php

$moduleId = 16;
$requiredAction = 'eliminar';
require_once __DIR__ . '/../../app/src_guard.php';

// Validate CSRF token before processing mutation.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate();
}
if (!isset($_POST['id_pension'])) {
    header('Location: listar_pension.php?error=datos');
    exit();
}

$id_pension = (int) $_POST['id_pension'];

if ($id_pension <= 0) {
    header('Location: listar_pension.php?error=id');
    exit();
}

require_once __DIR__ . '/../../app/conexion.php';

$sentencia = $conexion->prepare('SELECT fun_softdelete_pension(?);');
$sentencia->execute([$id_pension]);
$resultado = $sentencia->fetchColumn();
$ok = $resultado === true || $resultado === 1 || $resultado === '1' || $resultado === 't' || $resultado === 'true';

if ($ok) {
    header('Location: listar_pension.php?success=' . urlencode('Pensión eliminada correctamente.'));
    exit();
}

header('Location: listar_pension.php?error=delete');
exit();
