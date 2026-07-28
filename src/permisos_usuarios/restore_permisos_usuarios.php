<?php

$moduleId = 21;
$requiredAction = 'restaurar';
require_once __DIR__ . '/../../app/src_guard.php';

// Validate CSRF token before processing mutation.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate();
}
if (!isset($_POST['id_usuario']) || !isset($_POST['id_operacion'])) {
    header('Location: listar_permisos_usuarios.php?error=datos');
    exit();
}

$id_usuario = trim($_POST['id_usuario']);
$id_operacion = (int) $_POST['id_operacion'];

if ($id_usuario === '' || $id_operacion <= 0) {
    header('Location: listar_permisos_usuarios.php?error=ids');
    exit();
}

require_once __DIR__ . '/../../app/conexion.php';

$sentencia = $conexion->prepare('SELECT fun_restore_permisos_usuarios(?, ?);');
$sentencia->execute([$id_usuario, $id_operacion]);
$resultado = $sentencia->fetchColumn();
$ok = $resultado === true || $resultado === 1 || $resultado === '1' || $resultado === 't' || $resultado === 'true';

if ($ok) {
    header('Location: listar_permisos_usuarios.php?restaurado=1');
    exit();
}

header('Location: listar_permisos_usuarios.php?error=restore');
exit();
