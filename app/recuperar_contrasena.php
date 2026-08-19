<?php
/**
 * recuperar_contrasena.php
 * Procesa la solicitud de recuperación de contraseña.
 * Genera un token seguro, lo guarda hasheado en BD y envía el enlace por correo.
 */

declare(strict_types=1);

require_once __DIR__ . '/session_bootstrap.php';
require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/csrf_guard.php';
require_once __DIR__ . '/helpers/Mailer.php';
require_once __DIR__ . '/helpers/RateLimiter.php';
require_once __DIR__ . '/helpers/InputSanitizer.php';

$urlLogin = '/dlgc_rrhh/templates/login.php';

function redirigirLogin(array $parametros): void
{
    global $urlLogin;
    header('Location: ' . $urlLogin . '?' . http_build_query($parametros));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirigirLogin([
        'tab' => 'recuperar',
        'status' => 'error',
        'code' => 'metodo_no_valido',
    ]);
}

// Validate CSRF token for password recovery.
csrf_validate();

$email = InputSanitizer::email($_POST['email'] ?? '');

if ($email === '' || !InputSanitizer::validateEmail($email) || strlen($email) > 40) {
    redirigirLogin([
        'tab' => 'recuperar',
        'status' => 'error',
        'code' => 'email_invalido',
    ]);
}

try {
    $sentencia = $conexion->prepare(
        "SELECT id_usuario, username, primer_nombre, primer_apellido, correo
         FROM t_usuarios
         WHERE correo = :email
           AND fec_delete IS NULL
         LIMIT 1"
    );
    $sentencia->execute([':email' => $email]);
    $usuario = $sentencia->fetch();

    // Si no existe el usuario, no revelamos información.
    if (!$usuario) {
        redirigirLogin([
            'tab' => 'recuperar',
            'status' => 'info',
            'code' => 'solicitud_procesada',
        ]);
    }

    // --- Rate limiting para recuperación ---
    $rateLimiter = new RateLimiter($conexion);
    $rateCheck = $rateLimiter->verificar($usuario['id_usuario'], 'recuperacion');

    if (!$rateCheck['permitido']) {
        redirigirLogin([
            'tab' => 'recuperar',
            'status' => 'error',
            'code' => 'demasiados_intentos',
            'msg' => 'Has enviado demasiadas solicitudes. Inténtalo de nuevo en ' . $rateCheck['minutos'] . ' minuto(s).',
        ]);
    }

    // Generar token aleatorio seguro de 32 bytes -> 64 caracteres hex.
    $token = bin2hex(random_bytes(32));
    $tokenHash = hash('sha256', $token);

    // Expiración: 1 hora.
    $fechaExpiracion = (new DateTime('+1 hour'))->format('Y-m-d H:i:s');
    $usrInsert = $_SESSION['username'] ?? 'sistema_recuperacion';

    // Invalidar tokens anteriores no usados del mismo usuario (soft delete).
    $invalidar = $conexion->prepare(
        "UPDATE t_recuperacion_contrasena
         SET fec_delete = CURRENT_TIMESTAMP,
             usr_delete = :usr_delete
         WHERE id_usuario = :id_usuario
           AND fecha_uso IS NULL
           AND fec_delete IS NULL"
    );
    $invalidar->execute([
        ':id_usuario' => $usuario['id_usuario'],
        ':usr_delete' => $usrInsert,
    ]);

    // Borrar tokens expirados globalmente (del mismo usuario y de todos).
    $limpiarExpirados = $conexion->prepare(
        "DELETE FROM t_recuperacion_contrasena
         WHERE fecha_expiracion < CURRENT_TIMESTAMP
            OR fecha_uso IS NOT NULL
            OR fec_delete IS NOT NULL"
    );
    $limpiarExpirados->execute();

    // Insertar nuevo token.
    $insertar = $conexion->prepare(
        "INSERT INTO t_recuperacion_contrasena
            (id_usuario, token_hash, fecha_expiracion, usr_insert, fec_insert)
         VALUES
            (:id_usuario, :token_hash, :fecha_expiracion, :usr_insert, CURRENT_TIMESTAMP)"
    );
    $insertar->execute([
        ':id_usuario'       => $usuario['id_usuario'],
        ':token_hash'       => $tokenHash,
        ':fecha_expiracion' => $fechaExpiracion,
        ':usr_insert'       => $usrInsert,
    ]);

    // Construir enlace de recuperación.
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host   = $_SERVER['HTTP_HOST'];
    $link   = $scheme . '://' . $host . '/dlgc_rrhh/app/restablecer_contrasena.php?token=' . $token;

    // Si el servidor está en un subdirectorio virtual distinto, ajustar aquí.

    $nombreDestinatario = trim(($usuario['primer_nombre'] ?? '') . ' ' . ($usuario['primer_apellido'] ?? ''));
    if ($nombreDestinatario === '') {
        $nombreDestinatario = $usuario['username'];
    }

    $subject = 'Recuperación de contraseña - Portal DLGC';

    $body = sprintf(
        '<!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Recuperación de Contraseña</title>
            <style>
                body { margin: 0; padding: 0; background-color: #ececec; font-family: Inter, system-ui, -apple-system, sans-serif; color: #0D130F; }
                .wrapper { width: 100%%; padding: 40px 16px; }
                .card { max-width: 480px; margin: 0 auto; background-color: #ffffff; border-radius: 24px; border: 2px solid #3ba86a; box-shadow: 0 12px 30px rgba(59, 168, 106, 0.12); overflow: hidden; }
                .header { background-color: #3ba86a; padding: 24px; text-align: center; }
                .header h1 { color: #ffffff; margin: 0; font-size: 1.25rem; font-weight: 700; }
                .content { padding: 32px; }
                .content p { margin: 0 0 16px; line-height: 1.6; font-size: 0.95rem; color: #0D130F; }
                .content p strong { color: #0D130F; }
                .content a { color: #0D130F; text-decoration: none; }
                .btn-container { text-align: center; margin: 24px 0; }
                .btn { display: inline-block; background-color: #3ba86a; color: #ffffff !important; text-decoration: none; padding: 14px 28px; border-radius: 12px; font-weight: 600; font-size: 1rem; }
                .btn:hover { background-color: #298350; }
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
                        <p>Recibimos una solicitud para restablecer tu contraseña. Haz clic en el botón de abajo para continuar:</p>
                        <div class="btn-container">
                            <a href="%s" class="btn" style="color: #ffffff !important; text-decoration: none;">Restablecer Contraseña</a>
                        </div>
                        <p class="notice">Este enlace expirará en <strong>1 hora</strong>. Si no solicitaste este cambio, ignora este mensaje.</p>
                        <p>Saludos,<br><strong>Portal DLGC RRHH</strong></p>
                    </div>
                    <div class="footer">
                        &copy; Distribuciones La Gran Cacharrería. Todos los derechos reservados.
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
             . "Recibimos una solicitud para restablecer tu contraseña.\n\n"
             . "Visita el siguiente enlace (válido por 1 hora):\n{$link}\n\n"
             . "Si no solicitaste este cambio, ignora este mensaje.\n\n"
             . "Distribuciones La Gran Cacharrería";

    $mailer = new Mailer();
    $enviado = $mailer->send($usuario['correo'], $nombreDestinatario, $subject, $body, $altBody);

    if (!$enviado) {
        error_log('No se pudo enviar correo de recuperación a: ' . $usuario['correo']);
    }

    // Registrar intento de envío.
    $rateLimiter->registrar($usuario['id_usuario'], 'recuperacion');

    // Independientemente de si se envió, mostramos mensaje genérico de éxito.
    redirigirLogin([
        'tab' => 'recuperar',
        'status' => 'info',
        'code' => 'solicitud_procesada',
    ]);

} catch (PDOException $e) {
    error_log('Error en recuperación de contraseña: ' . $e->getMessage());
    redirigirLogin([
        'tab' => 'recuperar',
        'status' => 'error',
        'code' => 'error_servidor',
    ]);
} catch (Exception $e) {
    error_log('Error inesperado en recuperación: ' . $e->getMessage());
    redirigirLogin([
        'tab' => 'recuperar',
        'status' => 'error',
        'code' => 'error_servidor',
    ]);
}
