<?php

$moduleId = 19;
$requiredAction = 'actualizar';
require_once __DIR__ . '/../../app/src_guard.php';

// Validate CSRF token before processing mutation.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate();
}
if (
    !isset($_POST['id_operacion']) ||
    !isset($_POST['id_modulo']) ||
    !isset($_POST['nombre_operacion'])
) {
    header('Location: listar_operaciones.php?error=datos');
    exit();
}

$id_operacion = htmlspecialchars(trim($_POST['id_operacion'] ?? ''));
$id_modulo = trim($_POST['id_modulo'] ?? '');
$nombre_operacion = trim($_POST['nombre_operacion'] ?? '');

require_once __DIR__ . '/../../app/conexion.php';

$sentencia = $conexion->prepare('SELECT fun_update_operaciones(:id_operacion, :id_modulo, :nombre_operacion);');
$sentencia->execute([
    ':id_operacion' => $id_operacion,
        ':id_modulo' => $id_modulo,
        ':nombre_operacion' => $nombre_operacion,
]);
$resultado = (string) $sentencia->fetchColumn();

if (stripos($resultado, 'funcion') !== false || stripos($resultado, 'correctamente') !== false || stripos($resultado, 'Esta vaina funcionó') !== false) {
    header('Location: listar_operaciones.php?success=' . urlencode('Operación actualizada correctamente.'));
    exit();
}

header('Location: listar_operaciones.php?error=' . urlencode($resultado));
exit();
