<?php

$moduleId = 18;
$requiredAction = 'crear';
require_once __DIR__ . '/../../app/src_guard.php';

// Validate CSRF token before processing mutation.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate();
}
if (
    !isset($_POST['id_modulo']) ||
    !isset($_POST['nombre_modulo'])
) {
    header('Location: forma_modulos.php?error=datos');
    exit();
}

require_once __DIR__ . '/../../app/helpers/InputSanitizer.php';

$id_modulo = InputSanitizer::intValue($_POST['id_modulo'] ?? null, 1);
$nombre_modulo = InputSanitizer::boundedText($_POST['nombre_modulo'] ?? null, 30);

if ($id_modulo === null || $nombre_modulo === null) {
    header('Location: forma_modulos.php?error=datos');
    exit();
}

require_once __DIR__ . '/../../app/conexion.php';

$sentencia = $conexion->prepare('SELECT fun_insert_modulos(?, ?);');
$sentencia->execute([$id_modulo, $nombre_modulo]);
$resultado = (string) $sentencia->fetchColumn();

if (stripos($resultado, 'funcion') !== false || stripos($resultado, 'correctamente') !== false || stripos($resultado, 'Esta vaina funcionó') !== false) {
    header('Location: listar_modulos.php?success=' . urlencode('Módulo creado correctamente.'));
    exit();
}

header('Location: forma_modulos.php?error=' . urlencode($resultado));
exit();
