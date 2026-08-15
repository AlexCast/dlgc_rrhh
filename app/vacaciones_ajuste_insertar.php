<?php
declare(strict_types=1);

/**
 * Registra un ajuste manual de saldo de vacaciones (reconoce días ya disfrutados históricamente,
 * p. ej. saldo inicial de migración). Por ley el ciclo causa exactamente 15 días: el ajuste solo
 * puede restar, nunca sumar. Requiere permiso 'crear' del módulo 29 (Gestión de Vacaciones).
 * Ledger append-only: para corregir un ajuste mal cargado se anula con vacaciones_ajuste_anular.php
 * y se crea uno nuevo.
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

if (!has_module_permission(29, 'crear')) {
    responderJson(false, 'No tienes permiso para registrar ajustes de vacaciones.', 403);
}

$idEmpleado = trim($_POST['id_empleado'] ?? '');
$diasAjuste = $_POST['dias_ajuste'] ?? null;
$motivo = trim($_POST['motivo'] ?? '');
$fechaAjuste = trim($_POST['fecha_ajuste'] ?? '') ?: date('Y-m-d');

if ($idEmpleado === '') {
    responderJson(false, 'Debes indicar el empleado.', 400);
}
if (!is_numeric($diasAjuste) || (float) $diasAjuste >= 0.0) {
    responderJson(false, 'Los días de ajuste deben ser un número negativo (solo se permite restar días ya disfrutados).', 400);
}
if (strlen($motivo) < 5) {
    responderJson(false, 'El motivo debe tener al menos 5 caracteres.', 400);
}

try {
    $sentencia = $conexion->prepare('SELECT fun_insert_vacaciones_ajustes(:id_empleado, :dias_ajuste, :motivo, :fecha_ajuste) AS mensaje');
    $sentencia->execute([
        ':id_empleado' => $idEmpleado,
        ':dias_ajuste' => $diasAjuste,
        ':motivo'      => $motivo,
        ':fecha_ajuste' => $fechaAjuste,
    ]);
    $mensaje = $sentencia->fetchColumn();

    $exito = is_string($mensaje) && stripos($mensaje, 'correctamente') !== false;
    responderJson($exito, (string) $mensaje, $exito ? 200 : 400);
} catch (Throwable $e) {
    error_log('Error en vacaciones_ajuste_insertar.php: ' . $e->getMessage());
    responderJson(false, 'Ocurrió un error al registrar el ajuste.', 500);
}
