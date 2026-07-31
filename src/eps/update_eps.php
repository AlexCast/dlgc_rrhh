<?php

$moduleId = 14;
$requiredAction = 'actualizar';
require_once __DIR__ . '/../../app/src_guard.php';

// Validate CSRF token before processing mutation.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate();
}
if (
    !isset($_POST['id_eps']) ||
    !isset($_POST['nombre_eps'])
) {
    header('Location: listar_eps.php?error=datos');
    exit();
}

$id_eps = (int) $_POST['id_eps'];
$nombre_eps = trim((string) $_POST['nombre_eps']);

if ($id_eps <= 0) {
    header('Location: listar_eps.php?error=id');
    exit();
}

require_once __DIR__ . '/../../app/conexion.php';

$sentencia = $conexion->prepare('SELECT fun_update_eps(?, ?);');
$sentencia->execute([$id_eps, $nombre_eps]);
$resultado = (string) $sentencia->fetchColumn();

if (stripos($resultado, 'funcion') !== false || stripos($resultado, 'correctamente') !== false) {
    header('Location: listar_eps.php?success=' . urlencode('EPS actualizada correctamente.'));
    exit();
}

header('Location: listar_eps.php?error=' . urlencode($resultado));
exit();
