<?php

$moduleId = 2;
$requiredAction = 'crear';
require_once __DIR__ . '/../../app/src_guard.php';

// Validate CSRF token before processing mutation.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate();
}
if (
    !isset($_POST['nombre_soat'])
) {
    header('Location: forma_soat.php?error=datos');
    exit();
}

require_once __DIR__ . '/../../app/helpers/InputSanitizer.php';

$nombre_soat = InputSanitizer::boundedText($_POST['nombre_soat'] ?? null, 50);

if ($nombre_soat === null) {
    header('Location: forma_soat.php?error=datos');
    exit();
}

require_once __DIR__ . '/../../app/conexion.php';

$sentencia = $conexion->prepare('SELECT fun_insert_soat(?);');
$sentencia->execute([$nombre_soat]);
$resultado = (string) $sentencia->fetchColumn();

if (stripos($resultado, 'funcion') !== false || stripos($resultado, 'correctamente') !== false) {
    header('Location: listar_soat.php?success=' . urlencode('SOAT creado correctamente.'));
    exit();
}

header('Location: forma_soat.php?error=' . urlencode($resultado));
exit();
