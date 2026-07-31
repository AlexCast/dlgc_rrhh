<?php

$moduleId = 11;
$requiredAction = 'eliminar';
require_once __DIR__ . '/../../app/src_guard.php';

// Validate CSRF token before processing mutation.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate();
}
if (!isset($_POST['id_caja'])) {
    header('Location: listar_cajacompensacion.php?error=datos');
    exit();
}

$id_caja = (int) $_POST['id_caja'];

if ($id_caja <= 0) {
    header('Location: listar_cajacompensacion.php?error=id');
    exit();
}

require_once __DIR__ . '/../../app/conexion.php';

$sentencia = $conexion->prepare('SELECT fun_softdelete_cajacompensacion(?);');
$sentencia->execute([$id_caja]);
$resultado = $sentencia->fetchColumn();
$ok = $resultado === true || $resultado === 1 || $resultado === '1' || $resultado === 't' || $resultado === 'true';

if ($ok) {
    header('Location: listar_cajacompensacion.php?success=' . urlencode('Caja de compensación eliminada correctamente.'));
    exit();
}

header('Location: listar_cajacompensacion.php?error=delete');
exit();
