<?php
declare(strict_types=1);

/**
 * Authentication and authorization guard for protected views.
 *
 * Usage:
 * require_once __DIR__ . '/auth_guard.php';
 */

require_once __DIR__ . '/helpers/CookieHelper.php';
require_once __DIR__ . '/helpers/RememberMeHelper.php';

/**
 * Ends the current session safely and redirects to login.
 */
function terminate_session_and_redirect(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        $_SESSION = [];
        CookieHelper::deleteSessionCookie();
        session_destroy();
    }

    header('Location: ../templates/login.php', true, 302);
    exit;
}

// Iniciar sesión con configuración segura centralizada.
require_once __DIR__ . '/session_bootstrap.php';

// Conexión y utilidades para reautenticación vía "Recordarme".
require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/helpers/SessionUserLoader.php';

// Deshabilitar la caché del navegador para prevenir el botón "Atrás".
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Make CSRF helpers available to every protected page.
require_once __DIR__ . '/csrf_guard.php';



// Base authentication check.
if (empty($_SESSION['user_id'])) {
    // Intentar reautenticación mediante cookie "Recordarme".
    $rememberedUserId = RememberMeHelper::validate($conexion);

    if ($rememberedUserId !== null && load_user_session($conexion, $rememberedUserId)) {
        // Regenerar ID de sesión para mitigar fijación de sesión al reautenticar.
        session_regenerate_id(true);
        csrf_regenerate_token();

        // Propagar usuario a PostgreSQL para auditorías de esta misma petición.
        $actorAuditoria = $_SESSION['nombre_completo'] ?? $_SESSION['username'] ?? '';
        $actorAuditoria = trim((string) $actorAuditoria);
        if ($actorAuditoria !== '') {
            try {
                $sentenciaAuditoria = $conexion->prepare("SELECT set_config('app.current_user', ?, false)");
                $sentenciaAuditoria->execute([$actorAuditoria]);
            } catch (PDOException $eAuditoria) {
                error_log('Error al configurar app.current_user tras remember-me: ' . $eAuditoria->getMessage());
            }
        }
    } else {
        terminate_session_and_redirect();
    }
}

// Session hijacking prevention using exact user-agent matching.
$current_user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
$login_user_agent = $_SESSION['login_user_agent'] ?? null;

if (!is_string($login_user_agent) || hash_equals($login_user_agent, $current_user_agent) === false) {
    terminate_session_and_redirect();
}

// Automatic logout after 30 minutes of inactivity.
$inactivity_timeout_seconds = 30 * 60;
$current_timestamp = time();
$last_activity_timestamp = $_SESSION['last_activity_timestamp'] ?? null;

if (!is_int($last_activity_timestamp)) {
    terminate_session_and_redirect();
}

if (($current_timestamp - $last_activity_timestamp) > $inactivity_timeout_seconds) {
    terminate_session_and_redirect();
}

$_SESSION['last_activity_timestamp'] = $current_timestamp;

/**
 * Estructura real guardada por app/login.php en $_SESSION['permisos'], indexada por id_modulo:
 * [
 *   2 => [
 *     'nombre_modulo'      => 'Empleados',
 *     'tipo_modulo'        => 'RRHH',
 *     'permiso_crear'       => true,
 *     'permiso_actualizar'  => false,
 *     'permiso_eliminar'    => false,
 *     'permiso_restaurar'   => false,
 *   ],
 * ]
 *
 * La existencia de la clave $module_id en este arreglo ES el permiso de visualización
 * del módulo (no existe una columna permiso_ver independiente).
 */
const MODULE_ACTION_TO_PERMISSION_KEY = [
    'crear' => 'permiso_crear',
    'actualizar' => 'permiso_actualizar',
    'eliminar' => 'permiso_eliminar',
    'restaurar' => 'permiso_restaurar',
];

/**
 * Devuelve el arreglo de permisos por módulo de la sesión activa, o null si no existe.
 */
function get_session_module_permissions(): ?array
{
    $permissions = $_SESSION['permisos'] ?? null;
    return is_array($permissions) ? $permissions : null;
}

/**
 * Verifica si el usuario tiene acceso (visualización) a un módulo: basta con que exista
 * la fila id_usuario/id_modulo en t_usuarios_modulos, reflejada en la sesión.
 */
function has_module_access($module_id): bool
{
    if (!is_scalar($module_id)) {
        return false;
    }

    $permissions = get_session_module_permissions();
    if ($permissions === null) {
        return false;
    }

    return array_key_exists((string) $module_id, $permissions);
}

/**
 * Verifica si el usuario tiene un permiso de acción concreto ('crear', 'actualizar',
 * 'eliminar' o 'restaurar') dentro de un módulo al que ya tiene acceso.
 */
function has_module_permission($module_id, $action_type): bool
{
    if (!is_scalar($module_id) || !is_string($action_type)) {
        return false;
    }

    $permissions = get_session_module_permissions();
    if ($permissions === null) {
        return false;
    }

    $normalized_module_id = (string) $module_id;
    if (!array_key_exists($normalized_module_id, $permissions) || !is_array($permissions[$normalized_module_id])) {
        return false;
    }

    $normalized_action_type = strtolower(trim($action_type));
    $permission_key = MODULE_ACTION_TO_PERMISSION_KEY[$normalized_action_type] ?? null;

    if ($permission_key === null || !array_key_exists($permission_key, $permissions[$normalized_module_id])) {
        return false;
    }

    return (bool) $permissions[$normalized_module_id][$permission_key];
}

/**
 * Corta la ejecución y redirige a access_denied.php si el usuario no tiene acceso al módulo.
 * Debe llamarse al inicio de cada plantilla protegida, justo después de auth_guard.php.
 */
function require_module_access($module_id): void
{
    if (!has_module_access($module_id)) {
        header('Location: /dlgc_rrhh/templates/access_denied.php', true, 302);
        exit;
    }
}

/**
 * Devuelve la URL del dashboard único de la aplicación.
 */
function get_dashboard_url(): string
{
    return '/dlgc_rrhh/templates/firstpage.php';
}
