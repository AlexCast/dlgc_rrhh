<?php

declare(strict_types=1);

require_once __DIR__ . '/StorageAdapterInterface.php';

/**
 * Adaptador nulo para desarrollo/local.
 * No sube archivos a ningún proveedor externo; solo genera claves y URLs simuladas.
 * Útil para probar el flujo de solicitudes de permiso sin credenciales de R2.
 */
class NullStorageAdapter implements StorageAdapterInterface
{
    public function __construct(array $config = [])
    {
        // No requiere configuración.
    }

    public function upload(string $localTmpPath, string $destinationKey, string $mimeType): array
    {
        return [
            'storage_key' => ltrim($destinationKey, '/'),
            'url_publica' => '',
        ];
    }

    public function download(string $storageKey): string
    {
        throw new RuntimeException('El adaptador NULL no almacena archivos reales.');
    }

    public function delete(string $storageKey): bool
    {
        return true;
    }

    public function getPublicUrl(string $storageKey): string
    {
        return '';
    }

    public function getProviderName(): string
    {
        return 'NULL';
    }
}
