<?php

declare(strict_types=1);

$moduleId = 24;
$requiredAction = 'restaurar';
require_once __DIR__ . '/../../app/src_guard.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate();
}

if (!isset($_POST['id_comunicado'])) {
    header('Location: listar_comunicados.php?error=datos');
    exit();
}

$id_comunicado = (int) trim($_POST['id_comunicado']);

if ($id_comunicado <= 0) {
    header('Location: listar_comunicados.php?error=id');
    exit();
}

require_once __DIR__ . '/../../app/conexion.php';

$sentencia = $conexion->prepare('SELECT fun_restore_comunicados(?);');
$sentencia->execute([$id_comunicado]);
$resultado = $sentencia->fetchColumn();
$ok = $resultado === true || $resultado === 1 || $resultado === '1' || $resultado === 't' || $resultado === 'true';

if ($ok) {
    header('Location: listar_comunicados.php?success=' . urlencode('Comunicado restaurado correctamente.'));
    exit();
}

header('Location: listar_comunicados.php?error=restore');
exit();
