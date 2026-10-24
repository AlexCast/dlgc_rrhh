<?php

$moduleId = 15;
$requiredAction = 'crear';
require_once __DIR__ . '/../../app/src_guard.php';

// Validate CSRF token before processing mutation.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate();
}
if (
    !isset($_POST['id_usuario']) ||
    !isset($_POST['id_banco']) ||
    !isset($_POST['num_cuenta']) ||
    !isset($_POST['salario'])
) {
    header('Location: forma_nomina.php?error=datos');
    exit();
}

require_once __DIR__ . '/../../app/helpers/InputSanitizer.php';

$id_usuario = InputSanitizer::boundedText($_POST['id_usuario'] ?? null, 20);
$id_banco = InputSanitizer::intValue($_POST['id_banco'] ?? null, 1);
$num_cuenta = InputSanitizer::digits($_POST['num_cuenta'] ?? null, 20);
$salario = InputSanitizer::decimalString($_POST['salario'] ?? null, 0.0);

if ($id_usuario === null || $id_banco === null || $num_cuenta === null || $salario === null) {
    header('Location: forma_nomina.php?error=datos');
    exit();
}

require_once __DIR__ . '/../../app/conexion.php';

$sentencia = $conexion->prepare('SELECT fun_insert_nomina(?, ?, ?, ?);');
$sentencia->execute([
    $id_usuario,
    $id_banco,
    $num_cuenta,
    $salario
]);
$resultado = (string) $sentencia->fetchColumn();

if (stripos($resultado, 'funcion') !== false || stripos($resultado, 'correctamente') !== false) {
    header('Location: listar_nomina.php?success=' . urlencode('Nómina creada correctamente.'));
    exit();
}

header('Location: forma_nomina.php?error=' . urlencode($resultado));
exit();