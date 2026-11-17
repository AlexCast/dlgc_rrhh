<?php
declare(strict_types=1);

require_once __DIR__ . '/session_bootstrap.php';
require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/auth_guard.php';

header('Content-Type: application/json; charset=utf-8');

function responderGestorPendientesJson(bool $success, string $message, int $httpCode = 200, array $extra = []): void
{
    http_response_code($httpCode);
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $extra), JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    responderGestorPendientesJson(false, 'Método no permitido.', 405);
}

if (!has_module_access(30)) {
    responderGestorPendientesJson(false, 'No tienes acceso al Gestor de Archivos.', 403);
}

try {
    $sentencia = $conexion->prepare('CALL proc_listar_versiones_pendientes()');
    $sentencia->execute();
    $pendientes = $sentencia->fetchAll(PDO::FETCH_ASSOC);
    $sentencia->closeCursor();
    responderGestorPendientesJson(true, 'Pendientes obtenidos correctamente.', 200, ['pendientes' => $pendientes]);
} catch (PDOException $e) {
    error_log('Error en gestor_pendientes: ' . $e->getMessage());
    responderGestorPendientesJson(false, 'Ocurrió un error al cargar los pendientes.', 500);
}
