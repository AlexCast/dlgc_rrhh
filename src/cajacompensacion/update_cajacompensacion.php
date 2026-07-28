<?php

$moduleId = 11;
$requiredAction = 'actualizar';
require_once __DIR__ . '/../../app/src_guard.php';

// Validate CSRF token before processing mutation.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate();
}
if (
    !isset($_POST['id_caja']) ||
    !isset($_POST['nombre_caja'])
) {
    header('Location: listar_cajacompensacion.php?error=datos');
    exit();
}

$id_caja = (int) $_POST['id_caja'];
$nombre_caja = trim((string) $_POST['nombre_caja']);

if ($id_caja <= 0) {
    header('Location: listar_cajacompensacion.php?error=id');
    exit();
}

require_once __DIR__ . '/../../app/conexion.php';

$sentencia = $conexion->prepare('SELECT fun_update_cajacompensacion(?, ?);');
$sentencia->execute([$id_caja, $nombre_caja]);
$resultado = (string) $sentencia->fetchColumn();

if (stripos($resultado, 'funcion') !== false || stripos($resultado, 'correctamente') !== false) {
    header('Location: listar_cajacompensacion.php');
    exit();
}

header('Location: listar_cajacompensacion.php?error=' . urlencode($resultado));
exit();