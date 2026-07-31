<?php

$moduleId = 10;
$requiredAction = 'crear';
require_once __DIR__ . '/../../app/src_guard.php';

// Validate CSRF token before processing mutation.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate();
}
if (
    !isset($_POST['nombre_banco'])
) {
    header('Location: forma_banco.php?error=datos');
    exit();
}

$nombre_banco = trim((string) $_POST['nombre_banco']);

require_once __DIR__ . '/../../app/conexion.php';

$sentencia = $conexion->prepare('SELECT fun_insert_banco(?);');
$sentencia->execute([$nombre_banco]);
$resultado = (string) $sentencia->fetchColumn();

if (stripos($resultado, 'funcion') !== false || stripos($resultado, 'correctamente') !== false) {
    header('Location: listar_banco.php?success=' . urlencode('Banco creado correctamente.'));
    exit();
}

header('Location: forma_banco.php?error=' . urlencode($resultado));
exit();
