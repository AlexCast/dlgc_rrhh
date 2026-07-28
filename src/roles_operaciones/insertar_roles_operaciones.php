<?php

$moduleId = 6;
$requiredAction = 'crear';
require_once __DIR__ . '/../../app/src_guard.php';

// Validate CSRF token before processing mutation.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate();
}
if (!isset($_POST['id_rol']) || !isset($_POST['id_operacion'])) {
    header('Location: forma_roles_operaciones.php?error=datos');
    exit();
}

$id_rol = (int) $_POST['id_rol'];
$id_operacion = (int) $_POST['id_operacion'];

if ($id_rol <= 0 || $id_operacion <= 0) {
    header('Location: forma_roles_operaciones.php?error=ids');
    exit();
}

require_once __DIR__ . '/../../app/conexion.php';

$sentencia = $conexion->prepare('SELECT fun_insert_roles_operaciones(?, ?);');
$sentencia->execute([$id_rol, $id_operacion]);
$resultado = (string) $sentencia->fetchColumn();

if (stripos($resultado, 'correctamente') !== false) {
    header('Location: listar_roles_operaciones.php');
    exit();
}

header('Location: forma_roles_operaciones.php?error=' . urlencode($resultado));
exit();
