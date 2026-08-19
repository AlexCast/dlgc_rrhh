<?php

declare(strict_types=1);

require_once __DIR__ . '/StorageAdapterInterface.php';

/**
 * Adaptador para Bunny.net Storage (Edge Storage + Pull Zone).
 * Sube vía HTTP PUT a https://{storageHost}/{storageZoneName}/{destinationKey}
 * usando el AccessKey de la zona de almacenamiento (Storage API Password).
 * La lectura pública se sirve a través del hostname de la Pull Zone (CDN).
 */
class BunnyStorageAdapter implements StorageAdapterInterface
{
    private string $storageZoneName;
    private string $accessKey;
    private string $storageHost;
    private string $pullZoneHost;

    public function __construct(array $config)
    {
        $this->storageZoneName = (string) ($config['storage_zone_name'] ?? '');
        $this->accessKey       = (string) ($config['access_key'] ?? '');
        $this->storageHost     = (string) ($config['storage_host'] ?? 'storage.bunnycdn.com');
        $this->pullZoneHost    = (string) ($config['pull_zone_host'] ?? '');

        if ($this->storageZoneName === '' || $this->accessKey === '' || $this->pullZoneHost === '') {
            throw new RuntimeException('Configuración de Bunny.net incompleta. Revisa config/storage_config.php.');
        }
    }

    public function upload(string $localTmpPath, string $destinationKey, string $mimeType): array
    {
        if (!is_readable($localTmpPath)) {
            throw new RuntimeException('El archivo temporal no es legible.');
        }

        $destinationKey = ltrim($destinationKey, '/');
        $url = "https://{$this->storageHost}/{$this->storageZoneName}/{$destinationKey}";
        $fileContents = file_get_contents($localTmpPath);
        if ($fileContents === false) {
            throw new RuntimeException('No se pudo leer el archivo a subir.');
        }

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_CUSTOMREQUEST  => 'PUT',
            CURLOPT_POSTFIELDS     => $fileContents,
            CURLOPT_HTTPHEADER     => [
                'AccessKey: ' . $this->accessKey,
                'Content-Type: ' . $mimeType,
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 30,
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($response === false || $httpCode < 200 || $httpCode >= 300) {
            throw new RuntimeException('Error subiendo el archivo a Bunny.net: ' . ($curlError ?: "HTTP {$httpCode}"));
        }

        return [
            'storage_key' => $destinationKey,
            'url_publica' => $this->getPublicUrl($destinationKey),
        ];
    }

    public function delete(string $storageKey): bool
    {
        $storageKey = ltrim($storageKey, '/');
        $url = "https://{$this->storageHost}/{$this->storageZoneName}/{$storageKey}";

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_CUSTOMREQUEST  => 'DELETE',
            CURLOPT_HTTPHEADER     => ['AccessKey: ' . $this->accessKey],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 30,
        ]);
        curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // 404 se considera éxito: el objetivo (que el archivo no exista) ya se cumple.
        return $httpCode >= 200 && $httpCode < 300 || $httpCode === 404;
    }

    public function getPublicUrl(string $storageKey): string
    {
        $storageKey = ltrim($storageKey, '/');
        return "https://{$this->pullZoneHost}/{$storageKey}";
    }
}
