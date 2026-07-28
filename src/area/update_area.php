<?php

$moduleId = 8;
$requiredAction = 'actualizar';
require_once __DIR__ . '/../../app/src_guard.php';

// Validate CSRF token before processing mutation.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate();
}
if (
    !isset($_POST['id_area']) ||
    !isset($_POST['nombre_area'])
) {
    header('Location: listar_area.php?error=datos');
    exit();
}

$id_area = (int) $_POST['id_area'];
$nombre_area = trim((string) $_POST['nombre_area']);

if ($id_area <= 0) {
    header('Location: listar_area.php?error=id');
    exit();
}

require_once __DIR__ . '/../../app/conexion.php';

$sentencia = $conexion->prepare('SELECT fun_update_area(?, ?);');
$sentencia->execute([$id_area, $nombre_area]);
$resultado = (string) $sentencia->fetchColumn();

if (stripos($resultado, 'funcion') !== false || stripos($resultado, 'correctamente') !== false) {
    header('Location: listar_area.php');
    exit();
}

header('Location: listar_area.php?error=' . urlencode($resultado));
exit();
