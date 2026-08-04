<?php

declare(strict_types=1);

require_once __DIR__ . '/StorageAdapterInterface.php';
require_once __DIR__ . '/BunnyStorageAdapter.php';
require_once __DIR__ . '/NullStorageAdapter.php';

/**
 * Crea el adaptador de almacenamiento configurado en config/storage_config.php,
 * a partir del valor 'provider' (BUNNY | R2 | B2 | CLOUDFLARE_IMAGES, igual que
 * el CHECK de t_permisos_evidencias.proveedor). Punto único para cambiar de
 * proveedor sin tocar el resto de la aplicación.
 */
class StorageFactory
{
    public static function create(): StorageAdapterInterface
    {
        $configPath = __DIR__ . '/../../../config/storage_config.php';
        if (!is_file($configPath)) {
            throw new RuntimeException(
                'No existe config/storage_config.php. Copia config/storage_config.php.example y completa las credenciales.'
            );
        }

        $config = require $configPath;
        $provider = strtoupper((string) ($config['provider'] ?? ''));

        switch ($provider) {
            case 'BUNNY':
                return new BunnyStorageAdapter($config['bunny'] ?? []);
            case 'NULL':
                return new NullStorageAdapter();
            default:
                throw new RuntimeException("Proveedor de almacenamiento '{$provider}' no soportado todavía.");
        }
    }
}
