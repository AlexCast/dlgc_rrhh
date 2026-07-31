<?php

$moduleId = 21;
$requiredAction = 'actualizar';
require_once __DIR__ . '/../../app/src_guard.php';

// Validate CSRF token before processing mutation.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate();
}
if (
    !isset($_POST['id_usuario_actual']) ||
    !isset($_POST['id_operacion_actual']) ||
    !isset($_POST['id_usuario_nuevo']) ||
    !isset($_POST['id_operacion_nueva'])
) {
    header('Location: listar_permisos_usuarios.php?error=datos');
    exit();
}

$id_usuario_actual = trim($_POST['id_usuario_actual']);
$id_operacion_actual = (int) $_POST['id_operacion_actual'];
$id_usuario_nuevo = trim($_POST['id_usuario_nuevo']);
$id_operacion_nueva = (int) $_POST['id_operacion_nueva'];

if ($id_usuario_actual === '' || $id_operacion_actual <= 0 || $id_usuario_nuevo === '' || $id_operacion_nueva <= 0) {
    header('Location: listar_permisos_usuarios.php?error=ids');
    exit();
}

require_once __DIR__ . '/../../app/conexion.php';

$sentencia = $conexion->prepare('SELECT fun_update_permisos_usuarios(?, ?, ?, ?);');
$sentencia->execute([$id_usuario_actual, $id_operacion_actual, $id_usuario_nuevo, $id_operacion_nueva]);
$resultado = (string) $sentencia->fetchColumn();

if (stripos($resultado, 'correctamente') !== false) {
    header('Location: listar_permisos_usuarios.php?success=' . urlencode('Permiso de usuario actualizado correctamente.'));
    exit();
}

header('Location: listar_permisos_usuarios.php?error=' . urlencode($resultado));
exit();
