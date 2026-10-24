<?php

$moduleId = 4;
$requiredAction = 'crear';
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
    header('Location: forma_empleados.php?error=datos');
    exit();
}

require_once __DIR__ . '/../../app/helpers/InputSanitizer.php';

$id_usuario = InputSanitizer::boundedText($_POST['id_usuario'] ?? null, 20);
$id_jefe = InputSanitizer::boundedText($_POST['id_jefe'] ?? null, 20);
$id_municipio = InputSanitizer::intValue($_POST['id_municipio'] ?? null, 1);
$fecha_ingreso = InputSanitizer::date($_POST['fecha_ingreso'] ?? null);
$fecha_egreso = InputSanitizer::date($_POST['fecha_egreso'] ?? null);
$genero = InputSanitizer::whitelist($_POST['genero'] ?? null, ['MASCULINO', 'FEMENINO', 'OTRO']);
$fecha_nacimiento = InputSanitizer::date($_POST['fecha_nacimiento'] ?? null);
$tipo_sangre = InputSanitizer::whitelist($_POST['tipo_sangre'] ?? null, ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-']);
$estado_civil = InputSanitizer::whitelist($_POST['estado_civil'] ?? null, ['SOLTERO(A)', 'CASADO(A)', 'DIVORCIADO(A)', 'VIUDO(A)', 'UNION LIBRE', 'SEPARADO(A)']);
$direccion_casa = InputSanitizer::boundedText($_POST['direccion_casa'] ?? null, 100);
$numero_celular = InputSanitizer::digits($_POST['numero_celular'] ?? null, 10);
$foto_perfil = InputSanitizer::boundedText($_POST['foto_perfil'] ?? null, 255);

if ($id_usuario === null || $id_municipio === null || $fecha_ingreso === null || $genero === null || $fecha_nacimiento === null || $tipo_sangre === null || $estado_civil === null || $direccion_casa === null || $numero_celular === null) {
    header('Location: forma_empleados.php?error=datos');
    exit();
}

require_once __DIR__ . '/../../app/conexion.php';

$sentencia = $conexion->prepare('SELECT fun_insert_empleados(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?);');
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
    header('Location: listar_empleados.php?success=' . urlencode('Empleado creado correctamente.'));
    exit();
}

header('Location: forma_empleados.php?error=' . urlencode($resultado));
exit();