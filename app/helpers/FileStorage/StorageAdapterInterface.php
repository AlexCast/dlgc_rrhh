<?php

declare(strict_types=1);

/**
 * Contrato común para cualquier proveedor externo de almacenamiento de objetos
 * (Cloudflare R2, ...). Ningún archivo se guarda en la DB ni dentro del proyecto:
 * solo se persiste la referencia devuelta por upload().
 */
interface StorageAdapterInterface
{
    /**
     * Sube un archivo local temporal al proveedor externo.
     *
     * @param string $localTmpPath  Ruta local temporal (ej. $_FILES[...]['tmp_name']).
     * @param string $destinationKey Ruta/clave lógica destino (ej. "permisos/2026/07/uuid.pdf").
     * @param string $mimeType      Mime type validado (image/jpeg, image/png, application/pdf).
     * @return array{storage_key: string, url_publica: ?string} Referencia a persistir en t_permisos_evidencias.
     * @throws RuntimeException Si la subida falla.
     */
    public function upload(string $localTmpPath, string $destinationKey, string $mimeType): array;

    /**
     * Descarga el contenido completo de un objeto. Pensado solo para objetos
     * pequeños (fotos de perfil comprimidas): el contenido se devuelve en memoria.
     * Los proxies autenticados (app/foto_perfil.php) usan este método para servir
     * archivos de buckets privados.
     *
     * @throws RuntimeException Si el objeto no existe o la descarga falla.
     */
    public function download(string $storageKey): string;

    /**
     * Elimina un archivo previamente subido. Debe ser idempotente (no lanzar error si ya no existe).
     */
    public function delete(string $storageKey): bool;

    /**
     * Devuelve la URL pública/firmada para mostrar o descargar el archivo.
     * Con bucket privado no existe URL pública: se devuelve cadena vacía.
     */
    public function getPublicUrl(string $storageKey): string;

    /**
     * Nombre del proveedor a persistir en la base de datos (ej. 'R2', 'NULL').
     */
    public function getProviderName(): string;
}
