<?php

$moduleId = 28;
$requiredAction = 'restaurar';
require_once __DIR__ . '/../../app/src_guard.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate();
}
if (!isset($_POST['id_festivo'])) {
    header('Location: listar_dias_festivos.php?error=datos');
    exit();
}

$id_festivo = (int) $_POST['id_festivo'];

if ($id_festivo <= 0) {
    header('Location: listar_dias_festivos.php?error=id');
    exit();
}

require_once __DIR__ . '/../../app/conexion.php';

$sentencia = $conexion->prepare('SELECT fun_restore_dias_festivos(?);');
$sentencia->execute([$id_festivo]);
$resultado = $sentencia->fetchColumn();
$ok = $resultado === true || $resultado === 1 || $resultado === '1' || $resultado === 't' || $resultado === 'true';

if ($ok) {
    header('Location: listar_dias_festivos.php?success=' . urlencode('Festivo de empresa restaurado correctamente.'));
    exit();
}

header('Location: listar_dias_festivos.php?error=' . urlencode('No se pudo restaurar el festivo.'));
exit();
