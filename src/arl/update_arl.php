<?php

$moduleId = 9;
$requiredAction = 'actualizar';
require_once __DIR__ . '/../../app/src_guard.php';

// Validate CSRF token before processing mutation.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate();
}
if (
    !isset($_POST['id_arl']) ||
    !isset($_POST['nombre_arl'])
) {
    header('Location: listar_arl.php?error=datos');
    exit();
}

$id_arl = (int) $_POST['id_arl'];
$nombre_arl = trim((string) $_POST['nombre_arl']);

if ($id_arl <= 0) {
    header('Location: listar_arl.php?error=id');
    exit();
}

require_once __DIR__ . '/../../app/conexion.php';

$sentencia = $conexion->prepare('SELECT fun_update_arl(?, ?);');
$sentencia->execute([$id_arl, $nombre_arl]);
$resultado = (string) $sentencia->fetchColumn();

if (stripos($resultado, 'funcion') !== false || stripos($resultado, 'correctamente') !== false) {
    header('Location: listar_arl.php?success=' . urlencode('ARL actualizada correctamente.'));
    exit();
}

header('Location: listar_arl.php?error=' . urlencode($resultado));
exit();
