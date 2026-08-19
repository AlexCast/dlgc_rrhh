<?php
declare(strict_types=1);

/**
 * CSRF protection helpers.
 *
 * Provides token generation/validation for state-changing requests.
 * All pages/forms protected by auth_guard.php can rely on the active session.
 */

const CSRF_TOKEN_KEY = 'csrf_token';
const CSRF_TOKEN_LENGTH_BYTES = 32;

/**
 * Ensures a CSRF token exists in $_SESSION and returns it.
 * Regenerates only if missing.
 */
function csrf_get_token(): string
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        throw new RuntimeException('La sesión debe estar activa para generar un token CSRF.');
    }

    if (empty($_SESSION[CSRF_TOKEN_KEY])) {
        $_SESSION[CSRF_TOKEN_KEY] = bin2hex(random_bytes(CSRF_TOKEN_LENGTH_BYTES));
    }

    return $_SESSION[CSRF_TOKEN_KEY];
}

/**
 * Returns the hidden input HTML for the CSRF token.
 */
function csrf_input(): string
{
    $token = csrf_get_token();
    return '<input type="hidden" name="' . CSRF_TOKEN_KEY . '" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
}

/**
 * Validates the CSRF token sent via POST.
 *
 * @param bool $exitOnFailure If true, terminates the request with a 403 response.
 */
function csrf_validate(bool $exitOnFailure = true): bool
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        if ($exitOnFailure) {
            csrf_fail('Sesión no activa.');
        }
        return false;
    }

    $sessionToken = $_SESSION[CSRF_TOKEN_KEY] ?? '';
    $postToken    = $_POST[CSRF_TOKEN_KEY] ?? '';

    // DEBUG: log tokens only when they differ (remove after diagnosis)
    if (!hash_equals($sessionToken, $postToken)) {
        error_log(sprintf(
            'CSRF mismatch: session=%s post=%s ip=%s uri=%s',
            is_string($sessionToken) ? substr($sessionToken, 0, 8) : gettype($sessionToken),
            is_string($postToken) ? substr($postToken, 0, 8) : gettype($postToken),
            $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            $_SERVER['REQUEST_URI'] ?? 'unknown'
        ));
    }

    if (!is_string($sessionToken) || $sessionToken === '' ||
        !is_string($postToken)    || $postToken === '') {
        if ($exitOnFailure) {
            csrf_fail('Token ausente o sesión inválida.');
        }
        return false;
    }

    if (!hash_equals($sessionToken, $postToken)) {
        if ($exitOnFailure) {
            csrf_fail('Token enviado no coincide con la sesión.');
        }
        return false;
    }

    return true;
}

/**
 * Regenerates the CSRF token. Useful after privilege escalation (login).
 */
function csrf_regenerate_token(): string
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        throw new RuntimeException('La sesión debe estar activa para regenerar el token CSRF.');
    }

    $_SESSION[CSRF_TOKEN_KEY] = bin2hex(random_bytes(CSRF_TOKEN_LENGTH_BYTES));
    return $_SESSION[CSRF_TOKEN_KEY];
}

/**
 * Terminates the request with a 403 CSRF error.
 */
function csrf_fail(string $detail = ''): void
{
    http_response_code(403);
    header('Content-Type: text/html; charset=utf-8');
    $message = 'Solicitud rechazada: token de seguridad CSRF inválido o ausente.';
    if ($detail !== '' && (bool) ini_get('display_errors')) {
        $message .= ' ' . $detail;
    }
    exit($message);
}
