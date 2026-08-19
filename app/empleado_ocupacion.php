<?php
declare(strict_types=1);

/**
 * Devuelve los rangos de fechas de permisos/vacaciones APROBADOS de un empleado que se
 * solapan con un mes dado, para pintar el calendario de disponibilidad en
 * templates/empleados.php. Acceso: módulo Directorio de Empleados (22).
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

$idUsuarioSesion = $_SESSION['id_usuario'] ?? null;
if (!is_string($idUsuarioSesion) || trim($idUsuarioSesion) === '') {
    responderJson(false, 'Sesión inválida.', 401);
}

if (!has_module_access(22)) {
    responderJson(false, 'No tienes acceso al Directorio de Empleados.', 403);
}

$idEmpleado = trim((string) ($_GET['id_empleado'] ?? ''));
if ($idEmpleado === '') {
    responderJson(false, 'El empleado indicado no es válido.', 400);
}

$anio = (int) ($_GET['anio'] ?? date('Y'));
$mes = (int) ($_GET['mes'] ?? date('n'));
if ($anio < 2000 || $anio > 2100 || $mes < 1 || $mes > 12) {
    responderJson(false, 'El mes/año indicado no es válido.', 400);
}

$primerDiaMes = sprintf('%04d-%02d-01', $anio, $mes);
$ultimoDiaMes = date('Y-m-t', strtotime($primerDiaMes));

try {
    $sentencia = $conexion->prepare(
        'SELECT fecha_inicio, fecha_fin
         FROM t_solicitudes_permisos
         WHERE id_empleado = :id_empleado
           AND estado = :estado
           AND fec_delete IS NULL
           AND fecha_inicio <= :ultimo_dia
           AND fecha_fin >= :primer_dia'
    );
    $sentencia->execute([
        ':id_empleado' => $idEmpleado,
        ':estado' => 'APROBADO',
        ':ultimo_dia' => $ultimoDiaMes,
        ':primer_dia' => $primerDiaMes,
    ]);
    $rangos = $sentencia->fetchAll(PDO::FETCH_ASSOC);

    responderJson(true, 'Ocupación obtenida correctamente.', 200, ['rangos' => $rangos]);

} catch (PDOException $e) {
    error_log('Error al consultar ocupación de empleado: ' . $e->getMessage());
    responderJson(false, 'Error del servidor al consultar la ocupación.', 500);
}
