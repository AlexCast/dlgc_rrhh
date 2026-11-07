<?php
declare(strict_types=1);

/**
 * Endpoint del módulo Documentación (31): lista los documentos del empleado en sesión.
 *
 * Módulo transversal: no exige asignación previa en RBAC; basta con estar autenticado.
 * Solo se ejecutan procedimientos almacenados.
 */

require_once __DIR__ . '/session_bootstrap.php';
require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/auth_guard.php';

header('Content-Type: application/json; charset=utf-8');

function responderDocListarJson(bool $success, string $message, int $httpCode = 200, array $extra = []): void
{
    http_response_code($httpCode);
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $extra), JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    responderDocListarJson(false, 'Método no permitido.', 405);
}

$idUsuario = $_SESSION['id_usuario'] ?? null;
if (!is_string($idUsuario) || trim($idUsuario) === '') {
    responderDocListarJson(false, 'Sesión inválida.', 401);
}

try {
    $sentencia = $conexion->prepare('CALL proc_listar_documentos_usuario(?)');
    $sentencia->execute([$idUsuario]);
    $documentos = $sentencia->fetchAll(PDO::FETCH_ASSOC);
    $sentencia->closeCursor();

    responderDocListarJson(true, 'Listado obtenido correctamente.', 200, ['documentos' => $documentos]);
} catch (PDOException $e) {
    error_log('Error en doc_listar: ' . $e->getMessage());
    responderDocListarJson(false, 'Ocurrió un error al cargar los documentos.', 500);
}
