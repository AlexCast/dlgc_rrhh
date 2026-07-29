<?php
declare(strict_types=1);

/**
 * Endpoint para actualizar una queja/sugerencia propia dentro de los 5 minutos.
 */

session_start();

require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/auth_guard.php';

header('Content-Type: application/json; charset=utf-8');

function responderJson(bool $success, string $message, int $httpCode = 200): void
{
    http_response_code($httpCode);
    echo json_encode(['success' => $success, 'message' => $message], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responderJson(false, 'Método no permitido.', 405);
}

if (!csrf_validate(false)) {
    responderJson(false, 'Token de seguridad inválido o ausente. Recarga la página e inténtalo de nuevo.', 403);
}

if (!has_module_access(25)) {
    responderJson(false, 'No tienes acceso al módulo SST.', 403);
}

$idUsuario = $_SESSION['id_usuario'] ?? null;
if (!is_string($idUsuario) || trim($idUsuario) === '') {
    responderJson(false, 'Sesión inválida.', 401);
}

$idQueja       = filter_input(INPUT_POST, 'id_queja', FILTER_VALIDATE_INT);
$tipoPeticion  = strtoupper(trim($_POST['tipo_peticion'] ?? ''));
$asunto        = trim($_POST['asunto'] ?? '');
$descripcion   = trim($_POST['descripcion'] ?? '');

if (!$idQueja || $idQueja <= 0) {
    responderJson(false, 'El ID de la queja no es válido.', 400);
}

if (!in_array($tipoPeticion, ['QUEJA', 'SUGERENCIA', 'RECLAMO', 'DENUNCIA'], true)) {
    responderJson(false, 'El tipo de petición no es válido.', 400);
}

if ($asunto === '' || mb_strlen($asunto) < 5) {
    responderJson(false, 'El asunto debe tener al menos 5 caracteres.', 400);
}

if ($descripcion === '' || mb_strlen($descripcion) < 10) {
    responderJson(false, 'La descripción debe tener al menos 10 caracteres.', 400);
}

try {
    $sentencia = $conexion->prepare('SELECT fun_update_sst_queja(:id_queja, :id_usuario, :tipo_peticion, :asunto, :descripcion);');
    $sentencia->execute([
        ':id_queja'       => $idQueja,
        ':id_usuario'     => $idUsuario,
        ':tipo_peticion'  => $tipoPeticion,
        ':asunto'         => $asunto,
        ':descripcion'    => $descripcion,
    ]);

    $resultado = (string) $sentencia->fetchColumn();

    if (stripos($resultado, 'correctamente') === false) {
        responderJson(false, $resultado, 400);
    }

    responderJson(true, $resultado);
} catch (PDOException $e) {
    error_log('Error en sst_actualizar_queja: ' . $e->getMessage());
    responderJson(false, 'Ocurrió un error al actualizar la solicitud.', 500);
}
