<?php
declare(strict_types=1);

/**
 * Devuelve las fechas de días festivos (nacionales + empresa) dentro de un rango,
 * para que el front-end excluya domingos y festivos al contar "días hábiles"
 * en el selector de fechas de solicitud de permisos.
 * Acceso: cualquier usuario autenticado con acceso al módulo de Solicitudes y Permisos.
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

$idUsuario = $_SESSION['id_usuario'] ?? null;
if (!is_string($idUsuario) || trim($idUsuario) === '') {
    responderJson(false, 'Sesión inválida.', 401);
}

if (!has_module_access(3)) {
    responderJson(false, 'No tienes acceso al módulo de Solicitudes y Permisos.', 403);
}

$anio = (int) ($_GET['anio'] ?? date('Y'));
if ($anio < 2000 || $anio > 2100) {
    responderJson(false, 'El año indicado no es válido.', 400);
}

// Se incluye el año siguiente para no cortar solicitudes que crucen el 31 de diciembre.
try {
    $sentencia = $conexion->prepare(
        'SELECT fecha, descripcion, tipo_festivo FROM t_dias_festivos
         WHERE fec_delete IS NULL
           AND EXTRACT(YEAR FROM fecha) IN (:anio, :anio_siguiente)
         ORDER BY fecha'
    );
    $sentencia->execute([':anio' => $anio, ':anio_siguiente' => $anio + 1]);
    $festivos = $sentencia->fetchAll(PDO::FETCH_ASSOC);

    responderJson(true, 'Festivos obtenidos correctamente.', 200, ['festivos' => $festivos]);

} catch (PDOException $e) {
    error_log('Error al listar días festivos: ' . $e->getMessage());
    responderJson(false, 'Error del servidor al consultar los días festivos.', 500);
}
