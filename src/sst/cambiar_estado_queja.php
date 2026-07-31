<?php

declare(strict_types=1);

$moduleId = 26;
$requiredAction = 'actualizar';
require_once __DIR__ . '/../../app/src_guard.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate();
}

if (
    !isset($_POST['id_queja']) ||
    !isset($_POST['estado'])
) {
    header('Location: listar_quejas.php?error=datos');
    exit();
}

$idQueja = (int) trim($_POST['id_queja']);
$estado = trim((string) $_POST['estado']);
$respuesta = trim((string) ($_POST['respuesta'] ?? ''));
$idEncargado = $_SESSION['id_usuario'] ?? null;

if ($idQueja <= 0 || $estado === '' || !is_string($idEncargado) || trim($idEncargado) === '') {
    header('Location: listar_quejas.php?error=datos');
    exit();
}

if (!in_array($estado, ['EN PROCESO', 'RESUELTO', 'CANCELADO_ENCARGADO'], true)) {
    header('Location: listar_quejas.php?error=estado');
    exit();
}

require_once __DIR__ . '/../../app/conexion.php';

$sentencia = $conexion->prepare('SELECT fun_cambiar_estado_sst_queja(?, ?, ?, ?);');
$sentencia->execute([$idQueja, $idEncargado, $estado, $respuesta]);
$resultado = (string) $sentencia->fetchColumn();

if (stripos($resultado, 'correctamente') !== false) {
    header('Location: listar_quejas.php?success=' . urlencode('Estado actualizado correctamente.'));
    exit();
}

header('Location: listar_quejas.php?error=' . urlencode($resultado));
exit();
