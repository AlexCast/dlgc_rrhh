<?php

declare(strict_types=1);

/**
 * EvidenceValidator.php
 * Valida archivos de evidencia (jpg/png/pdf, máx 10MB) antes de subirlos al
 * proveedor externo. Refleja las mismas reglas del CHECK de t_permisos_evidencias
 * para fallar rápido en el servidor sin gastar una llamada al proveedor externo.
 */
class EvidenceValidator
{
    private const MAX_BYTES = 10 * 1024 * 1024; // 10MB, igual al CHECK de t_permisos_evidencias.tamano_bytes

    private const ALLOWED_MIME_EXT = [
        'image/jpeg'      => ['jpg', 'jpeg'],
        'image/png'       => ['png'],
        'application/pdf' => ['pdf'],
    ];

    /**
     * @param array $file Entrada individual de $_FILES (name, tmp_name, size, error, type).
     * @return string[] Lista de errores; vacía si el archivo es válido.
     */
    public static function validate(array $file): array
    {
        $errores = [];

        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            $errores[] = 'Ocurrió un error al recibir el archivo.';
            return $errores;
        }

        $size = (int) ($file['size'] ?? 0);
        if ($size <= 0 || $size > self::MAX_BYTES) {
            $errores[] = 'El archivo debe pesar entre 1 byte y 10MB.';
        }

        $tmpName = (string) ($file['tmp_name'] ?? '');
        $mimeType = $tmpName !== '' && is_readable($tmpName)
            ? (string) (finfo_file(finfo_open(FILEINFO_MIME_TYPE), $tmpName) ?: '')
            : '';

        if (!isset(self::ALLOWED_MIME_EXT[$mimeType])) {
            $errores[] = 'Solo se permiten archivos JPG, PNG o PDF.';
            return $errores;
        }

        $extension = strtolower((string) pathinfo((string) ($file['name'] ?? ''), PATHINFO_EXTENSION));
        if (!in_array($extension, self::ALLOWED_MIME_EXT[$mimeType], true)) {
            $errores[] = 'La extensión del archivo no coincide con su contenido real.';
        }

        return $errores;
    }

    public static function detectMimeType(string $tmpName): string
    {
        return (string) (finfo_file(finfo_open(FILEINFO_MIME_TYPE), $tmpName) ?: '');
    }

    public static function buildStorageKey(string $idEmpleado, string $extension): string
    {
        $fecha = date('Y/m');
        $uuid = bin2hex(random_bytes(16));
        return "permisos/{$fecha}/{$idEmpleado}_{$uuid}." . strtolower($extension);
    }
}
