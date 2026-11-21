<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/session_bootstrap.php';
require_once __DIR__ . '/../app/csrf_guard.php';
require_once __DIR__ . '/../app/helpers/TurnstileHelper.php';

$turnstileSiteKey = TurnstileHelper::siteKey();
$status = $_GET['status'] ?? '';
$code = $_GET['code'] ?? '';
$msg = $_GET['msg'] ?? '';

$messages = [
    'metodo_no_valido' => 'Solicitud no válida. Usa el formulario para continuar.',
    'reenvio_datos_invalidos' => 'Ingresa un usuario o correo válido.',
    'reenvio_sin_pendiente' => 'No encontramos una activación pendiente con esos datos.',
    'demasiados_intentos' => 'Has enviado demasiadas solicitudes. Inténtalo de nuevo más tarde.',
    'reenvio_fallo' => 'No pudimos enviar el correo en este momento. Inténtalo más tarde.',
    'turnstile_invalido' => 'No pudimos verificar que no eres un robot. Resuelve el captcha e inténtalo de nuevo.',
    'reenvio_enviado' => 'Correo de verificación reenviado. Revisa tu bandeja de entrada y la carpeta de spam.',
    'error_servidor' => 'Ocurrió un error del servidor. Inténtalo más tarde.',
];
$feedback = trim((string) $msg) !== '' ? (string) $msg : ($messages[$code] ?? '');
$feedbackType = $status === 'success' ? 'success' : 'error';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reenviar correo de verificación | Portal DLGC</title>
    <link rel="stylesheet" href="/assets/css/login.css?v=<?php echo filemtime(__DIR__ . '/../assets/css/login.css'); ?>">
    <link rel="stylesheet" href="/assets/css/recover_password.css?v=<?php echo filemtime(__DIR__ . '/../assets/css/recover_password.css'); ?>">
    <link rel="icon" type="image/png" sizes="32x32" href="/assets/img/favicon.ico">
<?php if ($turnstileSiteKey !== ''): ?>
    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" defer></script>
<?php endif; ?>
</head>
<body>
    <canvas id="interactive-grid" aria-hidden="true"></canvas>
    <div class="custom-cursor-dot" aria-hidden="true"></div>
    <div class="custom-cursor-outline" aria-hidden="true"></div>

    <header class="navbar-wrapper">
        <nav class="navbar auth-navbar" aria-label="Navegación de verificación">
            <a href="/templates/index.html" class="logo" aria-label="Ir al inicio">
                <img src="/assets/img/logo1.png" alt="Distribuciones La Gran Cacharrería">
            </a>
            <div class="nav-actions">
                <a href="/templates/login.php?tab=register" class="btn btn-outline back-btn" aria-label="Volver a activar cuenta">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M15 18l-6-6 6-6"></path>
                    </svg>
                    <span>Volver</span>
                </a>
                <button id="theme-toggle" class="icon-btn" type="button" aria-label="Cambiar modo oscuro/claro">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
                    </svg>
                </button>
            </div>
        </nav>
    </header>

    <main class="auth-wrapper resend-wrapper">
        <section class="auth-card resend-card fade-in-up" aria-labelledby="resend-title">
            <div class="resend-icon" aria-hidden="true">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="5" width="18" height="14" rx="2"></rect>
                    <path d="m3 7 9 6 9-6"></path>
                </svg>
            </div>
            <div class="auth-header-text resend-header">
                <h1 id="resend-title">Reenviar correo de verificación</h1>
                <p>Indica el usuario o correo asociado a tu activación pendiente.</p>
            </div>

            <?php if ($feedback !== ''): ?>
                <div class="auth-feedback <?php echo $feedbackType; ?> visible" role="status" aria-live="polite">
                    <?php echo htmlspecialchars($feedback, ENT_QUOTES, 'UTF-8'); ?>
                </div>
            <?php endif; ?>

            <form class="auth-form active resend-form" action="/app/reenviar_verificacion.php" method="POST">
                <?php echo csrf_input(); ?>
                <div class="input-group">
                    <label for="resend-ident">Usuario o Correo Electrónico</label>
                    <input type="text" id="resend-ident" name="identificador" required minlength="3" maxlength="40" placeholder="usuario o ejemplo@empresa.com" autocomplete="username" autocapitalize="off" autofocus value="<?php echo htmlspecialchars($_GET['identificador'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                </div>

                <?php if ($turnstileSiteKey !== ''): ?>
                    <div class="cf-turnstile" data-sitekey="<?php echo htmlspecialchars($turnstileSiteKey, ENT_QUOTES, 'UTF-8'); ?>" data-action="resend"></div>
                <?php endif; ?>

                <button type="submit" class="btn btn-primary full-width">Reenviar correo</button>
            </form>
        </section>
    </main>

    <script src="/assets/js/theme.js?v=<?php echo filemtime(__DIR__ . '/../assets/js/theme.js'); ?>"></script>
    <script src="/assets/js/script.js?v=<?php echo filemtime(__DIR__ . '/../assets/js/script.js'); ?>"></script>
</body>
</html>
