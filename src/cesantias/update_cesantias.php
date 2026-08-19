<?php

$moduleId = 12;
$requiredAction = 'actualizar';
require_once __DIR__ . '/../../app/src_guard.php';

// Validate CSRF token before processing mutation.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate();
}
if (
    !isset($_POST['id_cesantia']) ||
    !isset($_POST['nombre_cesantia'])
) {
    header('Location: listar_cesantias.php?error=datos');
    exit();
}

$id_cesantia = (int) $_POST['id_cesantia'];
$nombre_cesantia = trim((string) $_POST['nombre_cesantia']);

if ($id_cesantia <= 0) {
    header('Location: listar_cesantias.php?error=id');
    exit();
}

require_once __DIR__ . '/../../app/conexion.php';

$sentencia = $conexion->prepare('SELECT fun_update_cesantias(?, ?);');
$sentencia->execute([$id_cesantia, $nombre_cesantia]);
$resultado = (string) $sentencia->fetchColumn();

if (stripos($resultado, 'funcion') !== false || stripos($resultado, 'correctamente') !== false) {
    header('Location: listar_cesantias.php?success=' . urlencode('Cesantías actualizada correctamente.'));
    exit();
}

header('Location: listar_cesantias.php?error=' . urlencode($resultado));
exit();
