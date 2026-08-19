<?php

declare(strict_types=1);

$moduleId = 5;
require_once __DIR__ . '/../../app/auth_guard.php';
require_module_access(5);

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Método no permitido']);
    exit();
}

csrf_validate(false);

$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) {
    $input = $_POST;
}

$id_comunicado = isset($input['id_comunicado']) ? (int) $input['id_comunicado'] : 0;
$id_usuario = $_SESSION['id_usuario'] ?? '';

if ($id_comunicado <= 0 || $id_usuario === '') {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Datos incompletos']);
    exit();
}

require_once __DIR__ . '/../../app/conexion.php';

$sentencia = $conexion->prepare('SELECT fun_marcar_visto_comunicado(?, ?);');
$sentencia->execute([$id_comunicado, $id_usuario]);
$resultado = $sentencia->fetchColumn();
$ok = $resultado === true || $resultado === 1 || $resultado === '1' || $resultado === 't' || $resultado === 'true';

if ($ok) {
    echo json_encode(['ok' => true]);
    exit();
}

http_response_code(500);
echo json_encode(['ok' => false, 'error' => 'No se pudo marcar como visto']);
exit();
