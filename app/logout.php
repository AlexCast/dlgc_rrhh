<?php
// app/logout.php
declare(strict_types=1);

require_once __DIR__ . '/session_bootstrap.php';
require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/helpers/CookieHelper.php';
require_once __DIR__ . '/helpers/RememberMeHelper.php';

// 1. Vaciar el arreglo de sesión
$_SESSION = [];

// 2. Invalidar token "Recordarme" si existe.
RememberMeHelper::invalidateCurrent($conexion);

// 3. Destruir la cookie de sesión en el navegador respetando SameSite/HttpOnly/Secure
CookieHelper::deleteSessionCookie();

// 4. Destruir la sesión en el servidor
session_destroy();

// 4. Redirigir al login
header("Location: ../templates/login.php");
exit;