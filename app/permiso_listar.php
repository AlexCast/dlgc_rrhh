<?php
declare(strict_types=1);

/**
 * Endpoint de listado de solicitudes de permiso, según la bandeja solicitada (?vista=mias|jefe|rrhh).
 * - mias: solo las solicitudes del usuario en sesión.
 * - jefe: solo las solicitudes donde el usuario en sesión es el jefe responsable (subordinados propios).
 * - rrhh: TODAS las solicitudes del sistema (requiere permiso VER del módulo 27).
 * Para jefe/rrhh, por defecto solo se listan las solicitudes PENDIENTES de ese nivel (la bandeja
 * se vacía al resolver); con ?historial=1 se lista el historial completo ya resuelto por ese nivel.
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

$vista = strtolower(trim($_GET['vista'] ?? 'mias'));
$historial = filter_var($_GET['historial'] ?? false, FILTER_VALIDATE_BOOLEAN);

$filtroSql = '';
$parametros = [];
$ordenSql = 'sp.fec_insert DESC';

switch ($vista) {
    case 'mias':
        if (!has_module_permission(3, 'crear') && !has_module_access(3)) {
            responderJson(false, 'No tienes acceso al módulo de Solicitudes y Permisos.', 403);
        }
        $filtroSql = 'sp.id_empleado = :id_usuario';
        $parametros[':id_usuario'] = $idUsuario;
        break;

    case 'jefe':
        if (!has_module_permission(3, 'actualizar')) {
            responderJson(false, 'No tienes permiso para revisar solicitudes de subordinados.', 403);
        }
        $filtroSql = 'sp.id_jefe_responsable = :id_usuario AND aj.estado ' . ($historial ? "<> 'PENDIENTE'" : "= 'PENDIENTE'");
        $parametros[':id_usuario'] = $idUsuario;
        if ($historial) {
            $ordenSql = 'aj.fec_resolucion DESC NULLS LAST';
        }
        break;

    case 'rrhh':
        if (!has_module_access(27)) {
            responderJson(false, 'No tienes permiso para ver la bandeja de RRHH.', 403);
        }
        $filtroSql = "ar.estado " . ($historial ? "<> 'PENDIENTE'" : "= 'PENDIENTE'");
        if ($historial) {
            $ordenSql = 'ar.fec_resolucion DESC NULLS LAST';
        }
        break;

    default:
        responderJson(false, 'Vista no válida.', 400);
}


try {
    $sentencia = $conexion->prepare(
        "SELECT
            sp.id_permiso, sp.id_empleado, sp.id_jefe_responsable, sp.es_por_horas,
            sp.fecha_inicio, sp.fecha_fin, sp.hora_inicio, sp.hora_fin, sp.estado,
            sp.metodo_descuento, sp.fec_insert,
            ue.primer_nombre AS empleado_nombre, ue.primer_apellido AS empleado_apellido,
            aj.estado AS estado_jefe, aj.observacion AS observacion_jefe, aj.fec_resolucion AS fec_resolucion_jefe,
            ar.estado AS estado_rrhh, ar.observacion AS observacion_rrhh, ar.fec_resolucion AS fec_resolucion_rrhh,
            (SELECT COUNT(*) FROM t_permisos_evidencias e WHERE e.id_permiso = sp.id_permiso AND e.fec_delete IS NULL) AS total_evidencias,
            (SELECT STRING_AGG(tp.nombre_tipo, ', ' ORDER BY tp.nombre_tipo)
               FROM t_solicitudes_permisos_motivos spm
               INNER JOIN t_tipos_permisos tp ON tp.id_tipo_permiso = spm.id_tipo_permiso
              WHERE spm.id_permiso = sp.id_permiso AND spm.fec_delete IS NULL) AS motivos
         FROM t_solicitudes_permisos sp
         INNER JOIN t_usuarios ue ON ue.id_usuario = sp.id_empleado
         LEFT JOIN t_permisos_aprobaciones aj ON aj.id_permiso = sp.id_permiso AND aj.nivel_aprobacion = 'JEFE' AND aj.fec_delete IS NULL
         LEFT JOIN t_permisos_aprobaciones ar ON ar.id_permiso = sp.id_permiso AND ar.nivel_aprobacion = 'RRHH' AND ar.fec_delete IS NULL
         WHERE sp.fec_delete IS NULL
           AND {$filtroSql}
         ORDER BY {$ordenSql}"
    );
    $sentencia->execute($parametros);
    $solicitudes = $sentencia->fetchAll(PDO::FETCH_ASSOC);

    responderJson(true, 'Listado obtenido correctamente.', 200, ['solicitudes' => $solicitudes]);

} catch (PDOException $e) {
    error_log('Error al listar solicitudes de permiso: ' . $e->getMessage());
    responderJson(false, 'Error del servidor al consultar las solicitudes.', 500);
}
