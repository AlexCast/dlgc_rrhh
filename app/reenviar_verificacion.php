<?php
/**
 * reenviar_verificacion.php
 * Reenvía el correo de verificación a un usuario con una activación pendiente.
 *
 * A diferencia de un nuevo registro, aquí no se invalidan los tokens previos
 * marcando fec_delete: el trigger tri_limpiar_registros_pendientes eliminaría
 * el registro pendiente (y en cascada todos sus tokens). En su lugar, los
 * tokens activos anteriores se expiran vía fecha_expiracion.
 *
 * Soporta dos modos de respuesta:
 *  - Clásico (formulario): redirige a /templates/login.php con parámetros.
 *  - AJAX (X-Requested-With: XMLHttpRequest o campo ajax=1): responde JSON.
 */

declare(strict_types=1);

require_once __DIR__ . '/session_bootstrap.php';
require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/csrf_guard.php';
require_once __DIR__ . '/helpers/Mailer.php';
require_once __DIR__ . '/helpers/RateLimiter.php';
require_once __DIR__ . '/helpers/TurnstileHelper.php';

$urlLogin = '/templates/login.php';
$usrInsert = 'sistema_reenvio';

$isAjax = ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest'
    || ($_POST['ajax'] ?? '') === '1';

function redirigirLogin(array $parametros): void
{
    global $urlLogin, $isAjax;

    if ($isAjax) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'status' => $parametros['status'] ?? 'error',
            'code'   => $parametros['code'] ?? '',
            'msg'    => $parametros['msg'] ?? '',
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    header('Location: ' . $urlLogin . '?' . http_build_query($parametros));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirigirLogin([
        'tab' => 'register',
        'status' => 'error',
        'code' => 'metodo_no_valido',
    ]);
}

// Validate CSRF token for verification resend.
csrf_validate();

// Verificación anti-bot (Cloudflare Turnstile).
if (!TurnstileHelper::verify($_POST['cf-turnstile-response'] ?? null, 'resend')) {
    redirigirLogin([
        'tab' => 'register',
        'status' => 'error',
        'code' => 'turnstile_invalido',
    ]);
}

$identificador = strtolower(trim($_POST['identificador'] ?? ''));

if ($identificador === '' || strlen($identificador) < 3 || strlen($identificador) > 40) {
    redirigirLogin([
        'tab' => 'register',
        'status' => 'error',
        'code' => 'reenvio_datos_invalidos',
    ]);
}

try {
    // Limpiar registros pendientes expirados del mismo usuario/correo para
    // que el usuario pueda registrarse de nuevo si ya vencieron.
    $limpiarPendientes = $conexion->prepare(
        "DELETE FROM t_registros_pendientes
         WHERE (username = ? OR correo = ?)
           AND fecha_expiracion < CURRENT_TIMESTAMP"
    );
    $limpiarPendientes->execute([
        $identificador,
        $identificador,
    ]);

    $sentencia = $conexion->prepare(
        "SELECT id_registro_pendiente, username, correo, primer_nombre, primer_apellido, id_usuario
         FROM t_registros_pendientes
         WHERE (username = ? OR correo = ?)
           AND fec_delete IS NULL
         ORDER BY id_registro_pendiente DESC
         LIMIT 1"
    );
    $sentencia->execute([
        $identificador,
        $identificador,
    ]);
    $pendiente = $sentencia->fetch();

    if (!$pendiente) {
        redirigirLogin([
            'tab' => 'register',
            'status' => 'error',
            'code' => 'reenvio_sin_pendiente',
        ]);
    }

    // --- Rate limiting (comparte presupuesto con el registro) ---
    $rateLimiter = new RateLimiter($conexion);
    $rateCheck = $rateLimiter->verificar($pendiente['id_usuario'], 'verificacion');

    if (!$rateCheck['permitido']) {
        redirigirLogin([
            'tab' => 'register',
            'status' => 'error',
            'code' => 'demasiados_intentos',
            'msg' => 'Has enviado demasiadas solicitudes. Inténtalo de nuevo en ' . $rateCheck['minutos'] . ' minuto(s).',
        ]);
    }

    // Generar token seguro de 32 bytes -> 64 caracteres hex.
    $token = bin2hex(random_bytes(32));
    $tokenHash = hash('sha256', $token);
    $fechaExpiracion = (new DateTime('+24 hours'))->format('Y-m-d H:i:s');

    // Expirar tokens activos anteriores SIN marcar fec_delete (ver cabecera).
    $expirarPrevios = $conexion->prepare(
        "UPDATE t_verificacion_correo
         SET fecha_expiracion = CURRENT_TIMESTAMP,
             usr_update = ?,
             fec_update = CURRENT_TIMESTAMP
         WHERE id_registro_pendiente = ?
           AND fecha_uso IS NULL
           AND fec_delete IS NULL
           AND fecha_expiracion > CURRENT_TIMESTAMP"
    );
    $expirarPrevios->execute([
        $usrInsert,
        $pendiente['id_registro_pendiente'],
    ]);

    $insertarToken = $conexion->prepare(
        "INSERT INTO t_verificacion_correo
            (id_registro_pendiente, token_hash, fecha_expiracion, usr_insert, fec_insert)
         VALUES
            (?, ?, ?, ?, CURRENT_TIMESTAMP)"
    );
    $insertarToken->execute([
        $pendiente['id_registro_pendiente'],
        $tokenHash,
        $fechaExpiracion,
        $usrInsert,
    ]);

    // Construir enlace y enviar correo de verificación.
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host   = $_SERVER['HTTP_HOST'];
    $link   = $scheme . '://' . $host . '/app/verificar_correo.php?token=' . $token;

    $nombreDestinatario = trim($pendiente['primer_nombre'] . ' ' . $pendiente['primer_apellido']);
    if ($nombreDestinatario === '') {
        $nombreDestinatario = $pendiente['username'];
    }

    $subject = 'Verifica tu correo - Portal DLGC';

    $body = sprintf(
        '<!DOCTYPE html>
        <html lang="es">
        <head><meta charset="UTF-8"><title>Verificación de Correo</title></head>
        <body style="margin:0;padding:0;background-color:#ececec;font-family:Inter,system-ui,-apple-system,sans-serif;color:#0D130F;">
            <div style="max-width:480px;margin:0 auto;padding:40px 16px;">
                <div style="background-color:#ffffff;border-radius:24px;border:2px solid #3ba86a;overflow:hidden;">
                    <div style="background-color:#3ba86a;padding:24px;text-align:center;">
                        <h1 style="color:#ffffff;margin:0;font-size:1.25rem;">Portal DLGC RRHH</h1>
                    </div>
                    <div style="padding:32px;">
                        <p style="margin:0 0 16px;line-height:1.6;">Hola <strong>%s</strong>,</p>
                        <p style="margin:0 0 16px;line-height:1.6;">Haz clic en el botón de abajo para verificar tu correo y activar tu cuenta:</p>
                        <div style="text-align:center;margin:24px 0;">
                            <a href="%s" style="display:inline-block;background-color:#3ba86a;color:#ffffff !important;text-decoration:none;padding:14px 28px;border-radius:12px;font-weight:600;">Verificar Correo</a>
                        </div>
                        <p style="font-size:0.85rem;color:#6b7280;">Este enlace expirará en <strong>24 horas</strong>. Si no creaste esta cuenta, ignora este mensaje.</p>
                        <p>Saludos,<br><strong>Portal DLGC RRHH</strong></p>
                    </div>
                </div>
            </div>
        </body>
        </html>',
        htmlspecialchars($nombreDestinatario, ENT_QUOTES, 'UTF-8'),
        htmlspecialchars($link, ENT_QUOTES, 'UTF-8')
    );

    $altBody = "Portal DLGC RRHH\n\n"
             . "Hola {$nombreDestinatario},\n\n"
             . "Verifica tu correo visitando el siguiente enlace (válido por 24 horas):\n{$link}\n\n"
             . "Si no creaste esta cuenta, ignora este mensaje.\n\n"
             . "Distribuciones La Gran Cacharrería";

    try {
        $mailer = new Mailer();
        $enviado = $mailer->send($pendiente['correo'], $nombreDestinatario, $subject, $body, $altBody);
    } catch (Exception $e) {
        error_log('Error enviando correo de verificación (reenvío): ' . $e->getMessage());
        $enviado = false;
    }

    $rateLimiter->registrar($pendiente['id_usuario'], 'verificacion');

    if (!$enviado) {
        redirigirLogin([
            'tab' => 'register',
            'status' => 'error',
            'code' => 'reenvio_fallo',
        ]);
    }

    redirigirLogin([
        'tab' => 'login',
        'status' => 'success',
        'code' => 'reenvio_enviado',
    ]);

} catch (PDOException $e) {
    error_log('Error en reenvío de verificación: ' . $e->getMessage());
    redirigirLogin([
        'tab' => 'register',
        'status' => 'error',
        'code' => 'error_servidor',
    ]);
}
