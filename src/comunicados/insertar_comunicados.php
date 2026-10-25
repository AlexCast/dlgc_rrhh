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
                CONCAT_WS(
                    ' ',
                    NULLIF(TRIM(u.primer_nombre), ''),
                    NULLIF(TRIM(u.segundo_nombre), ''),
                    NULLIF(TRIM(u.primer_apellido), ''),
                    NULLIF(TRIM(u.segundo_apellido), '')
                ) AS name
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

    $plantilla = construirCorreoComunicado($comunicado);

    $enviados = 0;

    try {
        $mailer = new Mailer();
    } catch (Throwable $e) {
        error_log('notificarComunicadoPorCorreo: no se pudo inicializar el correo: ' . $e->getMessage());
        return 0;
    }

    // Envío individual por destinatario: las APIs REST no soportan BCC, por lo
    // que cada empleado recibe su propio correo con saludo personalizado.
    foreach ($empleados as $empleado) {
        $email = trim((string) ($empleado['email'] ?? ''));
        if ($email === '') {
            continue;
        }

        $nombre = trim((string) ($empleado['name'] ?? ''));
        $nombreHtml = $nombre !== '' ? htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8') : '';

        $saludoHtml = $nombreHtml !== ''
            ? 'Hola, <strong>' . $nombreHtml . '</strong>. Has recibido un nuevo comunicado.'
            : 'Hola. Has recibido un nuevo comunicado.';
        $saludoTexto = $nombre !== ''
            ? 'Hola, ' . $nombre . '. Has recibido un nuevo comunicado.'
            : 'Hola. Has recibido un nuevo comunicado.';

        $body = str_replace('{{saludo}}', $saludoHtml, $plantilla['body']);
        $altBody = str_replace('{{saludo_texto}}', $saludoTexto, $plantilla['altBody']);

        if ($mailer->send($email, $nombre, $plantilla['subject'], $body, $altBody)) {
            $enviados++;
        }
    }

    return $enviados;
}

/**
 * Construye la plantilla del correo de un comunicado.
 *
 * El cuerpo HTML y el texto plano incluyen los marcadores {{saludo}} y
 * {{saludo_texto}} respectivamente, que se reemplazan por un saludo
 * personalizado con el nombre de cada destinatario.
 *
 * @param array $comunicado Datos del comunicado (titulo, contenido, categoria, autor).
 *
 * @return array{subject:string, body:string, altBody:string}
 */
function construirCorreoComunicado(array $comunicado): array
{
    $categoria = (string) $comunicado['categoria'];
    $tituloHtml = htmlspecialchars((string) $comunicado['titulo'], ENT_QUOTES, 'UTF-8');
    $categoriaHtml = htmlspecialchars($categoria, ENT_QUOTES, 'UTF-8');
    $autorHtml = htmlspecialchars((string) $comunicado['autor'], ENT_QUOTES, 'UTF-8');
    $contenidoHtml = (string) $comunicado['contenido'];

    $colores = [
        'GENERAL'       => ['bg' => '#eafaf1', 'color' => '#3ba86a', 'bar' => '#3ba86a'],
        'URGENTE'       => ['bg' => '#fdeceb', 'color' => '#dc2626', 'bar' => '#dc2626'],
        'EVENTO'        => ['bg' => '#f1ebfd', 'color' => '#7c3aed', 'bar' => '#7c3aed'],
        'INFORMACION'   => ['bg' => '#e6f4fc', 'color' => '#0284c7', 'bar' => '#0284c7'],
        'INSTITUCIONAL' => ['bg' => '#fdf3e3', 'color' => '#d97706', 'bar' => '#d97706'],
    ];
    $badge = $colores[$categoria] ?? $colores['GENERAL'];

    $saludo = '{{saludo}}';

    $subject = 'Nuevo comunicado (' . $categoria . '): ' . (string) $comunicado['titulo'];

    $body = '<!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>' . $tituloHtml . '</title>
        <style>
            body { margin: 0; padding: 0; background-color: #ececec; font-family: Inter, system-ui, -apple-system, sans-serif; color: #0D130F; }
            .wrapper { width: 100%; padding: 40px 16px; }
            .card { max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 18px; box-shadow: 0 12px 30px rgba(13, 19, 15, 0.10); overflow: hidden; }
            .accent { height: 8px; background-color: ' . $badge['bar'] . '; }
            .header { padding: 28px 32px 4px; }
            .eyebrow { display: inline-block; background-color: ' . $badge['bg'] . '; color: ' . $badge['color'] . '; border-radius: 999px; padding: 4px 14px; font-weight: 700; font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.6px; }
            .header h1 { margin: 16px 0 4px; font-size: 1.4rem; font-weight: 800; color: #0D130F; line-height: 1.3; }
            .header .portal { color: #6b7280; font-size: 0.82rem; margin: 0; }
            .content { padding: 20px 32px 32px; }
            .greeting { margin: 0 0 18px; line-height: 1.6; font-size: 0.95rem; color: #0D130F; }
            .greeting strong { color: #0D130F; }
            .post-body { background-color: #f9fafb; border: 1px solid #e5e7eb; border-radius: 14px; padding: 20px 24px; margin: 0 0 18px; }
            .post-body p { margin: 0 0 12px; line-height: 1.6; font-size: 0.95rem; color: #0D130F; }
            .post-body p:last-child { margin-bottom: 0; }
            .post-body ul, .post-body ol { margin: 0 0 12px; padding-left: 24px; }
            .post-body li { line-height: 1.6; font-size: 0.95rem; color: #0D130F; }
            .post-body h2, .post-body h3 { margin: 16px 0 8px; color: #0D130F; }
            .post-body blockquote { margin: 12px 0; padding: 8px 16px; border-left: 4px solid ' . $badge['bar'] . '; background-color: #f0fdf4; color: #0D130F; }
            .notice { font-size: 0.85rem; color: #6b7280; line-height: 1.5; margin: 0 0 16px; }
            .signature { font-size: 0.95rem; line-height: 1.6; color: #0D130F; margin: 0; }
            .footer { text-align: center; padding: 16px 32px; font-size: 0.8rem; color: #6b7280; border-top: 1px solid #e5e7eb; }
        </style>
    </head>
    <body>
        <div class="wrapper">
            <div class="card">
                <div class="accent"></div>
                <div class="header">
                    <span class="eyebrow">' . $categoriaHtml . '</span>
                    <h1>' . $tituloHtml . '</h1>
                    <p class="portal">Portal DLGC RRHH &middot; Comunicados internos</p>
                </div>
                <div class="content">
                    <p class="greeting">' . $saludo . '</p>
                    <div class="post-body">' . $contenidoHtml . '</div>
                    <p class="notice">Publicado por ' . $autorHtml . '. Si tienes dudas sobre este comunicado, contacta al área de Recursos Humanos.</p>
                    <p class="signature">Saludos,<br><strong>Portal DLGC RRHH</strong></p>
                </div>
                <div class="footer">
                    &copy; Distribuciones La Gran Cacharrería. Todos los derechos reservados.
                </div>
            </div>
        </div>
    </body>
    </html>';

    $altSaludo = '{{saludo_texto}}';

    $altBody = "Portal DLGC RRHH - Comunicados internos\n\n"
        . $altSaludo . "\n\n"
        . "Categoría: " . $categoria . "\n"
        . "Título: " . (string) $comunicado['titulo'] . "\n\n"
        . strip_tags($contenidoHtml) . "\n\n"
        . "Publicado por " . (string) $comunicado['autor'] . ".\n"
        . "Si tienes dudas, contacta al área de Recursos Humanos.\n\n"
        . "Distribuciones La Gran Cacharrería";

    return [
        'subject' => $subject,
        'body'    => $body,
        'altBody' => $altBody,
    ];
}
