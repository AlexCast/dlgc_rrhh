<?php
declare(strict_types=1);

/**
 * Endpoint para aprobar/rechazar una solicitud de permiso en el nivel JEFE o RRHH.
 * El nivel JEFE solo lo puede resolver el jefe directo fijado en la solicitud (lo valida
 * la función fun_resolver_permisos_aprobaciones); el nivel RRHH requiere permiso del módulo 27.
 */

require_once __DIR__ . '/session_bootstrap.php';
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

        $nombreEmpleadoHtml = htmlspecialchars((string) $empleado['primer_nombre'], ENT_QUOTES, 'UTF-8');
        $estadoTexto = $nuevoEstado === 'APROBADO' ? 'aprobada' : 'rechazada';
        $responsableTexto = $nivel === 'JEFE' ? 'tu jefe directo' : 'RRHH';
        $badgeColor = $nuevoEstado === 'APROBADO' ? '#3ba86a' : '#e05d5d';
        $badgeBg = $nuevoEstado === 'APROBADO' ? '#eafaf1' : '#fdeceb';

        $body = sprintf(
            '<!DOCTYPE html>
            <html lang="es">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Resolución de solicitud de permiso</title>
                <style>
                    body { margin: 0; padding: 0; background-color: #ececec; font-family: Inter, system-ui, -apple-system, sans-serif; color: #0D130F; }
                    .wrapper { width: 100%%; padding: 40px 16px; }
                    .card { max-width: 480px; margin: 0 auto; background-color: #ffffff; border-radius: 24px; border: 2px solid #3ba86a; box-shadow: 0 12px 30px rgba(59, 168, 106, 0.12); overflow: hidden; }
                    .header { background-color: #3ba86a; padding: 24px; text-align: center; }
                    .header h1 { color: #ffffff; margin: 0; font-size: 1.25rem; font-weight: 700; }
                    .content { padding: 32px; }
                    .content p { margin: 0 0 16px; line-height: 1.6; font-size: 0.95rem; color: #0D130F; }
                    .content p strong { color: #0D130F; }
                    .badge { display: inline-block; background-color: %s; color: %s; border-radius: 8px; padding: 4px 12px; font-weight: 600; font-size: 0.85rem; }
                    .notice { font-size: 0.85rem; color: #6b7280; }
                    .notice strong { color: #6b7280; }
                    .footer { text-align: center; padding: 16px 32px; font-size: 0.8rem; color: #6b7280; border-top: 1px solid #e5e7eb; }
                </style>
            </head>
            <body>
                <div class="wrapper">
                    <div class="card">
                        <div class="header">
                            <h1>Portal DLGC RRHH</h1>
                        </div>
                        <div class="content">
                            <p>Hola <strong>%s</strong>,</p>
                            <p><span class="badge">Solicitud #%d</span></p>
                            <p>Tu solicitud de permiso fue <strong>%s</strong> por %s.</p>
                            <p class="notice">Si quieres saber más detalles sobre la resolución, ingresa al portal DLGC RRHH.</p>
                            <p>Saludos,<br><strong>Portal DLGC RRHH</strong></p>
                        </div>
                        <div class="footer">
                            &copy; Distribuciones La Gran Cacharrería. Todos los derechos reservados.
                        </div>
                    </div>
                </div>
            </body>
            </html>',
            $badgeBg,
            $badgeColor,
            $nombreEmpleadoHtml,
            $idPermiso,
            $estadoTexto,
            $responsableTexto
        );

        $altBody = "Portal DLGC RRHH\n\n"
                 . "Hola {$empleado['primer_nombre']},\n\n"
                 . "Tu solicitud de permiso #{$idPermiso} fue {$estadoTexto} por {$responsableTexto}.\n\n"
                 . "Ingresa al portal DLGC RRHH para más detalles.\n\n"
                 . "Distribuciones La Gran Cacharrería";

        $mailer->send($empleado['correo'], (string) $empleado['primer_nombre'], $subject, $body, $altBody);
    } catch (Throwable $e) {
        error_log('notificarResolucionSolicitud: no se pudo enviar la notificación: ' . $e->getMessage());
    }
}
