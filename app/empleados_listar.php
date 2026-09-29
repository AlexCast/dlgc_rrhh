<?php
declare(strict_types=1);

/**
 * Devuelve el directorio de empleados activos (nombre, cargo actual y foto) para
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

$idUsuario = $_SESSION['id_usuario'] ?? null;
if (!is_string($idUsuario) || trim($idUsuario) === '') {
    responderJson(false, 'Sesión inválida.', 401);
}

if (!has_module_access(22)) {
    responderJson(false, 'No tienes acceso al Directorio de Empleados.', 403);
}

try {
    $sentencia = $conexion->query(
        "SELECT
            e.id_usuario,
            e.foto_perfil,
            e.numero_celular,
            u.primer_nombre,
            u.segundo_nombre,
            u.primer_apellido,
            u.segundo_apellido,
            u.correo,
            CONCAT_WS(' ', u.primer_nombre, NULLIF(u.segundo_nombre, ''), u.primer_apellido, NULLIF(u.segundo_apellido, '')) AS nombre_completo,
            (
                SELECT c.puesto
                FROM t_contratos_empleados c
                WHERE c.id_usuario = e.id_usuario
                  AND c.fec_delete IS NULL
                ORDER BY c.fecha_inicio_puesto DESC NULLS LAST, c.id_contrato DESC
                LIMIT 1
            ) AS cargo
         FROM t_empleados e
         INNER JOIN t_usuarios u ON e.id_usuario = u.id_usuario
         WHERE e.fec_delete IS NULL
           AND (e.fecha_egreso IS NULL OR e.fecha_egreso > CURRENT_DATE)
         ORDER BY nombre_completo"
    );
    $empleados = $sentencia->fetchAll(PDO::FETCH_ASSOC);

    $empleados = array_map(function (array $empleado): array {
        $primerNombre = trim((string) ($empleado['primer_nombre'] ?? ''));
        $primerApellido = trim((string) ($empleado['primer_apellido'] ?? ''));
        $segundoNombre = trim((string) ($empleado['segundo_nombre'] ?? ''));
        $segundoApellido = trim((string) ($empleado['segundo_apellido'] ?? ''));

        $empleado['nombre_corto'] = trim("{$primerNombre} {$primerApellido}") !== ''
            ? trim("{$primerNombre} {$primerApellido}")
            : $empleado['id_usuario'];
        $empleado['nombre_completo'] = trim((string) $empleado['nombre_completo']) !== ''
            ? (string) $empleado['nombre_completo']
            : $empleado['id_usuario'];
        $empleado['cargo'] = trim((string) ($empleado['cargo'] ?? '')) !== '' ? (string) $empleado['cargo'] : 'Sin cargo';
        $empleado['numero_celular'] = trim((string) ($empleado['numero_celular'] ?? ''));
        $empleado['correo'] = trim((string) ($empleado['correo'] ?? ''));
        $empleado['segundo_nombre'] = $segundoNombre;
        $empleado['segundo_apellido'] = $segundoApellido;

        return $empleado;
    }, $empleados);

    responderJson(true, 'Empleados obtenidos correctamente.', 200, ['empleados' => $empleados]);

} catch (PDOException $e) {
    error_log('Error al listar empleados del directorio: ' . $e->getMessage());
    responderJson(false, 'Error del servidor al consultar los empleados.', 500);
}
