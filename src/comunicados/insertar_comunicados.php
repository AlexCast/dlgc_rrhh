<?php

declare(strict_types=1);

$moduleId = 24;
$requiredAction = 'crear';
require_once __DIR__ . '/../../app/src_guard.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate();
}

if (
    !isset($_POST['titulo']) ||
    !isset($_POST['contenido']) ||
    !isset($_POST['categoria'])
) {
    header('Location: forma_comunicados.php?error=datos');
    exit();
}

require_once __DIR__ . '/../../app/helpers/HtmlSanitizer.php';
require_once __DIR__ . '/../../app/helpers/InputSanitizer.php';

$titulo = InputSanitizer::boundedText($_POST['titulo'] ?? null, 100);
$contenido = trim(HtmlSanitizer::clean((string) $_POST['contenido']));
$categoria = InputSanitizer::whitelist($_POST['categoria'] ?? null, ['GENERAL', 'URGENTE', 'EVENTO', 'INFORMACION', 'INSTITUCIONAL']);
$enviarCorreo = (string) ($_POST['enviar_correo'] ?? '') === '1';

if ($titulo === null || strip_tags($contenido) === '' || $categoria === null) {
    header('Location: forma_comunicados.php?error=datos');
    exit();
}

require_once __DIR__ . '/../../app/conexion.php';

$sentencia = $conexion->prepare('SELECT fun_insert_comunicados(?, ?, ?);');
$sentencia->execute([$titulo, $contenido, $categoria]);
$resultado = (string) $sentencia->fetchColumn();

if (stripos($resultado, 'correctamente') !== false) {
    $mensaje = 'Comunicado publicado correctamente.';

    if ($enviarCorreo) {
        require_once __DIR__ . '/../../app/helpers/Mailer.php';
        $notificados = notificarComunicadoPorCorreo($conexion, [
            'titulo'    => $titulo,
            'contenido' => $contenido,
            'categoria' => $categoria,
            'autor'     => (string) ($_SESSION['nombre_completo'] ?? 'Portal DLGC RRHH'),
        ]);

        $mensaje .= $notificados > 0
            ? ' Notificado por correo a ' . $notificados . ' empleado(s).'
            : ' No fue posible notificar por correo (sin destinatarios o error de envío).';
    }

    header('Location: listar_comunicados.php?success=' . urlencode($mensaje));
    exit();
}

header('Location: forma_comunicados.php?error=' . urlencode($resultado));
exit();

/**
 * Notifica por correo la publicación de un comunicado a todos los empleados
 * activos que tengan un correo registrado.
 *
 * @return int Cantidad de destinatarios notificados (0 si no se envió).
 */
function notificarComunicadoPorCorreo(PDO $conexion, array $comunicado): int
{
    $sentencia = $conexion->prepare(
        "SELECT u.correo AS email,
                CONCAT_WS(' ', u.primer_nombre, u.primer_apellido) AS name
         FROM t_empleados e
         INNER JOIN t_usuarios u ON e.id_usuario = u.id_usuario
         WHERE e.fec_delete IS NULL
           AND (e.fecha_egreso IS NULL OR e.fecha_egreso > CURRENT_DATE)
           AND u.fec_delete IS NULL
           AND u.correo IS NOT NULL
           AND TRIM(u.correo) <> ''"
    );
    $sentencia->execute();
    $empleados = $sentencia->fetchAll(PDO::FETCH_ASSOC);

    if ($empleados === []) {
        return 0;
    }

    $categoria = (string) $comunicado['categoria'];
    $tituloHtml = htmlspecialchars((string) $comunicado['titulo'], ENT_QUOTES, 'UTF-8');
    $categoriaHtml = htmlspecialchars($categoria, ENT_QUOTES, 'UTF-8');
    $autorHtml = htmlspecialchars((string) $comunicado['autor'], ENT_QUOTES, 'UTF-8');
    $contenidoHtml = (string) $comunicado['contenido'];

    $colores = [
        'GENERAL'       => ['bg' => '#eafaf1', 'color' => '#3ba86a'],
        'URGENTE'       => ['bg' => '#fdeceb', 'color' => '#dc2626'],
        'EVENTO'        => ['bg' => '#f1ebfd', 'color' => '#7c3aed'],
        'INFORMACION'   => ['bg' => '#e6f4fc', 'color' => '#0284c7'],
        'INSTITUCIONAL' => ['bg' => '#fdf3e3', 'color' => '#d97706'],
    ];
    $badge = $colores[$categoria] ?? $colores['GENERAL'];

    $subject = 'Nuevo comunicado - ' . (string) $comunicado['titulo'];

    $body = '<!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>' . $tituloHtml . '</title>
        <style>
            body { margin: 0; padding: 0; background-color: #ececec; font-family: Inter, system-ui, -apple-system, sans-serif; color: #0D130F; }
            .wrapper { width: 100%; padding: 40px 16px; }
            .card { max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 24px; border: 2px solid #3ba86a; box-shadow: 0 12px 30px rgba(59, 168, 106, 0.12); overflow: hidden; }
            .header { background-color: #3ba86a; padding: 24px; text-align: center; }
            .header h1 { color: #ffffff; margin: 0; font-size: 1.25rem; font-weight: 700; }
            .content { padding: 32px; }
            .content p { margin: 0 0 16px; line-height: 1.6; font-size: 0.95rem; color: #0D130F; }
            .badge { display: inline-block; background-color: ' . $badge['bg'] . '; color: ' . $badge['color'] . '; border-radius: 999px; padding: 4px 14px; font-weight: 600; font-size: 0.8rem; }
            .post-title { margin: 16px 0 12px; font-size: 1.4rem; font-weight: 800; color: #0D130F; line-height: 1.3; }
            .post-body { background-color: #f9fafb; border: 1px solid #e5e7eb; border-radius: 16px; padding: 20px 24px; margin: 0 0 16px; }
            .post-body p { margin: 0 0 12px; line-height: 1.6; font-size: 0.95rem; color: #0D130F; }
            .post-body p:last-child { margin-bottom: 0; }
            .post-body ul, .post-body ol { margin: 0 0 12px; padding-left: 24px; }
            .post-body li { line-height: 1.6; font-size: 0.95rem; color: #0D130F; }
            .post-body h2, .post-body h3 { margin: 16px 0 8px; color: #0D130F; }
            .post-body blockquote { margin: 12px 0; padding: 8px 16px; border-left: 4px solid #3ba86a; background-color: #f0fdf4; color: #0D130F; }
            .notice { font-size: 0.85rem; color: #6b7280; }
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
                    <p>Hola,</p>
                    <p><span class="badge">' . $categoriaHtml . '</span></p>
                    <h2 class="post-title">' . $tituloHtml . '</h2>
                    <div class="post-body">' . $contenidoHtml . '</div>
                    <p class="notice">Publicado por ' . $autorHtml . ' a través del portal DLGC RRHH. Si tienes dudas, contacta al área de Recursos Humanos.</p>
                    <p>Saludos,<br><strong>Portal DLGC RRHH</strong></p>
                </div>
                <div class="footer">
                    &copy; Distribuciones La Gran Cacharrería. Todos los derechos reservados.
                </div>
            </div>
        </div>
    </body>
    </html>';

    $altBody = "Portal DLGC RRHH\n\n"
        . "Hola,\n\n"
        . "Se ha publicado un nuevo comunicado (" . $categoria . "):\n\n"
        . (string) $comunicado['titulo'] . "\n\n"
        . strip_tags($contenidoHtml) . "\n\n"
        . "Publicado por " . (string) $comunicado['autor'] . " a través del portal DLGC RRHH.\n\n"
        . "Distribuciones La Gran Cacharrería";

    try {
        $mailer = new Mailer();
        return $mailer->sendBulk($empleados, $subject, $body, $altBody);
    } catch (Throwable $e) {
        error_log('notificarComunicadoPorCorreo: no se pudo enviar la notificación: ' . $e->getMessage());
        return 0;
    }
}
