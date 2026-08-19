<?php

$moduleId = 4;
$requiredAction = 'actualizar';
require_once __DIR__ . '/../../app/src_guard.php';

// Validate CSRF token before processing mutation.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate();
}
if (
    !isset($_POST['id_usuario']) ||
    !isset($_POST['id_municipio']) ||
    !isset($_POST['fecha_ingreso']) ||
    !isset($_POST['genero']) ||
    !isset($_POST['fecha_nacimiento']) ||
    !isset($_POST['tipo_sangre']) ||
    !isset($_POST['estado_civil']) ||
    !isset($_POST['direccion_casa']) ||
    !isset($_POST['numero_celular'])
) {
    header('Location: listar_empleados.php?error=datos');
    exit();
}

$id_usuario = trim((string) $_POST['id_usuario']);
$id_jefe = !empty(trim((string) $_POST['id_jefe'])) ? trim((string) $_POST['id_jefe']) : null;
$id_municipio = (int) trim((string) $_POST['id_municipio']);
$fecha_ingreso = trim((string) $_POST['fecha_ingreso']);
$fecha_egreso = !empty(trim((string) $_POST['fecha_egreso'])) ? trim((string) $_POST['fecha_egreso']) : null;
$genero = trim((string) $_POST['genero']);
$fecha_nacimiento = trim((string) $_POST['fecha_nacimiento']);
$tipo_sangre = trim((string) $_POST['tipo_sangre']);
$estado_civil = trim((string) $_POST['estado_civil']);
$direccion_casa = trim((string) $_POST['direccion_casa']);
$numero_celular = trim((string) $_POST['numero_celular']);
$foto_perfil = !empty(trim((string) $_POST['foto_perfil'])) ? trim((string) $_POST['foto_perfil']) : null;

if ($id_usuario === '') {
    header('Location: listar_empleados.php?error=id');
    exit();
}

if ($id_municipio <= 0) {
    header('Location: listar_empleados.php?error=municipio');
    exit();
}

require_once __DIR__ . '/../../app/conexion.php';

$sentencia = $conexion->prepare('SELECT fun_update_empleados(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?);');
$sentencia->execute([
    $id_usuario,
    $fecha_ingreso,
    $genero,
    $fecha_nacimiento,
    $tipo_sangre,
    $estado_civil,
    $direccion_casa,
    $numero_celular,
    $id_jefe,
    $id_municipio,
    $fecha_egreso,
    $foto_perfil
]);
$resultado = (string) $sentencia->fetchColumn();

if (stripos($resultado, 'funcion') !== false || stripos($resultado, 'correctamente') !== false) {
    header('Location: listar_empleados.php?success=' . urlencode('Empleado actualizado correctamente.'));
    exit();
}

header('Location: listar_empleados.php?error=' . urlencode($resultado));
exit();