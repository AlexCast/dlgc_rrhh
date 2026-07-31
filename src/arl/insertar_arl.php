<?php

$moduleId = 9;
$requiredAction = 'crear';
require_once __DIR__ . '/../../app/src_guard.php';

// Validate CSRF token before processing mutation.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate();
}
if (
    !isset($_POST['nombre_arl'])
) {
    header('Location: forma_arl.php?error=datos');
    exit();
}

$nombre_arl = trim((string) $_POST['nombre_arl']);

require_once __DIR__ . '/../../app/conexion.php';

$sentencia = $conexion->prepare('SELECT fun_insert_arl(?);');
$sentencia->execute([$nombre_arl]);
$resultado = (string) $sentencia->fetchColumn();

if (stripos($resultado, 'funcion') !== false || stripos($resultado, 'correctamente') !== false) {
    header('Location: listar_arl.php?success=' . urlencode('ARL creada correctamente.'));
    exit();
}

header('Location: forma_arl.php?error=' . urlencode($resultado));
exit();
