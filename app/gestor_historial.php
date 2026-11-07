<?php
declare(strict_types=1);

/**
 * Endpoint del Gestor de Archivos (30): historial de versiones de un documento.
 *
 * Acceso restringido por RBAC. Solo se ejecutan procedimientos almacenados.
 */

require_once __DIR__ . '/session_bootstrap.php';
require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/auth_guard.php';
require_once __DIR__ . '/helpers/InputSanitizer.php';

header('Content-Type: application/json; charset=utf-8');

function responderGestorHistorialJson(bool $success, string $message, int $httpCode = 200, array $extra = []): void
{
    http_response_code($httpCode);
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $extra), JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    responderGestorHistorialJson(false, 'Método no permitido.', 405);
}

if (!has_module_access(30)) {
    responderGestorHistorialJson(false, 'No tienes acceso al Gestor de Archivos.', 403);
}

$idDocumento = InputSanitizer::intValue($_GET['id_documento'] ?? null, 1);
if ($idDocumento === null) {
    responderGestorHistorialJson(false, 'El identificador del documento no es válido.', 400);
}

try {
    $sentencia = $conexion->prepare('CALL proc_listar_versiones_documento(?)');
    $sentencia->execute([$idDocumento]);
    $versiones = $sentencia->fetchAll(PDO::FETCH_ASSOC);
    $sentencia->closeCursor();

    responderGestorHistorialJson(true, 'Historial obtenido correctamente.', 200, ['versiones' => $versiones]);
} catch (PDOException $e) {
    error_log('Error en gestor_historial: ' . $e->getMessage());
    responderGestorHistorialJson(false, 'Ocurrió un error al cargar el historial de versiones.', 500);
}
