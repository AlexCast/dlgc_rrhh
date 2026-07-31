<?php

$moduleId = 18;
$requiredAction = 'restaurar';
require_once __DIR__ . '/../../app/src_guard.php';

// Validate CSRF token before processing mutation.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate();
}
if (!isset($_POST['id_modulo'])) {
    header('Location: listar_modulos.php?error=datos');
    exit();
}

$id_modulo = trim($_POST['id_modulo']);

require_once __DIR__ . '/../../app/conexion.php';

$sentencia = $conexion->prepare('SELECT fun_restore_modulos(?);');
$sentencia->execute([$id_modulo]);
$resultado = $sentencia->fetchColumn();
$ok = $resultado === true || $resultado === 1 || $resultado === '1' || $resultado === 't' || $resultado === 'true';

if ($ok) {
    header('Location: listar_modulos.php?success=' . urlencode('Módulo restaurado correctamente.'));
    exit();
}

header('Location: listar_modulos.php?error=restore');
exit();
