<?php
/**
 * Script de mantenimiento para limpiar tokens "Recordarme" expirados.
 *
 * Uso:
 *   php scripts/cleanup_remember_tokens.php
 *
 * Se puede programar como tarea programada (cron en Linux, Task Scheduler en Windows)
 * para ejecutarse diariamente.
 */

require_once __DIR__ . '/../app/conexion.php';
require_once __DIR__ . '/../app/helpers/RememberMeHelper.php';

RememberMeHelper::pruneExpired($conexion);

echo "Limpieza de tokens 'Recordarme' completada." . PHP_EOL;
