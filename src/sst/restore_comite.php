<?php

declare(strict_types=1);

$moduleId = 26;
$requiredAction = 'restaurar';
require_once __DIR__ . '/../../app/src_guard.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate();
}

if (!isset($_POST['id_miembro'])) {
    header('Location: listar_comite.php?error=datos');
    exit();
}

$idMiembro = (int) trim($_POST['id_miembro']);

if ($idMiembro <= 0) {
    header('Location: listar_comite.php?error=id');
    exit();
}

require_once __DIR__ . '/../../app/conexion.php';

$sentencia = $conexion->prepare('SELECT fun_restore_sst_comite_miembro(?);');
$sentencia->execute([$idMiembro]);
$resultado = $sentencia->fetchColumn();
$ok = $resultado === true || $resultado === 1 || $resultado === '1' || $resultado === 't' || $resultado === 'true';

if ($ok) {
    header('Location: listar_comite.php?restaurado=1');
    exit();
}

header('Location: listar_comite.php?error=restore');
exit();
