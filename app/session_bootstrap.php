<?php
declare(strict_types=1);

/**
 * Bootstrap centralizado de sesión segura.
 *
 * Configura todas las banderas de seguridad de la cookie de sesión ANTES de
 * llamar session_start(). Es idempotente: puede incluirse varias veces sin error.
 *
 * Uso:
 *   require_once __DIR__ . '/session_bootstrap.php';
 */

if (session_status() === PHP_SESSION_ACTIVE) {
    return;
}

$is_https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';

// Forzar que el ID de sesión venga de la cookie y nunca de la URL.
ini_set('session.use_strict_mode', '1');
ini_set('session.use_only_cookies', '1');
ini_set('session.use_trans_sid', '0');

// Cookie de sesión: HttpOnly, Secure bajo HTTPS, SameSite=Lax, sesión del navegador.
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_secure', $is_https ? '1' : '0');
ini_set('session.cookie_samesite', 'Lax');
ini_set('session.cookie_lifetime', '0');
ini_set('session.cookie_path', '/');
ini_set('session.cookie_domain', '');

// Vida máxima de la sesión en el servidor: 30 minutos de inactividad.
ini_set('session.gc_maxlifetime', '1800');

session_start();
