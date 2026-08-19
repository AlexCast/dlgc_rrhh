<?php

$moduleId = 11;
$requiredAction = 'crear';
require_once __DIR__ . '/../../app/src_guard.php';

// Validate CSRF token before processing mutation.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate();
}
if (
    !isset($_POST['nombre_caja'])
) {
    header('Location: forma_cajacompensacion.php?error=datos');
    exit();
}

$nombre_caja = trim((string) $_POST['nombre_caja']);

require_once __DIR__ . '/../../app/conexion.php';

$sentencia = $conexion->prepare('SELECT fun_insert_cajacompensacion(?);');
$sentencia->execute([$nombre_caja]);
$resultado = (string) $sentencia->fetchColumn();

if (stripos($resultado, 'funcion') !== false || stripos($resultado, 'correctamente') !== false) {
    header('Location: listar_cajacompensacion.php?success=' . urlencode('Caja de compensación creada correctamente.'));
    exit();
}

header('Location: forma_cajacompensacion.php?error=' . urlencode($resultado));
exit();