<?php

declare(strict_types=1);

/**
 * Autoloader PSR-4 mínimo para el módulo de correo.
 *
 * Mapea el namespace App\Mail\ a este directorio (app/mail/) para no
 * depender de composer a la hora de cargar los adaptadores y servicios
 * de envío. Es idempotente: registra el autoloader una sola vez.
 */
spl_autoload_register(static function (string $class): void {
    $prefix = 'App\\Mail\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }

    $relative = substr($class, strlen($prefix));
    $file = __DIR__ . '/' . str_replace('\\', '/', $relative) . '.php';

    if (is_file($file)) {
        require_once $file;
    }
});
