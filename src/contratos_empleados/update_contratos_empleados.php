<?php

$moduleId = 13;
$requiredAction = 'actualizar';
require_once __DIR__ . '/../../app/src_guard.php';

// Validate CSRF token before processing mutation.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate();
}
if (
    !isset($_POST['id_contrato']) ||
    !isset($_POST['id_usuario']) ||
    !isset($_POST['id_area']) ||
    !isset($_POST['puesto']) ||
    !isset($_POST['fecha_inicio_puesto']) ||
    !isset($_POST['tipo_contrato']) ||
    !isset($_POST['direccion_oficina'])
) {
    header('Location: listar_contratos_empleados.php?error=datos');
    exit();
}

$id_contrato = (int) $_POST['id_contrato'];
$id_usuario = trim((string) $_POST['id_usuario']);
$id_area = (int) $_POST['id_area'];
$puesto = trim((string) $_POST['puesto']);
$fecha_inicio_puesto = trim((string) $_POST['fecha_inicio_puesto']);
$fecha_fin_puesto = !empty(trim((string) $_POST['fecha_fin_puesto'])) ? trim((string) $_POST['fecha_fin_puesto']) : null;
$tipo_contrato = trim((string) $_POST['tipo_contrato']);
$direccion_oficina = trim((string) $_POST['direccion_oficina']);

if ($id_contrato <= 0) {
    header('Location: listar_contratos_empleados.php?error=id');
    exit();
}

require_once __DIR__ . '/../../app/conexion.php';

$sentencia = $conexion->prepare('SELECT fun_update_contratos_empleados(?, ?, ?, ?, ?, ?, ?, ?);');
$sentencia->execute([
    $id_usuario,
    $id_contrato,
    $id_area,
    $puesto,
    $fecha_inicio_puesto,
    $tipo_contrato,
    $direccion_oficina,
    $fecha_fin_puesto
]);
$resultado = (string) $sentencia->fetchColumn();

if (stripos($resultado, 'funcion') !== false || stripos($resultado, 'correctamente') !== false) {
    header('Location: listar_contratos_empleados.php?success=' . urlencode('Contrato actualizado correctamente.'));
    exit();
}

header('Location: listar_contratos_empleados.php?error=' . urlencode($resultado));
exit();