<?php

declare(strict_types=1);

/**
 * ImageCompressor.php
 * Comprime imágenes de perfil con GD (extensión nativa de PHP, ya instalada):
 * redimensiona a un máximo de 512x512 px preservando la proporción y re-codifica
 * a JPEG calidad 80. Si el PNG original tiene canal alpha (transparencia) se
 * conserva como PNG con compresión máxima; el resto se convierte a JPEG.
 */
class ImageCompressor
{
    public const MAX_DIMENSION = 512;
    public const JPEG_QUALITY = 80;
    // Alineado con upload_max_filesize del servidor (2M).
    public const MAX_BYTES = 2 * 1024 * 1024;

    private const ALLOWED_MIME = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
    ];

    /**
     * Comprime la imagen en $rutaOrigen y escribe el resultado en $destinoTmp.
     *
     * @param string $rutaOrigen Ruta del archivo temporal subido ($_FILES[...]['tmp_name']).
     * @param string $destinoTmp Ruta destino donde se escribirá la imagen comprimida.
     * @return array{mime: string, extension: string} MIME y extensión de la imagen resultante.
     * @throws RuntimeException Si el archivo no es una imagen válida o el procesado falla.
     */
    public static function compress(string $rutaOrigen, string $destinoTmp): array
    {
        if (!is_readable($rutaOrigen)) {
            throw new RuntimeException('La imagen recibida no es legible.');
        }

        $tamano = (int) filesize($rutaOrigen);
        if ($tamano <= 0 || $tamano > self::MAX_BYTES) {
            throw new RuntimeException('La imagen debe pesar entre 1 byte y 2 MB.');
        }

        // MIME real detectado del contenido, nunca del nombre o del navegador.
        $mimeReal = (string) (finfo_file(finfo_open(FILEINFO_MIME_TYPE), $rutaOrigen) ?: '');
        if (!isset(self::ALLOWED_MIME[$mimeReal])) {
            throw new RuntimeException('Solo se permiten imágenes JPG, PNG o WebP.');
        }

        $imagen = $mimeReal === 'image/jpeg'
            ? @imagecreatefromjpeg($rutaOrigen)
            : ($mimeReal === 'image/webp' ? @imagecreatefromwebp($rutaOrigen) : @imagecreatefrompng($rutaOrigen));

        if ($imagen === false) {
            throw new RuntimeException('La imagen está dañada o no se pudo leer.');
        }

        $anchoOriginal = imagesx($imagen);
        $altoOriginal = imagesy($imagen);

        $escala = min(1.0, self::MAX_DIMENSION / max($anchoOriginal, $altoOriginal));
        $anchoNuevo = max(1, (int) round($anchoOriginal * $escala));
        $altoNuevo = max(1, (int) round($altoOriginal * $escala));

        $redimensionada = imagecreatetruecolor($anchoNuevo, $altoNuevo);
        if ($redimensionada === false) {
            throw new RuntimeException('No se pudo crear la imagen de salida.');
        }

        // Los PNG con transparencia se conservan como PNG; todo lo demás pasa a JPEG.
        if ($mimeReal === 'image/png' && self::pngTieneAlpha($imagen)) {
            imagealphablending($redimensionada, false);
            imagesavealpha($redimensionada, true);
            $transparente = imagecolorallocatealpha($redimensionada, 0, 0, 0, 127);
            if ($transparente !== false) {
                imagefilledrectangle($redimensionada, 0, 0, $anchoNuevo, $altoNuevo, $transparente);
            }
            imagecopyresampled(
                $redimensionada, $imagen,
                0, 0, 0, 0,
                $anchoNuevo, $altoNuevo, $anchoOriginal, $altoOriginal
            );

            if (!imagepng($redimensionada, $destinoTmp, 9)) {
                throw new RuntimeException('No se pudo generar el PNG comprimido.');
            }

            return ['mime' => 'image/png', 'extension' => 'png'];
        }

        // Fondo blanco por si el original (PNG sin alpha) tuviera esquinas negras.
        $blanco = imagecolorallocate($redimensionada, 255, 255, 255);
        if ($blanco !== false) {
            imagefilledrectangle($redimensionada, 0, 0, $anchoNuevo, $altoNuevo, $blanco);
        }
        imagecopyresampled(
            $redimensionada, $imagen,
            0, 0, 0, 0,
            $anchoNuevo, $altoNuevo, $anchoOriginal, $altoOriginal
        );

        if (!imagejpeg($redimensionada, $destinoTmp, self::JPEG_QUALITY)) {
            throw new RuntimeException('No se pudo generar el JPEG comprimido.');
        }

        return ['mime' => 'image/jpeg', 'extension' => 'jpg'];
    }

    /**
     * Detecta si un PNG tiene píxeles con transparencia. Muestrea con paso
     * variable para no recorrer millones de píxeles en imágenes grandes.
     */
    private static function pngTieneAlpha(\GdImage $imagen): bool
    {
        $ancho = imagesx($imagen);
        $alto = imagesy($imagen);
        $paso = max(1, (int) floor(min($ancho, $alto) / 256));

        for ($y = 0; $y < $alto; $y += $paso) {
            for ($x = 0; $x < $ancho; $x += $paso) {
                $color = imagecolorsforindex($imagen, imagecolorat($imagen, $x, $y));
                // alpha 0 = opaco; mayor a 0 = píxel con transparencia.
                if (($color['alpha'] ?? 0) > 0) {
                    return true;
                }
            }
        }

        return false;
    }
}
