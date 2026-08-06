<?php
declare(strict_types=1);

/**
 * Anula (borrado lógico) un ajuste manual de vacaciones cargado por error. Requiere permiso
 * 'eliminar' del módulo 29 (Gestión de Vacaciones). No edita valores: para reflejar la corrección
 * se debe crear un ajuste nuevo.
 */

session_start();

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

if (!has_module_permission(29, 'eliminar')) {
    responderJson(false, 'No tienes permiso para anular ajustes de vacaciones.', 403);
}

$idAjuste = (int) ($_POST['id_ajuste'] ?? 0);
if ($idAjuste <= 0) {
    responderJson(false, 'El ID del ajuste no es válido.', 400);
}

try {
    $sentencia = $conexion->prepare('SELECT fun_softdelete_vacaciones_ajustes(:id_ajuste) AS anulado');
    $sentencia->execute([':id_ajuste' => $idAjuste]);
    $anulado = filter_var($sentencia->fetchColumn(), FILTER_VALIDATE_BOOLEAN);

    responderJson($anulado, $anulado ? 'Ajuste anulado correctamente.' : 'No se encontró un ajuste activo con ese ID.', $anulado ? 200 : 404);
} catch (Throwable $e) {
    error_log('Error en vacaciones_ajuste_anular.php: ' . $e->getMessage());
    responderJson(false, 'Ocurrió un error al anular el ajuste.', 500);
}
