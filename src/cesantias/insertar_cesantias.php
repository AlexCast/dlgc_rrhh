<?php

$moduleId = 12;
$requiredAction = 'crear';
require_once __DIR__ . '/../../app/src_guard.php';

// Validate CSRF token before processing mutation.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate();
}
if (
    !isset($_POST['nombre_cesantia'])
) {
    header('Location: forma_cesantias.php?error=datos');
    exit();
}

$nombre_cesantia = trim((string) $_POST['nombre_cesantia']);

require_once __DIR__ . '/../../app/conexion.php';

$sentencia = $conexion->prepare('SELECT fun_insert_cesantias(?);');
$sentencia->execute([$nombre_cesantia]);
$resultado = (string) $sentencia->fetchColumn();

if (stripos($resultado, 'funcion') !== false || stripos($resultado, 'correctamente') !== false) {
    header('Location: listar_cesantias.php');
    exit();
}

header('Location: forma_cesantias.php?error=' . urlencode($resultado));
exit();
