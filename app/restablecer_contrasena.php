<?php
/**
 * restablecer_contrasena.php
 * Valida el token de recuperación y permite establecer una nueva contraseña.
 */

declare(strict_types=1);

require_once __DIR__ . '/session_bootstrap.php';
require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/csrf_guard.php';
require_once __DIR__ . '/helpers/InputSanitizer.php';
require_once __DIR__ . '/helpers/RememberMeHelper.php';

$urlLogin = '/dlgc_rrhh/templates/login.php';

function redirigirLogin(array $parametros): void
{
    global $urlLogin;
    header('Location: ' . $urlLogin . '?' . http_build_query($parametros));
    exit;
}

$token = $_GET['token'] ?? ($_POST['token'] ?? '');
$token = trim($token);

if ($token === '' || !ctype_xdigit($token) || strlen($token) !== 64) {
    redirigirLogin([
        'tab' => 'recuperar',
        'status' => 'error',
        'code' => 'token_invalido',
    ]);
}

$tokenHash = hash('sha256', $token);

try {
    $sentencia = $conexion->prepare(
        "SELECT id_recuperacion, id_usuario, fecha_expiracion, fecha_uso, fec_delete
         FROM t_recuperacion_contrasena
         WHERE token_hash = :token_hash
         LIMIT 1"
    );
    $sentencia->execute([':token_hash' => $tokenHash]);
    $recuperacion = $sentencia->fetch();

    if (!$recuperacion) {
        redirigirLogin([
            'tab' => 'recuperar',
            'status' => 'error',
            'code' => 'token_invalido',
        ]);
    }

    $ahora = new DateTime();
    $expiracion = new DateTime($recuperacion['fecha_expiracion']);

    if (
        $recuperacion['fecha_uso'] !== null ||
        $recuperacion['fec_delete'] !== null ||
        $ahora > $expiracion
    ) {
        redirigirLogin([
            'tab' => 'recuperar',
            'status' => 'error',
            'code' => 'token_expirado',
        ]);
    }

    // Si es GET, mostrar formulario de nueva contraseña.
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        mostrarFormulario($token);
        exit;
    }

    // Procesar POST.
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        redirigirLogin([
            'tab' => 'recuperar',
            'status' => 'error',
            'code' => 'metodo_no_valido',
        ]);
    }

    // Validate CSRF token for password reset.
    csrf_validate();

    $nuevaContrasena     = $_POST['nueva_contrasena'] ?? '';
    $confirmarContrasena = $_POST['confirmar_contrasena'] ?? '';

    $validacionContrasena = InputSanitizer::validatePassword($nuevaContrasena, $confirmarContrasena);
    if (!$validacionContrasena['valid']) {
        mostrarFormulario(
            $token,
            implode(' ', $validacionContrasena['errors'])
        );
        exit;
    }

    $hash = password_hash($nuevaContrasena, PASSWORD_BCRYPT);

    $actualizar = $conexion->prepare(
        "UPDATE t_usuarios
         SET contrasena = :contrasena,
             usr_update = :usr_update,
             fec_update = CURRENT_TIMESTAMP
         WHERE id_usuario = :id_usuario"
    );
    $actualizar->execute([
        ':contrasena' => $hash,
        ':usr_update' => $recuperacion['id_usuario'],
        ':id_usuario' => $recuperacion['id_usuario'],
    ]);

    // Marcar token como usado.
    $usarToken = $conexion->prepare(
        "UPDATE t_recuperacion_contrasena
         SET fecha_uso = CURRENT_TIMESTAMP,
             usr_update = :usr_update,
             fec_update = CURRENT_TIMESTAMP
         WHERE id_recuperacion = :id_recuperacion"
    );
    $usarToken->execute([
        ':usr_update'       => $recuperacion['id_usuario'],
        ':id_recuperacion'  => $recuperacion['id_recuperacion'],
    ]);

    // Al cambiar contraseña se invalidan todos los tokens "Recordarme" del usuario.
    RememberMeHelper::invalidateAllForUser($conexion, (string) $recuperacion['id_usuario']);

    redirigirLogin([
        'tab' => 'login',
        'status' => 'success',
        'code' => 'contrasena_actualizada',
    ]);

} catch (PDOException $e) {
    error_log('Error en restablecimiento de contraseña: ' . $e->getMessage());
    redirigirLogin([
        'tab' => 'recuperar',
        'status' => 'error',
        'code' => 'error_servidor',
    ]);
}

function mostrarFormulario(string $token, string $error = ''): void
{
    $errorHtml = $error !== '' ? '<div class="auth-feedback error">' . htmlspecialchars($error, ENT_QUOTES, 'UTF-8') . '</div>' : '';
    echo '<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restablecer Contraseña | Portal DLGC</title>
    <link rel="stylesheet" href="/dlgc_rrhh/assets/css/login.css">
    <link rel="stylesheet" href="/dlgc_rrhh/assets/css/recover_password.css">
</head>
<body>
    <main class="auth-wrapper">
        <div class="auth-card fade-in-up">
            <div class="auth-header-text">
                <h1>Restablecer Contraseña</h1>
                <p>Ingresa tu nueva contraseña</p>
            </div>
            ' . $errorHtml . '
            <form id="reset-form" class="auth-form active" action="/dlgc_rrhh/app/restablecer_contrasena.php" method="POST">
                <input type="hidden" name="token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">
                ' . csrf_input() . '
                <div class="input-group">
                    <label for="nueva_contrasena">Nueva Contraseña</label>
                    <div class="password-field">
                        <input type="password" id="nueva_contrasena" name="nueva_contrasena" required minlength="8" maxlength="255" placeholder="Mínimo 8 caracteres" autocomplete="new-password">
                        <button type="button" class="password-toggle" aria-label="Mostrar contraseña" aria-pressed="false" data-password-toggle="nueva_contrasena">
                            <svg class="eye-open" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                            <svg class="eye-closed" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="m3 3 18 18"></path>
                                <path d="M10.6 10.7a2 2 0 0 0 2.7 2.7"></path>
                                <path d="M9.9 4.2A10.8 10.8 0 0 1 12 4c6.5 0 10 8 10 8a17.7 17.7 0 0 1-2 3.1"></path>
                                <path d="M6.6 6.6C3.5 8.7 2 12 2 12s3.5 8 10 8a9.8 9.8 0 0 0 4.1-.9"></path>
                            </svg>
                        </button>
                    </div>
                </div>
                <div class="input-group">
                    <label for="confirmar_contrasena">Confirmar Contraseña</label>
                    <div class="password-field">
                        <input type="password" id="confirmar_contrasena" name="confirmar_contrasena" required minlength="8" maxlength="255" placeholder="Repite la contraseña" autocomplete="new-password">
                        <button type="button" class="password-toggle" aria-label="Mostrar contraseña" aria-pressed="false" data-password-toggle="confirmar_contrasena">
                            <svg class="eye-open" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                            <svg class="eye-closed" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="m3 3 18 18"></path>
                                <path d="M10.6 10.7a2 2 0 0 0 2.7 2.7"></path>
                                <path d="M9.9 4.2A10.8 10.8 0 0 1 12 4c6.5 0 10 8 10 8a17.7 17.7 0 0 1-2 3.1"></path>
                                <path d="M6.6 6.6C3.5 8.7 2 12 2 12s3.5 8 10 8a9.8 9.8 0 0 0 4.1-.9"></path>
                            </svg>
                        </button>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary full-width">Guardar Contraseña</button>
                <script src="/dlgc_rrhh/assets/js/recover_password.js"></script>
            </form>
        </div>
    </main>
</body>
</html>';
}
