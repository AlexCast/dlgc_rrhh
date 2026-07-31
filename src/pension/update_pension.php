<?php

$moduleId = 16;
$requiredAction = 'actualizar';
require_once __DIR__ . '/../../app/src_guard.php';

// Validate CSRF token before processing mutation.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate();
}
if (
    !isset($_POST['id_pension']) ||
    !isset($_POST['nombre_pension'])
) {
    header('Location: listar_pension.php?error=datos');
    exit();
}

$id_pension = (int) $_POST['id_pension'];
$nombre_pension = trim((string) $_POST['nombre_pension']);

if ($id_pension <= 0) {
    header('Location: listar_pension.php?error=id');
    exit();
}

require_once __DIR__ . '/../../app/conexion.php';

$sentencia = $conexion->prepare('SELECT fun_update_pension(?, ?);');
$sentencia->execute([$id_pension, $nombre_pension]);
$resultado = (string) $sentencia->fetchColumn();

if (stripos($resultado, 'funcion') !== false || stripos($resultado, 'correctamente') !== false) {
    header('Location: listar_pension.php?success=' . urlencode('Pensión actualizada correctamente.'));
    exit();
}

header('Location: listar_pension.php?error=' . urlencode($resultado));
exit();
