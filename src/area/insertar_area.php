<?php

$moduleId = 8;
$requiredAction = 'crear';
require_once __DIR__ . '/../../app/src_guard.php';

// Validate CSRF token before processing mutation.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate();
}
if (
    !isset($_POST['nombre_area'])
) {
    header('Location: forma_area.php?error=datos');
    exit();
}

$nombre_area = trim((string) $_POST['nombre_area']);

require_once __DIR__ . '/../../app/conexion.php';

$sentencia = $conexion->prepare('SELECT fun_insert_area(?);');
$sentencia->execute([$nombre_area]);
$resultado = (string) $sentencia->fetchColumn();

if (stripos($resultado, 'funcion') !== false || stripos($resultado, 'correctamente') !== false) {
    header('Location: listar_area.php?success=' . urlencode('Área creada correctamente.'));
    exit();
}

header('Location: forma_area.php?error=' . urlencode($resultado));
exit();