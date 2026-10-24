<?php

$moduleId = 7;
$requiredAction = 'actualizar';
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
    header('Location: listar_afiliaciones_empleados.php?error=datos');
    exit();
}

require_once __DIR__ . '/../../app/helpers/InputSanitizer.php';

$id_usuario = InputSanitizer::boundedText($_POST['id_usuario'] ?? null, 20);
$id_eps = InputSanitizer::intValue($_POST['id_eps'] ?? null, 1);
$id_arl = InputSanitizer::intValue($_POST['id_arl'] ?? null, 1);
$id_caja = InputSanitizer::intValue($_POST['id_caja'] ?? null, 1);
$id_pension = InputSanitizer::intValue($_POST['id_pension'] ?? null, 1);
$id_cesantia = InputSanitizer::intValue($_POST['id_cesantia'] ?? null, 1);

if ($id_usuario === null || $id_eps === null || $id_arl === null || $id_caja === null || $id_pension === null || $id_cesantia === null) {
    header('Location: listar_afiliaciones_empleados.php?error=datos');
    exit();
}

require_once __DIR__ . '/../../app/conexion.php';

$sentencia = $conexion->prepare('SELECT fun_update_afiliaciones_empleados(?, ?, ?, ?, ?, ?);');
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
    header('Location: listar_afiliaciones_empleados.php?success=' . urlencode('Afiliación actualizada correctamente.'));
    exit();
}

header('Location: listar_afiliaciones_empleados.php?error=' . urlencode($resultado));
exit();