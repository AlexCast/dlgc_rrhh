<?php

$moduleId = 18;
$requiredAction = 'actualizar';
require_once __DIR__ . '/../../app/src_guard.php';

// Validate CSRF token before processing mutation.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate();
}
if (
    !isset($_POST['id_modulo']) ||
    !isset($_POST['nombre_modulo'])
) {
    header('Location: listar_modulos.php?error=datos');
    exit();
}

$id_modulo = htmlspecialchars(trim($_POST['id_modulo'] ?? ''));
$nombre_modulo = trim($_POST['nombre_modulo'] ?? '');

require_once __DIR__ . '/../../app/conexion.php';

$sentencia = $conexion->prepare('SELECT fun_update_modulos(:id_modulo, :nombre_modulo);');
$sentencia->execute([
    ':id_modulo' => $id_modulo,
        ':nombre_modulo' => $nombre_modulo,
]);
$resultado = (string) $sentencia->fetchColumn();

if (stripos($resultado, 'funcion') !== false || stripos($resultado, 'correctamente') !== false || stripos($resultado, 'Esta vaina funcionó') !== false) {
    header('Location: listar_modulos.php?success=' . urlencode('Módulo actualizado correctamente.'));
    exit();
}

header('Location: listar_modulos.php?error=' . urlencode($resultado));
exit();
