<?php

declare(strict_types=1);

/**
 * FileCompressor.php
 * Compresión de archivos antes de subirlos a R2 (Fase 4).
 *
 * - Imágenes (JPG/PNG): GD nativa, redimensiona a un máximo configurable
 *   (1600 px por defecto) y re-codifica a JPEG (calidad 80). Los PNG con
 *   transparencia se conservan como PNG con compresión máxima.
 * - PDFs: Ghostscript (pdfwrite, /ebook ≈ 150 dpi). Si Ghostscript no está
 *   instalado/configurado, o el PDF ya está optimizado, se sube el original.
 *
 * Si el resultado comprimido pesa MÁS que el original, se conserva el original
 * (nunca empeoramos el tamaño).
 */
class FileCompressor
{
    private const DEFAULT_IMAGE_MAX_DIMENSION = 1600;
    private const DEFAULT_JPEG_QUALITY = 80;

    private static ?array $config = null;
    private static ?bool $gsResuelto = null;
    private static string $gsPath = '';

    private static function config(): array
    {
        if (self::$config === null) {
            $configPath = __DIR__ . '/../../config/storage_config.php';
            self::$config = is_file($configPath) ? (require $configPath) : [];
        }

        return self::$config;
    }

    /**
     * Perfil de pdfwrite: 'screen' (72 dpi), 'ebook' (150 dpi), 'printer' (300 dpi)
     * o 'prepress'. Default 'ebook'.
     */
    public static function pdfSettings(): string
    {
        $valor = strtolower(trim((string) (self::config()['compression']['pdf_settings'] ?? 'ebook')));
        return in_array($valor, ['screen', 'ebook', 'printer', 'prepress'], true) ? $valor : 'ebook';
    }

    /**
     * Si el perfil principal no logra el ahorro mínimo, reintentar con /screen.
     */
    public static function pdfFallbackScreen(): bool
    {
        return (bool) (self::config()['compression']['pdf_fallback_screen'] ?? true);
    }

    /**
     * Ahorro mínimo exigido (en %); si no se alcanza, se reintenta o se conserva el original.
     */
    public static function pdfMinAhorro(): int
    {
        $valor = (int) (self::config()['compression']['pdf_min_ahorro'] ?? 15);
        return ($valor >= 0 && $valor <= 90) ? $valor : 15;
    }

    public static function imageMaxDimension(): int
    {
        $valor = (int) (self::config()['compression']['image_max_dimension'] ?? self::DEFAULT_IMAGE_MAX_DIMENSION);
        return $valor > 0 ? $valor : self::DEFAULT_IMAGE_MAX_DIMENSION;
    }

    public static function jpegQuality(): int
    {
        $valor = (int) (self::config()['compression']['jpeg_quality'] ?? self::DEFAULT_JPEG_QUALITY);
        return ($valor >= 1 && $valor <= 100) ? $valor : self::DEFAULT_JPEG_QUALITY;
    }

    /**
     * Resuelve el ejecutable de Ghostscript: config explícita, auto-detección
     * en instalaciones típicas (Windows: C:\Program Files\gs, C:\tools\gs;
     * Linux: /usr/bin/gs, /usr/local/bin/gs) o PATH.
     */
    public static function gsPath(): string
    {
        if (self::$gsResuelto === null) {
            self::$gsResuelto = true;

            $configurado = trim((string) (self::config()['compression']['gs_path'] ?? ''));
            if ($configurado !== '' && is_file($configurado)) {
                self::$gsPath = $configurado;
                return self::$gsPath;
            }

            $esWindows = (PHP_OS_FAMILY ?? PHP_OS) === 'Windows';

            $candidatos = [];
            if ($esWindows) {
                foreach (['C:\\Program Files\\gs', 'C:\\Program Files (x86)\\gs', 'C:\\tools\\gs'] as $raiz) {
                    // Instalación típica: <raiz>\gsX.Y.Z\bin\gswin64c.exe o <raiz>\bin\gswin64c.exe.
                    foreach ([$raiz . '\\*\\bin\\gswin64c.exe', $raiz . '\\bin\\gswin64c.exe'] as $patron) {
                        $encontrados = glob($patron);
                        if (is_array($encontrados)) {
                            $candidatos = array_merge($candidatos, $encontrados);
                        }
                    }
                }

                if (!empty($candidatos)) {
                    rsort($candidatos); // la versión más nueva primero
                    self::$gsPath = (string) $candidatos[0];
                    return self::$gsPath;
                }

                // Último recurso en Windows: where.exe.
                foreach (['gswin64c', 'gs'] as $binario) {
                    $salida = [];
                    $estado = 0;
                    $ruta = @exec('where ' . $binario . ' 2>nul', $salida, $estado);
                    if ($estado === 0 && is_array($salida) && !empty($salida[0]) && is_file(trim((string) $salida[0]))) {
                        self::$gsPath = trim((string) $salida[0]);
                        return self::$gsPath;
                    }
                }
            } else {
                // Linux/macOS: rutas típicas y PATH (command -v o which).
                foreach (['/usr/bin/gs', '/usr/local/bin/gs'] as $ruta) {
                    if (is_file($ruta)) {
                        self::$gsPath = $ruta;
                        return self::$gsPath;
                    }
                }

                if (is_callable('shell_exec') && stripos((string) ini_get('disable_functions'), 'shell_exec') === false) {
                    $ruta = trim((string) @shell_exec('command -v gs 2>/dev/null'));
                    if ($ruta !== '' && is_file($ruta)) {
                        self::$gsPath = $ruta;
                        return self::$gsPath;
                    }
                } else {
                    foreach (['gs', 'gsc', 'gswin64c'] as $binario) {
                        $salida = [];
                        $estado = 0;
                        $ruta = trim((string) @exec('which ' . $binario . ' 2>/dev/null', $salida, $estado));
                        if ($estado === 0 && $ruta !== '' && is_file($ruta)) {
                            self::$gsPath = $ruta;
                            return self::$gsPath;
                        }
                    }
                }
            }

            self::$gsPath = '';
        }

        return self::$gsPath;
    }

    public static function pdfSupported(): bool
    {
        return self::gsPath() !== '';
    }

    /**
     * Comprime un archivo (imagen o PDF) y escribe el resultado en $destinoTmp.
     *
     * @return array{mime: string, extension: string, comprimido: bool}
     *   MIME y extensión del contenido final (pueden cambiar, p. ej. PNG→JPEG).
     * @throws RuntimeException Si el archivo de entrada es una imagen inválida.
     */
    public static function compress(string $rutaOrigen, string $destinoTmp, string $mime): array
    {
        if ($mime === 'image/jpeg' || $mime === 'image/png' || $mime === 'image/webp') {
            return self::compressImage($rutaOrigen, $destinoTmp, $mime);
        }

        if ($mime === 'application/pdf') {
            $ok = self::compressPdf($rutaOrigen, $destinoTmp);
            return [
                'mime'       => 'application/pdf',
                'extension'  => 'pdf',
                'comprimido' => $ok,
            ];
        }

        // Mime no comprimible: se copia el original tal cual.
        if (!copy($rutaOrigen, $destinoTmp)) {
            throw new RuntimeException('No se pudo preparar el archivo para su subida.');
        }
        return ['mime' => $mime, 'extension' => '', 'comprimido' => false];
    }

    private static function compressImage(string $rutaOrigen, string $destinoTmp, string $mime): array
    {
        if (!is_readable($rutaOrigen)) {
            throw new RuntimeException('La imagen recibida no es legible.');
        }

        $imagen = $mime === 'image/jpeg'
            ? @imagecreatefromjpeg($rutaOrigen)
            : ($mime === 'image/webp' ? @imagecreatefromwebp($rutaOrigen) : @imagecreatefrompng($rutaOrigen));

        if ($imagen === false) {
            throw new RuntimeException('La imagen está dañada o no se pudo leer.');
        }

        $anchoOriginal = imagesx($imagen);
        $altoOriginal = imagesy($imagen);
        $maxDimension = self::imageMaxDimension();

        $escala = min(1.0, $maxDimension / max($anchoOriginal, $altoOriginal));
        $anchoNuevo = max(1, (int) round($anchoOriginal * $escala));
        $altoNuevo = max(1, (int) round($altoOriginal * $escala));

        $redimensionada = imagecreatetruecolor($anchoNuevo, $altoNuevo);
        if ($redimensionada === false) {
            throw new RuntimeException('No se pudo crear la imagen de salida.');
        }

        $esPngAlpha = ($mime === 'image/png') && self::pngTieneAlpha($imagen, $anchoOriginal, $altoOriginal);

        if ($esPngAlpha) {
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

            return ['mime' => 'image/png', 'extension' => 'png', 'comprimido' => true];
        }

        $blanco = imagecolorallocate($redimensionada, 255, 255, 255);
        if ($blanco !== false) {
            imagefilledrectangle($redimensionada, 0, 0, $anchoNuevo, $altoNuevo, $blanco);
        }
        imagecopyresampled(
            $redimensionada, $imagen,
            0, 0, 0, 0,
            $anchoNuevo, $altoNuevo, $anchoOriginal, $altoOriginal
        );

        if (!imagejpeg($redimensionada, $destinoTmp, self::jpegQuality())) {
            throw new RuntimeException('No se pudo generar el JPEG comprimido.');
        }

        // Si el "comprimido" pesa más que el original, conservamos el original.
        if (filesize($destinoTmp) >= filesize($rutaOrigen)) {
            if (!copy($rutaOrigen, $destinoTmp)) {
                throw new RuntimeException('No se pudo conservar la imagen original.');
            }
            $extensionOriginal = [
                'image/jpeg' => 'jpg',
                'image/png'  => 'png',
                'image/webp' => 'webp',
            ][$mime] ?? '';
            return ['mime' => $mime, 'extension' => $extensionOriginal, 'comprimido' => false];
        }

        return ['mime' => 'image/jpeg', 'extension' => 'jpg', 'comprimido' => true];
    }

    private static function pngTieneAlpha(\GdImage $imagen, int $ancho, int $alto): bool
    {
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

    /**
     * Comprime un PDF con Ghostscript (pdfwrite). Estrategia:
     *  1. Perfil principal (ebook por defecto).
     *  2. Si no se alcanza pdf_min_ahorro y pdf_fallback_screen está activo,
     *     reintenta con /screen (72 dpi).
     *  3. Si aún así no hay ahorro, conserva el original (nunca empeora).
     * En caso de false, $destinoTmp contiene una copia del original.
     */
    private static function compressPdf(string $rutaOrigen, string $destinoTmp): bool
    {
        $gs = self::gsPath();
        if ($gs === '') {
            error_log('FileCompressor: Ghostscript no disponible; el PDF se sube sin comprimir.');
            if (!copy($rutaOrigen, $destinoTmp)) {
                throw new RuntimeException('No se pudo preparar el PDF para su subida.');
            }
            return false;
        }

        $tamanoOriginal = filesize($rutaOrigen);
        $minAhorro = self::pdfMinAhorro();
        $perfiles = [self::pdfSettings()];
        if (self::pdfFallbackScreen() && !in_array('screen', $perfiles, true)) {
            $perfiles[] = 'screen';
        }

        foreach ($perfiles as $perfil) {
            $salida = tempnam(sys_get_temp_dir(), 'gs_out_');
            if ($salida === false) {
                continue;
            }
            @unlink($salida); // ghostscript espera poder crear el archivo él mismo
            $salida .= '.pdf';

            $comando = [
                $gs,
                '-q',
                '-sDEVICE=pdfwrite',
                '-dPDFSETTINGS=/' . $perfil,
                '-dCompatibilityLevel=1.5',
                '-dNOPAUSE',
                '-dBATCH',
                '-sOutputFile=' . $salida,
                $rutaOrigen,
            ];

            $descriptores = [1 => ['pipe', 'w'], 2 => ['pipe', 'w']];
            $proceso = @proc_open($comando, $descriptores, $tuberias);

            $ok = false;
            $stderr = '';
            if (is_resource($proceso)) {
                fclose($tuberias[1]);
                $stderr = (string) stream_get_contents($tuberias[2]);
                fclose($tuberias[2]);
                $estado = proc_close($proceso);
                $ok = $estado === 0 && is_file($salida) && filesize($salida) > 0;
            }

            if ($ok) {
                $tamano = filesize($salida);
                $ahorro = (int) round((1 - $tamano / max(1, $tamanoOriginal)) * 100);

                if ($tamano < $tamanoOriginal && $ahorro >= $minAhorro) {
                    if (!rename($salida, $destinoTmp)) {
                        copy($salida, $destinoTmp);
                    }
                    @unlink($salida);
                    error_log("FileCompressor: PDF comprimido con /{$perfil}: {$tamanoOriginal} B -> {$tamano} B ({$ahorro}%).");
                    return true;
                }
            } else {
                error_log('FileCompressor: Ghostscript /' . $perfil . ' falló: ' . trim((string) $stderr));
            }

            @unlink($salida);
        }

        error_log('FileCompressor: el PDF no alcanzó el ahorro mínimo (' . $minAhorro . '%); se sube el original.');
        if (!copy($rutaOrigen, $destinoTmp)) {
            throw new RuntimeException('No se pudo preparar el PDF para su subida.');
        }
        return false;
    }
}
