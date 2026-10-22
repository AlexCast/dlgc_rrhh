<?php

declare(strict_types=1);

$moduleId = 26;
$requiredAction = 'actualizar';
require_once __DIR__ . '/../../app/src_guard.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate();
}

if (
    !isset($_POST['id_queja']) ||
    !isset($_POST['estado'])
) {
    header('Location: listar_quejas.php?error=datos');
    exit();
}

$idQueja = (int) trim($_POST['id_queja']);
$estado = trim((string) $_POST['estado']);
$respuesta = trim((string) ($_POST['respuesta'] ?? ''));
$idEncargado = $_SESSION['id_usuario'] ?? null;

if ($idQueja <= 0 || $estado === '' || !is_string($idEncargado) || trim($idEncargado) === '') {
    header('Location: listar_quejas.php?error=datos');
    exit();
}

if (!in_array($estado, ['EN PROCESO', 'RESUELTO', 'CANCELADO_ENCARGADO'], true)) {
    header('Location: listar_quejas.php?error=estado');
    exit();
}

require_once __DIR__ . '/../../app/conexion.php';
require_once __DIR__ . '/../../app/helpers/Mailer.php';

$sentencia = $conexion->prepare('SELECT fun_cambiar_estado_sst_queja(?, ?, ?, ?);');
$sentencia->execute([$idQueja, $idEncargado, $estado, $respuesta]);
$resultado = (string) $sentencia->fetchColumn();

if (stripos($resultado, 'correctamente') !== false) {
    notificarResolucionQueja($conexion, $idQueja, $idEncargado, $estado);
    header('Location: listar_quejas.php?success=' . urlencode('Estado actualizado correctamente.'));
    exit();
}

header('Location: listar_quejas.php?error=' . urlencode($resultado));
exit();

/**
 * Notifica al usuario que radicó la queja/sugerencia el cambio de estado hecho por el
 * encargado SST. Funciona para RESUELTO, CANCELADO_ENCARGADO y EN PROCESO.
 */
function notificarResolucionQueja(PDO $conexion, int $idQueja, string $idEncargado, string $estado): void
{
    $estados = [
        'RESUELTO'            => ['texto' => 'resuelta', 'badgeBg' => '#eafaf1', 'badgeColor' => '#3ba86a'],
        'CANCELADO_ENCARGADO' => ['texto' => 'cancelada', 'badgeBg' => '#fdeceb', 'badgeColor' => '#e05d5d'],
        'EN PROCESO'          => ['texto' => 'en proceso', 'badgeBg' => '#fff8e6', 'badgeColor' => '#b7791f'],
    ];

    if (!isset($estados[$estado])) {
        return;
    }

    try {
        // Se lee después del UPDATE: la función conserva la respuesta previa si llega vacía.
        $sentencia = $conexion->prepare(
            "SELECT u.correo, u.primer_nombre, q.tipo_peticion, q.asunto, q.respuesta
             FROM t_sst_buzon_quejas q
             INNER JOIN t_usuarios u ON u.id_usuario = q.id_usuario
             WHERE q.id_queja = ?
               AND q.fec_delete IS NULL"
        );
        $sentencia->execute([$idQueja]);
        $fila = $sentencia->fetch(PDO::FETCH_ASSOC);

        if ($fila === false || empty($fila['correo'])) {
            return;
        }

        $conf = $estados[$estado];
        $tipoPeticion = ucfirst(strtolower((string) $fila['tipo_peticion']));

        $mailer  = new Mailer();
        $subject = "Tu {$tipoPeticion} del Buzón SST fue {$conf['texto']}";

        $nombreHtml      = htmlspecialchars((string) $fila['primer_nombre'], ENT_QUOTES, 'UTF-8');
        $tipoPeticionHtml = htmlspecialchars($tipoPeticion, ENT_QUOTES, 'UTF-8');
        $asuntoHtml      = htmlspecialchars((string) $fila['asunto'], ENT_QUOTES, 'UTF-8');
        $respuestaHtml   = nl2br(htmlspecialchars(trim((string) $fila['respuesta']), ENT_QUOTES, 'UTF-8'));

        $bloqueRespuesta = '';
        if ($respuestaHtml !== '') {
            $bloqueRespuesta = sprintf(
                '<div style="background-color: #f8faf9; border-radius: 12px; padding: 20px; margin: 24px 0; border: 1px solid #e5e7eb;">
                    <p style="margin: 0 0 4px; line-height: 1.5; font-size: 0.95rem; color: #0D130F;"><strong style="color: #0D130F;">Respuesta del encargado:</strong></p>
                    <p style="margin: 0; line-height: 1.6; font-size: 0.95rem; color: #0D130F;">%s</p>
                </div>',
                $respuestaHtml
            );
        }

        $body = sprintf(
            '<!DOCTYPE html>
            <html lang="es">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Actualización de tu solicitud en el Buzón SST</title>
            </head>
            <body style="margin: 0; padding: 0; background-color: #ececec; font-family: Inter, system-ui, -apple-system, sans-serif; color: #0D130F;">
                <div style="width: 100%%; padding: 40px 16px;">
                    <div style="max-width: 480px; margin: 0 auto; background-color: #ffffff; border-radius: 24px; border: 2px solid #3ba86a; box-shadow: 0 12px 30px rgba(59, 168, 106, 0.12); overflow: hidden;">
                        <div style="background-color: #3ba86a; padding: 24px; text-align: center;">
                            <h1 style="color: #ffffff; margin: 0; font-size: 1.25rem; font-weight: 700;">Portal DLGC RRHH</h1>
                        </div>
                        <div style="padding: 32px;">
                            <p style="margin: 0 0 16px; line-height: 1.6; font-size: 0.95rem; color: #0D130F;">Hola <strong style="color: #0D130F;">%s</strong>,</p>
                            <p style="margin: 0 0 16px; line-height: 1.6; font-size: 0.95rem; color: #0D130F;">
                                Tu solicitud en el <strong style="color: #0D130F;">Buzón SST</strong> ha sido
                                <span style="display: inline-block; background-color: %s; color: %s; border-radius: 8px; padding: 4px 12px; font-weight: 600; font-size: 0.85rem;">%s</span>.
                            </p>

                            <div style="background-color: #f8faf9; border-radius: 12px; padding: 20px; margin: 24px 0; border: 1px solid #e5e7eb;">
                                <p style="margin: 0 0 12px; line-height: 1.5; font-size: 0.95rem; color: #0D130F;"><strong style="color: #0D130F;">Tipo de petición:</strong> %s</p>
                                <p style="margin: 0 0 12px; line-height: 1.5; font-size: 0.95rem; color: #0D130F;"><strong style="color: #0D130F;">Asunto:</strong> %s</p>
                                <p style="margin: 0; line-height: 1.5; font-size: 0.95rem; color: #0D130F;"><strong style="color: #0D130F;">Solicitud:</strong> #%d</p>
                            </div>
                            %s

                            <div style="text-align: center; margin: 24px 0;">
                                <a href="https://dlgchr.com.co" style="display: inline-block; background-color: #3ba86a; color: #ffffff !important; text-decoration: none; padding: 14px 28px; border-radius: 12px; font-weight: 600; font-size: 1rem;">Ir al módulo SST</a>
                            </div>

                            <p style="margin: 0; line-height: 1.6; font-size: 0.85rem; color: #6b7280;">Ingresa al portal DLGC RRHH para revisar el detalle de tu solicitud.</p>
                        </div>
                        <div style="text-align: center; padding: 16px 32px; font-size: 0.8rem; color: #6b7280; border-top: 1px solid #e5e7eb;">
                            &copy; Distribuciones La Gran Cacharrería. Todos los derechos reservados.
                        </div>
                    </div>
                </div>
            </body>
            </html>',
            $nombreHtml,
            $conf['badgeBg'],
            $conf['badgeColor'],
            $conf['texto'],
            $tipoPeticionHtml,
            $asuntoHtml,
            $idQueja,
            $bloqueRespuesta
        );

        $altBody = "Portal DLGC RRHH\n\n"
                 . "Hola {$fila['primer_nombre']},\n\n"
                 . "Tu solicitud #{$idQueja} ({$tipoPeticion}) del Buzón SST fue {$conf['texto']}.\n"
                 . "Asunto: {$fila['asunto']}\n";
        if (trim((string) $fila['respuesta']) !== '') {
            $altBody .= "\nRespuesta del encargado:\n{$fila['respuesta']}\n";
        }
        $altBody .= "\nIngresa al portal DLGC RRHH para revisar el detalle de tu solicitud.\n\n"
                 . "Distribuciones La Gran Cacharrería";

        $mailer->send($fila['correo'], (string) $fila['primer_nombre'], $subject, $body, $altBody);
    } catch (Throwable $e) {
        error_log('cambiar_estado_queja: no se pudo notificar el cambio de estado: ' . $e->getMessage());
    }
}
