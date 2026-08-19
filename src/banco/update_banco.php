<?php

$moduleId = 10;
$requiredAction = 'actualizar';
require_once __DIR__ . '/../../app/src_guard.php';

// Validate CSRF token before processing mutation.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate();
}
if (
    !isset($_POST['id_banco']) ||
    !isset($_POST['nombre_banco'])
) {
    header('Location: listar_banco.php?error=datos');
    exit();
}

$id_banco = (int) $_POST['id_banco'];
$nombre_banco = trim((string) $_POST['nombre_banco']);

if ($id_banco <= 0) {
    header('Location: listar_banco.php?error=id');
    exit();
}

require_once __DIR__ . '/../../app/conexion.php';

$sentencia = $conexion->prepare('SELECT fun_update_banco(?, ?);');
$sentencia->execute([$id_banco, $nombre_banco]);
$resultado = (string) $sentencia->fetchColumn();

if (stripos($resultado, 'funcion') !== false || stripos($resultado, 'correctamente') !== false) {
    header('Location: listar_banco.php?success=' . urlencode('Banco actualizado correctamente.'));
    exit();
}

header('Location: listar_banco.php?error=' . urlencode($resultado));
exit();
