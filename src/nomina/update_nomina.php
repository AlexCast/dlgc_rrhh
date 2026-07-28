<?php

$moduleId = 15;
$requiredAction = 'actualizar';
require_once __DIR__ . '/../../app/src_guard.php';

// Validate CSRF token before processing mutation.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate();
}
if (
    !isset($_POST['id_nomina']) ||
    !isset($_POST['id_usuario']) ||
    !isset($_POST['id_banco']) ||
    !isset($_POST['num_cuenta']) ||
    !isset($_POST['salario'])
) {
    header('Location: listar_nomina.php?error=datos');
    exit();
}

$id_nomina = (int) $_POST['id_nomina'];
$id_usuario = trim((string) $_POST['id_usuario']);
$id_banco = (int) $_POST['id_banco'];
$num_cuenta = trim((string) $_POST['num_cuenta']);
$salario = trim((string) $_POST['salario']);

if ($id_nomina <= 0) {
    header('Location: listar_nomina.php?error=id');
    exit();
}

require_once __DIR__ . '/../../app/conexion.php';

$sentencia = $conexion->prepare('SELECT fun_update_nomina(?, ?, ?, ?, ?);');
$sentencia->execute([
    $id_nomina,
    $id_usuario,
    $id_banco,
    $num_cuenta,
    $salario
]);
$resultado = (string) $sentencia->fetchColumn();

if (stripos($resultado, 'funcion') !== false || stripos($resultado, 'correctamente') !== false) {
    header('Location: listar_nomina.php');
    exit();
}

header('Location: listar_nomina.php?error=' . urlencode($resultado));
exit();