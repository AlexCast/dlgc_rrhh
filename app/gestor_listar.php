<?php
declare(strict_types=1);

/**
 * Endpoint del Gestor de Archivos (30): lista los documentos con su archivo vigente.
 *
 * Acceso restringido por RBAC (auth_guard + t_operaciones/t_roles_operaciones).
 * Solo se ejecutan procedimientos almacenados.
 */

require_once __DIR__ . '/session_bootstrap.php';
require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/auth_guard.php';

header('Content-Type: application/json; charset=utf-8');

function responderGestorListarJson(bool $success, string $message, int $httpCode = 200, array $extra = []): void
{
    http_response_code($httpCode);
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $extra), JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    responderGestorListarJson(false, 'Método no permitido.', 405);
}

if (!has_module_access(30)) {
    responderGestorListarJson(false, 'No tienes acceso al Gestor de Archivos.', 403);
}

try {
    $sentencia = $conexion->prepare('CALL proc_listar_documentos_gestor()');
    $sentencia->execute();
    $documentos = $sentencia->fetchAll(PDO::FETCH_ASSOC);
    $sentencia->closeCursor();

    responderGestorListarJson(true, 'Listado obtenido correctamente.', 200, ['documentos' => $documentos]);
} catch (PDOException $e) {
    error_log('Error en gestor_listar: ' . $e->getMessage());
    responderGestorListarJson(false, 'Ocurrió un error al cargar los documentos.', 500);
}
