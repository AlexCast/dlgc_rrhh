<?php

$moduleId = 16;
$requiredAction = 'crear';
require_once __DIR__ . '/../../app/src_guard.php';

// Validate CSRF token before processing mutation.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate();
}
if (
    !isset($_POST['nombre_pension'])
) {
    header('Location: forma_pension.php?error=datos');
    exit();
}

$nombre_pension = trim((string) $_POST['nombre_pension']);

require_once __DIR__ . '/../../app/conexion.php';

$sentencia = $conexion->prepare('SELECT fun_insert_pension(?);');
$sentencia->execute([$nombre_pension]);
$resultado = (string) $sentencia->fetchColumn();

if (stripos($resultado, 'funcion') !== false || stripos($resultado, 'correctamente') !== false) {
    header('Location: listar_pension.php?success=' . urlencode('Pensión creada correctamente.'));
    exit();
}

header('Location: forma_pension.php?error=' . urlencode($resultado));
exit();
