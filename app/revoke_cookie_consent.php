<?php
declare(strict_types=1);

/**
 * Endpoint para revocar el consentimiento de cookies opcionales.
 *
 * Limpia la preferencia de localStorage no puede hacerse desde el servidor,
 * pero esta URL borra la cookie técnica e invalida el token "Recordarme".
 *
 * El frontend debe llamar a este endpoint y luego limpiar localStorage.
 */

require_once __DIR__ . '/session_bootstrap.php';
require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/auth_guard.php';
require_once __DIR__ . '/helpers/CookieHelper.php';
require_once __DIR__ . '/helpers/RememberMeHelper.php';

const CONSENT_COOKIE_NAME = 'dlgc_cookie_consent';

// Borrar cookie de consentimiento.
CookieHelper::delete(CONSENT_COOKIE_NAME, ['path' => '/']);

// Invalidar token remember-me si existe.
RememberMeHelper::invalidateCurrent($conexion);

// Redirigir al login (la sesión PHP se mantiene, pero "Recordarme" ya no reaparecerá).
header('Location: /dlgc_rrhh/templates/login.php');
exit;
