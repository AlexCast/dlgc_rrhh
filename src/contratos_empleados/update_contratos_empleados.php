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

require_once __DIR__ . '/../../app/helpers/InputSanitizer.php';

$id_contrato = InputSanitizer::intValue($_POST['id_contrato'] ?? null, 1);
$id_usuario = InputSanitizer::boundedText($_POST['id_usuario'] ?? null, 20);
$id_area = InputSanitizer::intValue($_POST['id_area'] ?? null, 1);
$puesto = InputSanitizer::boundedText($_POST['puesto'] ?? null, 50);
$fecha_inicio_puesto = InputSanitizer::date($_POST['fecha_inicio_puesto'] ?? null);
$fecha_fin_puesto = InputSanitizer::date($_POST['fecha_fin_puesto'] ?? null);
$tipo_contrato = InputSanitizer::whitelist($_POST['tipo_contrato'] ?? null, ['FIJO', 'INDEFINIDO', 'PRACTICAS']);
$direccion_oficina = InputSanitizer::boundedText($_POST['direccion_oficina'] ?? null, 100);

if ($id_contrato === null || $id_usuario === null || $id_area === null || $puesto === null || $fecha_inicio_puesto === null || $tipo_contrato === null || $direccion_oficina === null) {
    header('Location: listar_contratos_empleados.php?error=datos');
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