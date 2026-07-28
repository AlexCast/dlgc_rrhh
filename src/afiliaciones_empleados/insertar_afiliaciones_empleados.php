<?php

$moduleId = 7;
$requiredAction = 'crear';
require_once __DIR__ . '/../../app/src_guard.php';

// Validate CSRF token before processing mutation.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate();
}
if (
    !isset($_POST['id_usuario']) ||
    !isset($_POST['id_eps']) ||
    !isset($_POST['id_arl']) ||
    !isset($_POST['id_caja']) ||
    !isset($_POST['id_pension']) ||
    !isset($_POST['id_cesantia'])
) {
    header('Location: forma_afiliaciones_empleados.php?error=datos');
    exit();
}

$id_usuario = trim((string) $_POST['id_usuario']);
$id_eps = (int) $_POST['id_eps'];
$id_arl = (int) $_POST['id_arl'];
$id_caja = (int) $_POST['id_caja'];
$id_pension = (int) $_POST['id_pension'];
$id_cesantia = (int) $_POST['id_cesantia'];

require_once __DIR__ . '/../../app/conexion.php';

$sentencia = $conexion->prepare('SELECT fun_insert_afiliaciones_empleados(?, ?, ?, ?, ?, ?);');
$sentencia->execute([
    $id_usuario,
    $id_eps,
    $id_arl,
    $id_caja,
    $id_pension,
    $id_cesantia
]);
$resultado = (string) $sentencia->fetchColumn();

if (stripos($resultado, 'funcion') !== false || stripos($resultado, 'correctamente') !== false) {
    header('Location: listar_afiliaciones_empleados.php');
    exit();
}

header('Location: forma_afiliaciones_empleados.php?error=' . urlencode($resultado));
exit();