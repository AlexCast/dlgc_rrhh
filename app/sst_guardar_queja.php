<?php
declare(strict_types=1);

/**
 * Endpoint para enviar una queja/sugerencia al buzón SST.
 */

session_start();

require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/auth_guard.php';
require_once __DIR__ . '/helpers/Mailer.php';

header('Content-Type: application/json; charset=utf-8');

function responderJson(bool $success, string $message, int $httpCode = 200): void
{
    http_response_code($httpCode);
    echo json_encode(['success' => $success, 'message' => $message], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responderJson(false, 'Método no permitido.', 405);
}

if (!csrf_validate(false)) {
    responderJson(false, 'Token de seguridad inválido o ausente. Recarga la página e inténtalo de nuevo.', 403);
}

if (!has_module_access(25)) {
    responderJson(false, 'No tienes acceso al módulo SST.', 403);
}

$idUsuario = $_SESSION['id_usuario'] ?? null;
if (!is_string($idUsuario) || trim($idUsuario) === '') {
    responderJson(false, 'Sesión inválida.', 401);
}

$tipoPeticion = strtoupper(trim($_POST['tipo_peticion'] ?? ''));
$asunto       = trim($_POST['asunto'] ?? '');
$descripcion  = trim($_POST['descripcion'] ?? '');

if (!in_array($tipoPeticion, ['QUEJA', 'SUGERENCIA', 'RECLAMO', 'DENUNCIA'], true)) {
    responderJson(false, 'El tipo de petición no es válido.', 400);
}

if ($asunto === '' || mb_strlen($asunto) < 5) {
    responderJson(false, 'El asunto debe tener al menos 5 caracteres.', 400);
}

if ($descripcion === '' || mb_strlen($descripcion) < 10) {
    responderJson(false, 'La descripción debe tener al menos 10 caracteres.', 400);
}

try {
    $sentencia = $conexion->prepare('SELECT fun_insert_sst_queja(:id_usuario, :tipo_peticion, :asunto, :descripcion);');
    $sentencia->execute([
        ':id_usuario'    => $idUsuario,
        ':tipo_peticion' => $tipoPeticion,
        ':asunto'        => $asunto,
        ':descripcion'   => $descripcion,
    ]);

    $resultado = (string) $sentencia->fetchColumn();

    if (stripos($resultado, 'correctamente') === false) {
        responderJson(false, $resultado, 400);
    }

    // Notificar a encargados SST (módulo 26)
    notificarEncargadosSst($conexion, $idUsuario, $tipoPeticion, $asunto, $descripcion);

    responderJson(true, $resultado);
} catch (PDOException $e) {
    error_log('Error en sst_guardar_queja: ' . $e->getMessage());
    responderJson(false, 'Ocurrió un error al enviar la solicitud.', 500);
}

function notificarEncargadosSst(PDO $conexion, string $idUsuario, string $tipoPeticion, string $asunto, string $descripcion): void
{
    try {
        $sentenciaUsuario = $conexion->prepare(
            "SELECT primer_nombre, segundo_nombre, primer_apellido, segundo_apellido
             FROM t_usuarios
             WHERE id_usuario = :id_usuario
               AND fec_delete IS NULL"
        );
        $sentenciaUsuario->execute([':id_usuario' => $idUsuario]);
        $usuario = $sentenciaUsuario->fetch(PDO::FETCH_OBJ);

        $nombreUsuario = $usuario
            ? trim(($usuario->primer_nombre ?? '') . ' ' . ($usuario->segundo_nombre ?? '') . ' ' . ($usuario->primer_apellido ?? '') . ' ' . ($usuario->segundo_apellido ?? ''))
            : $idUsuario;

        $sentenciaEncargados = $conexion->prepare(
            "SELECT DISTINCT u.id_usuario, u.correo
             FROM t_usuarios u
             INNER JOIN t_roles_operaciones ro ON ro.id_rol = u.id_rol AND ro.fec_delete IS NULL
             INNER JOIN t_operaciones o ON o.id_operacion = ro.id_operacion AND o.fec_delete IS NULL
             WHERE o.id_modulo = 26
               AND u.fec_delete IS NULL
             UNION
             SELECT DISTINCT u.id_usuario, u.correo
             FROM t_usuarios u
             INNER JOIN t_usuarios_operaciones uo ON uo.id_usuario = u.id_usuario AND uo.fec_delete IS NULL
             INNER JOIN t_operaciones o ON o.id_operacion = uo.id_operacion AND o.fec_delete IS NULL
             WHERE o.id_modulo = 26
               AND u.fec_delete IS NULL"
        );
        $sentenciaEncargados->execute();
        $encargados = $sentenciaEncargados->fetchAll(PDO::FETCH_OBJ);

        if (empty($encargados)) {
            error_log('sst_guardar_queja: No hay encargados SST configurados para notificar.');
            return;
        }

        $mailer = new Mailer();
        $subject = 'Nueva ' . ucfirst(strtolower($tipoPeticion)) . ' en el Buzón SST';

        $tipoPeticionHtml = htmlspecialchars(ucfirst(strtolower($tipoPeticion)));
        $nombreUsuarioHtml = htmlspecialchars($nombreUsuario);
        $idUsuarioHtml = htmlspecialchars($idUsuario);
        $asuntoHtml = htmlspecialchars($asunto);
        $descripcionHtml = nl2br(htmlspecialchars($descripcion));

        $body = sprintf(
            '<!DOCTYPE html>
            <html lang="es">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Nueva solicitud en el Buzón SST</title>
            </head>
            <body style="margin: 0; padding: 0; background-color: #ececec; font-family: Inter, system-ui, -apple-system, sans-serif; color: #0D130F;">
                <div style="width: 100%%; padding: 40px 16px;">
                    <div style="max-width: 480px; margin: 0 auto; background-color: #ffffff; border-radius: 24px; border: 2px solid #3ba86a; box-shadow: 0 12px 30px rgba(59, 168, 106, 0.12); overflow: hidden;">
                        <div style="background-color: #3ba86a; padding: 24px; text-align: center;">
                            <h1 style="color: #ffffff; margin: 0; font-size: 1.25rem; font-weight: 700;">Portal DLGC RRHH</h1>
                        </div>
                        <div style="padding: 32px;">
                            <p style="margin: 0 0 16px; line-height: 1.6; font-size: 0.95rem; color: #0D130F;">Hola <strong style="color: #0D130F;">Equipo de SST</strong>,</p>
                            <p style="margin: 0 0 16px; line-height: 1.6; font-size: 0.95rem; color: #0D130F;">Se ha recibido una nueva solicitud en el <strong style="color: #0D130F;">Buzón SST</strong> a continuación los detalles:</p>

                            <div style="background-color: #f8faf9; border-radius: 12px; padding: 20px; margin: 24px 0; border: 1px solid #e5e7eb;">
                                <p style="margin: 0 0 12px; line-height: 1.5; font-size: 0.95rem; color: #0D130F;"><strong style="color: #0D130F;">Tipo de petición:</strong> %s</p>
                                <p style="margin: 0 0 12px; line-height: 1.5; font-size: 0.95rem; color: #0D130F;"><strong style="color: #0D130F;">Enviada por:</strong> %s (%s)</p>
                                <p style="margin: 0 0 12px; line-height: 1.5; font-size: 0.95rem; color: #0D130F;"><strong style="color: #0D130F;">Asunto:</strong> %s</p>
                                <p style="margin: 0 0 4px; line-height: 1.5; font-size: 0.95rem; color: #0D130F;"><strong style="color: #0D130F;">Descripción:</strong></p>
                                <p style="margin: 0; line-height: 1.6; font-size: 0.95rem; color: #0D130F;">%s</p>
                            </div>

                            <div style="text-align: center; margin: 24px 0;">
                                <a href="https://dlgchr.com.co" style="display: inline-block; background-color: #3ba86a; color: #ffffff !important; text-decoration: none; padding: 14px 28px; border-radius: 12px; font-weight: 600; font-size: 1rem;">Ir al módulo SST</a>
                            </div>

                            <p style="margin: 0; line-height: 1.6; font-size: 0.85rem; color: #6b7280;">Ingresa al módulo de Administración SST para gestionar la solicitud.</p>
                        </div>
                        <div style="text-align: center; padding: 16px 32px; font-size: 0.8rem; color: #6b7280; border-top: 1px solid #e5e7eb;">
                            &copy; Distribuciones La Gran Cacharrería. Todos los derechos reservados.
                        </div>
                    </div>
                </div>
            </body>
            </html>',
            $tipoPeticionHtml,
            $nombreUsuarioHtml,
            $idUsuarioHtml,
            $asuntoHtml,
            $descripcionHtml
        );

        $altBody = "Portal DLGC RRHH\n\n"
                 . "Hola Equipo de SST,\n\n"
                 . "Se ha recibido una nueva solicitud en el Buzón SST.\n\n"
                 . "Tipo de petición: " . ucfirst(strtolower($tipoPeticion)) . "\n"
                 . "Enviada por: {$nombreUsuario} ({$idUsuario})\n"
                 . "Asunto: {$asunto}\n"
                 . "Descripción:\n{$descripcion}\n\n"
                 . "Ingresa al módulo de Administración SST para gestionar la solicitud.\n\n"
                 . "Distribuciones La Gran Cacharrería";

        foreach ($encargados as $encargado) {
            $ok = $mailer->send($encargado->correo, $encargado->id_usuario, $subject, $body, $altBody);
            if (!$ok) {
                error_log('sst_guardar_queja: No se pudo notificar a ' . $encargado->correo);
            }
        }
    } catch (Throwable $e) {
        error_log('sst_guardar_queja error notificando: ' . $e->getMessage());
    }
}
