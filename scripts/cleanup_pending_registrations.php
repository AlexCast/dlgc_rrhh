<?php
/**
 * cleanup_pending_registrations.php
 * Limpia registros pendientes de verificación expirados.
 * Ejecutar periódicamente vía cron en producción (Linux/Ubuntu).
 *
 * Ejemplo de cron cada hora:
 *   0 * * * * /usr/bin/php /var/www/html/dlgc_rrhh/scripts/cleanup_pending_registrations.php >> /var/log/dlgc_cleanup.log 2>&1
 */

declare(strict_types=1);

require_once __DIR__ . '/../app/conexion.php';

try {
    $sentencia = $conexion->prepare(
        "DELETE FROM t_registros_pendientes
         WHERE fecha_expiracion < CURRENT_TIMESTAMP
            OR fec_delete IS NOT NULL"
    );
    $sentencia->execute();
    $eliminados = $sentencia->rowCount();

    echo date('Y-m-d H:i:s') . " - Registros pendientes eliminados: {$eliminados}" . PHP_EOL;
} catch (PDOException $e) {
    error_log('Error limpiando registros pendientes: ' . $e->getMessage());
    exit(1);
}
