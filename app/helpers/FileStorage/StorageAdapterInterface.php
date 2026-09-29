<?php

declare(strict_types=1);

/**
 * Contrato común para cualquier proveedor externo de almacenamiento de evidencias
 * (Bunny.net, Cloudflare R2, Backblaze B2, ...). Ningún archivo se guarda en la DB
 * ni dentro del proyecto: solo se persiste la referencia devuelta por upload().
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
     * Elimina un archivo previamente subido. Debe ser idempotente (no lanzar error si ya no existe).
     */
    public function delete(string $storageKey): bool;

    /**
     * Devuelve la URL pública/firmada para mostrar o descargar el archivo.
     */
    public function getPublicUrl(string $storageKey): string;
}
