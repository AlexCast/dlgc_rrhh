<?php

$moduleId = 21;
$requiredAction = 'crear';
require_once __DIR__ . '/../../app/src_guard.php';

// Validate CSRF token before processing mutation.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate();
}
if (!isset($_POST['id_usuario']) || !isset($_POST['id_operacion'])) {
    header('Location: forma_permisos_usuarios.php?error=datos');
    exit();
}

$id_usuario = trim($_POST['id_usuario']);
$id_operacion = (int) $_POST['id_operacion'];

if ($id_usuario === '' || $id_operacion <= 0) {
    header('Location: forma_permisos_usuarios.php?error=ids');
    exit();
}

require_once __DIR__ . '/../../app/conexion.php';

$sentencia = $conexion->prepare('SELECT fun_insert_permisos_usuarios(?, ?);');
$sentencia->execute([$id_usuario, $id_operacion]);
$resultado = (string) $sentencia->fetchColumn();

if (stripos($resultado, 'correctamente') !== false) {
    header('Location: listar_permisos_usuarios.php');
    exit();
}

header('Location: forma_permisos_usuarios.php?error=' . urlencode($resultado));
exit();
