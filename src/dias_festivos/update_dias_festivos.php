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

$id_festivo = (int) $_POST['id_festivo'];
$fecha = trim((string) $_POST['fecha']);
$descripcion = trim((string) $_POST['descripcion']);
$descuentaSalario = isset($_POST['descuenta_salario']) ? 'true' : 'false';

if ($id_festivo <= 0) {
    header('Location: listar_dias_festivos.php?error=id');
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
