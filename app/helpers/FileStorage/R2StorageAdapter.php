<?php

declare(strict_types=1);

require_once __DIR__ . '/StorageAdapterInterface.php';
require_once __DIR__ . '/../../../vendor/autoload.php';

use Aws\S3\S3Client;
use Aws\S3\Exception\S3Exception;

/**
 * Adaptador para Cloudflare R2 (API compatible con S3).
 *
 * El bucket es PRIVADO: getPublicUrl() devuelve cadena vacía y los archivos se
 * sirven únicamente a través de proxies autenticados que usan download()
 * (app/foto_perfil.php y futuros).
 */
class R2StorageAdapter implements StorageAdapterInterface
{
    private S3Client $client;
    private string $bucket;

    public function __construct(array $config)
    {
        foreach (['account_id', 'access_key_id', 'secret_access_key', 'bucket'] as $clave) {
            if (empty($config[$clave])) {
                throw new RuntimeException(
                    "Configuración de R2 incompleta: falta '{$clave}'. Revisa config/storage_config.php."
                );
            }
        }

        $this->bucket = (string) $config['bucket'];

        $this->client = new S3Client([
            'region'                  => 'auto',
            'version'                 => 'latest',
            'endpoint'                => (string) ($config['endpoint'] ?? "https://{$config['account_id']}.r2.cloudflarestorage.com"),
            'credentials'             => [
                'key'    => (string) $config['access_key_id'],
                'secret' => (string) $config['secret_access_key'],
            ],
            // R2 exige estilo de ruta (bucket en la URL) y región 'auto'.
            'use_path_style_endpoint' => true,
        ]);
    }

    public function upload(string $localTmpPath, string $destinationKey, string $mimeType): array
    {
        if (!is_readable($localTmpPath)) {
            throw new RuntimeException('El archivo temporal no es legible.');
        }

        $destinationKey = ltrim($destinationKey, '/');

        try {
            $this->client->putObject([
                'Bucket'      => $this->bucket,
                'Key'         => $destinationKey,
                'SourceFile'  => $localTmpPath,
                'ContentType' => $mimeType,
            ]);
        } catch (Throwable $e) {
            throw new RuntimeException('Error subiendo el archivo a R2: ' . $e->getMessage(), 0, $e);
        }

        return [
            'storage_key' => $destinationKey,
            'url_publica' => '',
        ];
    }

    public function download(string $storageKey): string
    {
        $storageKey = ltrim($storageKey, '/');

        try {
            $resultado = $this->client->getObject([
                'Bucket' => $this->bucket,
                'Key'    => $storageKey,
            ]);
        } catch (S3Exception $e) {
            if ($e->getAwsErrorCode() === 'NoSuchKey') {
                throw new RuntimeException('El objeto no existe en R2.', 0, $e);
            }
            throw new RuntimeException('Error descargando el archivo de R2: ' . $e->getMessage(), 0, $e);
        } catch (Throwable $e) {
            throw new RuntimeException('Error descargando el archivo de R2: ' . $e->getMessage(), 0, $e);
        }

        return (string) $resultado['Body'];
    }

    public function delete(string $storageKey): bool
    {
        try {
            $this->client->deleteObject([
                'Bucket' => $this->bucket,
                'Key'    => ltrim($storageKey, '/'),
            ]);
            return true;
        } catch (Throwable $e) {
            error_log('R2StorageAdapter: no se pudo eliminar ' . $storageKey . ': ' . $e->getMessage());
            return false;
        }
    }

    public function getPublicUrl(string $storageKey): string
    {
        // Bucket privado: no hay URL pública; los archivos se sirven vía proxy autenticado.
        return '';
    }

    public function getProviderName(): string
    {
        return 'R2';
    }
}
