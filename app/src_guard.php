<?php
declare(strict_types=1);

/**
 * Guard reutilizable para los módulos administrativos de src/.
 *
 * Uso típico al inicio de cada archivo del CRUD:
 *
 *   $moduleId = 8;                     // ID del módulo en t_modulos
 *   $requiredAction = 'crear';          // 'crear' | 'actualizar' | 'eliminar' | 'restaurar' (opcional)
 *   require_once __DIR__ . '/src_guard.php';
 *   require_once __DIR__ . '/conexion.php';
 */

require_once __DIR__ . '/auth_guard.php';

if (!isset($moduleId) || !is_int($moduleId) || $moduleId <= 0) {
    http_response_code(500);
    die('Configuración de módulo inválida.');
}

require_module_access($moduleId);

if (!empty($requiredAction) && is_string($requiredAction)) {
    if (!has_module_permission($moduleId, $requiredAction)) {
        header('Location: /dlgc_rrhh/templates/access_denied.php', true, 302);
        exit;
    }
}
