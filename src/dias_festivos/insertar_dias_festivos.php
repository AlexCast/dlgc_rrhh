<?php

$moduleId = 28;
$requiredAction = 'crear';
require_once __DIR__ . '/../../app/src_guard.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate();
}
if (!isset($_POST['fecha'], $_POST['descripcion'])) {
    header('Location: forma_dias_festivos.php?error=datos');
    exit();
}

require_once __DIR__ . '/../../app/helpers/InputSanitizer.php';

$fecha = InputSanitizer::date($_POST['fecha'] ?? null);
$descripcion = InputSanitizer::boundedText($_POST['descripcion'] ?? null, 100);
$descuentaSalario = isset($_POST['descuenta_salario']) ? 'true' : 'false';

if ($fecha === null || $descripcion === null) {
    header('Location: forma_dias_festivos.php?error=datos');
    exit();
}

require_once __DIR__ . '/../../app/conexion.php';

$sentencia = $conexion->prepare('SELECT fun_insert_dias_festivos(?, ?, ?);');
$sentencia->execute([$fecha, $descripcion, $descuentaSalario]);
$resultado = (string) $sentencia->fetchColumn();

if (stripos($resultado, 'correctamente') !== false) {
    header('Location: listar_dias_festivos.php?success=' . urlencode('Festivo de empresa creado correctamente.'));
    exit();
}

header('Location: forma_dias_festivos.php?error=' . urlencode($resultado));
exit();
