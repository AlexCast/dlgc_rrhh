<?php
/**
 * login.php
 * Autenticación de usuarios contra t_usuarios, con verificación de hash,
 * regeneración de sesión y carga de permisos por módulo.
 */

declare(strict_types=1);

require_once __DIR__ . '/session_bootstrap.php';
require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/csrf_guard.php';
require_once __DIR__ . '/helpers/InputSanitizer.php';
require_once __DIR__ . '/helpers/SessionUserLoader.php';
require_once __DIR__ . '/helpers/RememberMeHelper.php';
require_once __DIR__ . '/helpers/RateLimiter.php';
require_once __DIR__ . '/helpers/TurnstileHelper.php';

$urlLogin = '/templates/login.php';

function redirigirLogin(array $parametros): void
{
    global $urlLogin;
    header('Location: ' . $urlLogin . '?' . http_build_query($parametros));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirigirLogin([
        'tab' => 'login',
        'status' => 'error',
        'code' => 'metodo_no_valido',
    ]);
}

// Validate CSRF token for login.
csrf_validate();

// --- 0. Verificación anti-bot (Cloudflare Turnstile) antes de tocar credenciales ---
if (!TurnstileHelper::verify($_POST['cf-turnstile-response'] ?? null, 'login')) {
    redirigirLogin([
        'tab'    => 'login',
        'status' => 'error',
        'code'   => 'turnstile_invalido',
        'user'   => InputSanitizer::loginIdentifier($_POST['user'] ?? ''),
    ]);
}

// --- 1. Recepción y normalización de credenciales enviadas por POST ---
$userInput  = InputSanitizer::loginIdentifier($_POST['user'] ?? '');
$contrasena = $_POST['contrasena'] ?? '';

if ($userInput === '' || $contrasena === '') {
    redirigirLogin([
        'tab' => 'login',
        'status' => 'error',
        'code' => 'campos_obligatorios',
    ]);
}

if (strlen($userInput) > 40 || strlen($contrasena) > 255) {
    redirigirLogin([
        'tab' => 'login',
        'status' => 'error',
        'code' => 'credenciales_invalidas',
    ]);
}

// --- 2. Búsqueda del usuario mediante el procedimiento proc_login_usuarios ---
// (acepta tanto username como correo, el procedimiento resuelve la coincidencia)
try {
    $sentencia = $conexion->prepare('CALL proc_login_usuarios(?)');
    $sentencia->execute([$userInput]);
    $usuario = $sentencia->fetch();
    $sentencia->closeCursor();

    // --- 2b. Rate limiting de intentos fallidos (solo si la cuenta existe). ---
    // Un fallo del rate limiter no debe ocultar el resultado real del login.
    $rateLimiter  = null;
    $rateCheck    = ['permitido' => true, 'minutos' => null];
    if ($usuario) {
        try {
            $rateLimiter = new RateLimiter($conexion);
            $rateCheck = $rateLimiter->verificar((string) $usuario['id_usuario'], 'login');
        } catch (PDOException $e) {
            error_log('Error al verificar el rate limit de login: ' . $e->getMessage());
        }

        if (!$rateCheck['permitido']) {
            error_log(sprintf(
                'Login bloqueado por rate limit: usuario=%s ip=%s',
                $userInput,
                $_SERVER['REMOTE_ADDR'] ?? 'desconocida'
            ));
            redirigirLogin([
                'tab' => 'login',
                'status' => 'error',
                'code' => 'demasiados_intentos',
                'msg' => 'Demasiados intentos fallidos. Inténtalo de nuevo en ' . $rateCheck['minutos'] . ' minuto(s).',
                'user' => $userInput,
            ]);
        }
    }

    // --- 3. Verificación del hash de la contraseña ---
    if (!$usuario || !password_verify($contrasena, $usuario['contrasena'])) {
        if ($usuario && $rateLimiter !== null) {
            try {
                $rateLimiter->registrar((string) $usuario['id_usuario'], 'login');
            } catch (PDOException $e) {
                error_log('Error al registrar el intento fallido de login: ' . $e->getMessage());
            }
        }
        error_log(sprintf(
            'Intento de login fallido: usuario=%s ip=%s cuenta_existe=%s',
            $userInput,
            $_SERVER['REMOTE_ADDR'] ?? 'desconocida',
            $usuario ? 'si' : 'no'
        ));
        redirigirLogin([
            'tab' => 'login',
            'status' => 'error',
            'code' => 'credenciales_invalidas',
            'user' => $userInput,
        ]);
    }

    // --- 3b. Verificación de correo electrónico ---
    if (empty($usuario['correo_verificado'])) {
        redirigirLogin([
            'tab' => 'login',
            'status' => 'error',
            'code' => 'correo_no_verificado',
            'user' => $userInput,
        ]);
    }

    // --- 4. Prevención de fijación de sesión ---
    session_regenerate_id(true);

    // --- 4b. Regenerar token CSRF tras autenticación exitosa ---
    csrf_regenerate_token();

    // --- 5. Cargar datos del usuario y permisos en sesión ---
    if (!load_user_session($conexion, (string) $usuario['id_usuario'])) {
        error_log('No se pudo cargar la sesión del usuario: ' . $usuario['id_usuario']);
        redirigirLogin([
            'tab' => 'login',
            'status' => 'error',
            'code' => 'error_servidor',
        ]);
    }

    // --- 6. Cookie "Recordarme" (solo si el usuario la solicitó Y aceptó cookies opcionales) ---
    $cookieConsent = $_POST['cookie_consent'] ?? 'necessary';
    $rememberMe    = filter_input(INPUT_POST, 'remember_me', FILTER_VALIDATE_BOOL) ?? false;

    if ($rememberMe && $cookieConsent === 'all') {
        RememberMeHelper::create($conexion, (string) $usuario['id_usuario']);
    }

    // --- 7. Redirección al dashboard tras login exitoso ---
    // Dashboard único: las métricas visibles dependen de los módulos del usuario.
    $dashboardUrl = '/templates/firstpage.php';

    header('Location: ' . $dashboardUrl);
    exit;

} catch (PDOException $e) {
    error_log('Error en el proceso de login: ' . $e->getMessage());
    redirigirLogin([
        'tab' => 'login',
        'status' => 'error',
        'code' => 'error_servidor',
    ]);
}