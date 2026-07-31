<?php

$moduleId = 20;
$requiredAction = 'restaurar';
require_once __DIR__ . '/../../app/src_guard.php';

// Validate CSRF token before processing mutation.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate();
}
if (!isset($_POST['id_usuario'])) {
    header('Location: listar_usuarios.php?error=datos');
    exit();
}

$id_usuario = trim($_POST['id_usuario']);

require_once __DIR__ . '/../../app/conexion.php';

$sentencia = $conexion->prepare('SELECT fun_restore_usuarios(?);');
$sentencia->execute([$id_usuario]);
$resultado = $sentencia->fetchColumn();
$ok = $resultado === true || $resultado === 1 || $resultado === '1' || $resultado === 't' || $resultado === 'true';

if ($ok) {
    header('Location: listar_usuarios.php?success=' . urlencode('Usuario restaurado correctamente.'));
    exit();
}

header('Location: listar_usuarios.php?error=restore');
exit();
