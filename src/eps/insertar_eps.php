<?php

$moduleId = 14;
$requiredAction = 'crear';
require_once __DIR__ . '/../../app/src_guard.php';

// Validate CSRF token before processing mutation.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate();
}
if (
    !isset($_POST['nombre_eps'])
) {
    header('Location: forma_eps.php?error=datos');
    exit();
}

$nombre_eps = trim((string) $_POST['nombre_eps']);

require_once __DIR__ . '/../../app/conexion.php';

$sentencia = $conexion->prepare('SELECT fun_insert_eps(?);');
$sentencia->execute([$nombre_eps]);
$resultado = (string) $sentencia->fetchColumn();

if (stripos($resultado, 'funcion') !== false || stripos($resultado, 'correctamente') !== false) {
    header('Location: listar_eps.php?success=' . urlencode('EPS creada correctamente.'));
    exit();
}

header('Location: forma_eps.php?error=' . urlencode($resultado));
exit();
