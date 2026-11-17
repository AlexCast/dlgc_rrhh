<?php
declare(strict_types=1);

require_once __DIR__ . '/session_bootstrap.php';
require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/auth_guard.php';
require_once __DIR__ . '/helpers/InputSanitizer.php';

header('Content-Type: application/json; charset=utf-8');

function responderGestorAprobarJson(bool $success, string $message, int $httpCode = 200): void
{
    http_response_code($httpCode);
    echo json_encode(['success' => $success, 'message' => $message], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responderGestorAprobarJson(false, 'Método no permitido.', 405);
}

if (!csrf_validate(false)) {
    responderGestorAprobarJson(false, 'Token de seguridad inválido o ausente.', 403);
}

if (!has_module_access(30) || !has_module_permission(30, 'actualizar')) {
    responderGestorAprobarJson(false, 'No tienes permiso para aprobar documentos.', 403);
}

$idVersion = InputSanitizer::intValue($_POST['id_version'] ?? null, 1);
$idAprobador = $_SESSION['id_usuario'] ?? null;
if ($idVersion === null || !is_string($idAprobador) || trim($idAprobador) === '') {
    responderGestorAprobarJson(false, 'Los datos de aprobación no son válidos.', 400);
}

try {
    $sentencia = $conexion->prepare('CALL proc_aprobar_version_documento(?, ?)');
    $sentencia->execute([$idVersion, $idAprobador]);
    $resultado = (string) $sentencia->fetchColumn();
    $sentencia->closeCursor();

    $ok = stripos($resultado, 'correctamente') !== false;
    responderGestorAprobarJson($ok, $resultado, $ok ? 200 : 409);
} catch (PDOException $e) {
    error_log('Error en gestor_aprobar: ' . $e->getMessage());
    responderGestorAprobarJson(false, 'No se pudo aprobar la versión.', 500);
}
