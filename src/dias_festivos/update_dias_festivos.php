<?php

$moduleId = 28;
$requiredAction = 'actualizar';
require_once __DIR__ . '/../../app/src_guard.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate();
}
if (!isset($_POST['id_festivo'], $_POST['fecha'], $_POST['descripcion'])) {
    header('Location: listar_dias_festivos.php?error=datos');
    exit();
}

require_once __DIR__ . '/../../app/helpers/InputSanitizer.php';

$id_festivo = InputSanitizer::intValue($_POST['id_festivo'] ?? null, 1);
$fecha = InputSanitizer::date($_POST['fecha'] ?? null);
$descripcion = InputSanitizer::boundedText($_POST['descripcion'] ?? null, 100);
$descuentaSalario = isset($_POST['descuenta_salario']) ? 1 : 0;

if ($id_festivo === null || $fecha === null || $descripcion === null) {
    header('Location: listar_dias_festivos.php?error=datos');
    exit();
}

require_once __DIR__ . '/../../app/conexion.php';

$sentencia = $conexion->prepare('SELECT fun_update_dias_festivos(?, ?, ?, ?);');
$sentencia->execute([$id_festivo, $fecha, $descripcion, $descuentaSalario]);
$resultado = (string) $sentencia->fetchColumn();

if (stripos($resultado, 'correctamente') !== false) {
    header('Location: listar_dias_festivos.php?success=' . urlencode('Festivo de empresa actualizado correctamente.'));
    exit();
}

header('Location: listar_dias_festivos.php?error=' . urlencode($resultado));
exit();
