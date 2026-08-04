<?php
declare(strict_types=1);

/**
 * Endpoint para aprobar/rechazar una solicitud de permiso en el nivel JEFE o RRHH.
 * El nivel JEFE solo lo puede resolver el jefe directo fijado en la solicitud (lo valida
 * la función fun_resolver_permisos_aprobaciones); el nivel RRHH requiere permiso del módulo 27.
 */

session_start();

require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/auth_guard.php';
require_once __DIR__ . '/helpers/Mailer.php';

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

$idActor = $_SESSION['id_usuario'] ?? null;
if (!is_string($idActor) || trim($idActor) === '') {
    responderJson(false, 'Sesión inválida.', 401);
}

$idPermiso   = (int) ($_POST['id_permiso'] ?? 0);
$nivel       = strtoupper(trim($_POST['nivel'] ?? ''));
$accion      = strtoupper(trim($_POST['accion'] ?? ''));
$observacion = trim($_POST['observacion'] ?? '') ?: null;
$metodoDescuento = strtoupper(trim($_POST['metodo_descuento'] ?? '')) ?: null;

if ($idPermiso <= 0) {
    responderJson(false, 'El ID de la solicitud no es válido.', 400);
}
if (!in_array($nivel, ['JEFE', 'RRHH'], true)) {
    responderJson(false, 'Nivel de aprobación no válido.', 400);
}
if (!in_array($accion, ['APROBADO', 'RECHAZADO'], true)) {
    responderJson(false, 'La acción debe ser APROBADO o RECHAZADO.', 400);
}
if ($nivel === 'RRHH' && $accion === 'APROBADO' && !in_array($metodoDescuento, ['DINERO', 'VACACIONES', 'NO'], true)) {
    responderJson(false, 'Debes indicar si el permiso es remunerado (Sí, Vacaciones o No) antes de aprobar.', 400);
}

if ($nivel === 'JEFE' && !has_module_permission(3, 'actualizar')) {
    responderJson(false, 'No tienes permiso para resolver solicitudes como jefe directo.', 403);
}
if ($nivel === 'RRHH' && !has_module_permission(27, 'actualizar')) {
    responderJson(false, 'No tienes permiso para resolver solicitudes como RRHH.', 403);
}

try {
    $sentencia = $conexion->prepare(
        'SELECT * FROM fun_resolver_permisos_aprobaciones(:id_permiso, :nivel, :id_actor, :accion, :observacion, :metodo_descuento)'
    );
    $sentencia->execute([
        ':id_permiso'       => $idPermiso,
        ':nivel'            => $nivel,
        ':id_actor'         => $idActor,
        ':accion'           => $accion,
        ':observacion'      => $observacion,
        ':metodo_descuento' => $metodoDescuento,
    ]);
    $resultado = $sentencia->fetch(PDO::FETCH_ASSOC);

    if ($resultado === false || !filter_var($resultado['exito'], FILTER_VALIDATE_BOOLEAN)) {
        responderJson(false, $resultado['mensaje'] ?? 'No se pudo resolver la aprobación.', 400);
    }

    notificarResolucionSolicitud($conexion, $idPermiso, $nivel, $accion, $resultado['nuevo_estado_solicitud']);

    responderJson(true, $resultado['mensaje'], 200, ['nuevo_estado_solicitud' => $resultado['nuevo_estado_solicitud']]);

} catch (PDOException $e) {
    error_log('Error al resolver aprobación de permiso: ' . $e->getMessage());
    responderJson(false, 'Error del servidor al procesar la aprobación.', 500);
}

/**
 * Notifica al empleado dueño de la solicitud cuando su estado agregado cambia
 * (rechazo inmediato en cualquier nivel, o aprobación final cuando ambos niveles aprueban).
 */
function notificarResolucionSolicitud(PDO $conexion, int $idPermiso, string $nivel, string $accion, ?string $nuevoEstado): void
{
    if (!in_array($nuevoEstado, ['APROBADO', 'RECHAZADO'], true)) {
        return; // Sigue EN_REVISION: aún falta el otro nivel, no se notifica todavía.
    }

    try {
        $sentencia = $conexion->prepare(
            'SELECT u.correo, u.primer_nombre
             FROM t_solicitudes_permisos sp
             INNER JOIN t_usuarios u ON u.id_usuario = sp.id_empleado
             WHERE sp.id_permiso = :id_permiso'
        );
        $sentencia->execute([':id_permiso' => $idPermiso]);
        $empleado = $sentencia->fetch(PDO::FETCH_ASSOC);

        if ($empleado === false || empty($empleado['correo'])) {
            return;
        }

        $mailer = new Mailer();
        $subject = $nuevoEstado === 'APROBADO'
            ? "Tu solicitud de permiso #{$idPermiso} fue aprobada"
            : "Tu solicitud de permiso #{$idPermiso} fue rechazada";
        $body = sprintf(
            '<p>Hola %s,</p><p>Tu solicitud de permiso #%d fue <strong>%s</strong> por %s.</p>',
            htmlspecialchars((string) $empleado['primer_nombre']),
            $idPermiso,
            $nuevoEstado === 'APROBADO' ? 'aprobada' : 'rechazada',
            $nivel === 'JEFE' ? 'tu jefe directo' : 'RRHH'
        );
        $mailer->send($empleado['correo'], (string) $empleado['primer_nombre'], $subject, $body, strip_tags($body));
    } catch (Throwable $e) {
        error_log('notificarResolucionSolicitud: no se pudo enviar la notificación: ' . $e->getMessage());
    }
}
