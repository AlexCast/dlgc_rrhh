<?php
declare(strict_types=1);

/**
 * Detalle completo de una solicitud (motivos, evidencias y estado de cada nivel de aprobación).
 * Acceso: el propio empleado, su jefe responsable, o cualquier usuario con acceso al módulo 27 (RRHH).
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

$idPermiso = (int) ($_GET['id_permiso'] ?? 0);
if ($idPermiso <= 0) {
    responderJson(false, 'El ID de la solicitud no es válido.', 400);
}

try {
    $sentencia = $conexion->prepare(
        'SELECT sp.*, ue.primer_nombre AS empleado_nombre, ue.primer_apellido AS empleado_apellido
         FROM t_solicitudes_permisos sp
         INNER JOIN t_usuarios ue ON ue.id_usuario = sp.id_empleado
         WHERE sp.id_permiso = :id_permiso AND sp.fec_delete IS NULL'
    );
    $sentencia->execute([':id_permiso' => $idPermiso]);
    $solicitud = $sentencia->fetch(PDO::FETCH_ASSOC);

    if ($solicitud === false) {
        responderJson(false, 'No se encontró la solicitud.', 404);
    }

    $esPropia = $solicitud['id_empleado'] === $idUsuario;
    $esJefeResponsable = $solicitud['id_jefe_responsable'] === $idUsuario;
    $esRrhh = has_module_access(27);

    if (!$esPropia && !$esJefeResponsable && !$esRrhh) {
        responderJson(false, 'No tienes acceso a esta solicitud.', 403);
    }

    $sentenciaMotivos = $conexion->prepare(
        'SELECT tp.nombre_tipo, spm.detalle_motivo
         FROM t_solicitudes_permisos_motivos spm
         INNER JOIN t_tipos_permisos tp ON tp.id_tipo_permiso = spm.id_tipo_permiso
         WHERE spm.id_permiso = :id_permiso AND spm.fec_delete IS NULL'
    );
    $sentenciaMotivos->execute([':id_permiso' => $idPermiso]);
    $motivos = $sentenciaMotivos->fetchAll(PDO::FETCH_ASSOC);

    $sentenciaEvidencias = $conexion->prepare(
        'SELECT id_evidencia, nombre_original, mime_type, tamano_bytes, url_publica
         FROM t_permisos_evidencias
         WHERE id_permiso = :id_permiso AND fec_delete IS NULL
         ORDER BY fec_insert'
    );
    $sentenciaEvidencias->execute([':id_permiso' => $idPermiso]);
    $evidencias = $sentenciaEvidencias->fetchAll(PDO::FETCH_ASSOC);

    $sentenciaAprobaciones = $conexion->prepare(
        'SELECT nivel_aprobacion, id_aprobador, estado, observacion, fec_resolucion
         FROM t_permisos_aprobaciones
         WHERE id_permiso = :id_permiso AND fec_delete IS NULL
         ORDER BY nivel_aprobacion'
    );
    $sentenciaAprobaciones->execute([':id_permiso' => $idPermiso]);
    $aprobaciones = $sentenciaAprobaciones->fetchAll(PDO::FETCH_ASSOC);

    responderJson(true, 'Detalle obtenido correctamente.', 200, [
        'solicitud'    => $solicitud,
        'motivos'      => $motivos,
        'evidencias'   => $evidencias,
        'aprobaciones' => $aprobaciones,
    ]);

} catch (PDOException $e) {
    error_log('Error al obtener detalle de solicitud de permiso: ' . $e->getMessage());
    responderJson(false, 'Error del servidor al consultar el detalle.', 500);
}
