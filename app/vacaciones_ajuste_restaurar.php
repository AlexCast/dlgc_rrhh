<?php
declare(strict_types=1);

/**
 * Revierte la anulación de un ajuste de vacaciones. Requiere permiso 'restaurar' del módulo 29.
 */

require_once __DIR__ . '/session_bootstrap.php';
require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/auth_guard.php';

header('Content-Type: application/json; charset=utf-8');

function responderJson(bool $success, string $message, int $httpCode = 200, array $extra = []): void
{
    http_response_code($httpCode);
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $extra), JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responderJson(false, 'Método no permitido.', 405);
}

if (!csrf_validate(false)) {
    responderJson(false, 'Token de seguridad inválido o ausente. Recarga la página e inténtalo de nuevo.', 403);
}

if (!has_module_permission(29, 'restaurar')) {
    responderJson(false, 'No tienes permiso para restaurar ajustes de vacaciones.', 403);
}

$idAjuste = (int) ($_POST['id_ajuste'] ?? 0);
if ($idAjuste <= 0) {
    responderJson(false, 'El ID del ajuste no es válido.', 400);
}

try {
    $sentencia = $conexion->prepare('SELECT fun_restore_vacaciones_ajustes(:id_ajuste) AS restaurado');
    $sentencia->execute([':id_ajuste' => $idAjuste]);
    $restaurado = filter_var($sentencia->fetchColumn(), FILTER_VALIDATE_BOOLEAN);

    responderJson($restaurado, $restaurado ? 'Ajuste restaurado correctamente.' : 'No se encontró un ajuste anulado con ese ID.', $restaurado ? 200 : 404);
} catch (Throwable $e) {
    error_log('Error en vacaciones_ajuste_restaurar.php: ' . $e->getMessage());
    responderJson(false, 'Ocurrió un error al restaurar el ajuste.', 500);
}
