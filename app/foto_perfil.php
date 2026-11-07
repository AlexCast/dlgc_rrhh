<?php
declare(strict_types=1);

/**
 * Endpoint proxy de la foto de perfil: sirve la imagen desde el bucket PRIVADO
 * de Cloudflare R2 únicamente a usuarios con sesión activa.
 *
 * La llave NUNCA proviene del usuario: se resuelve en la base de datos desde
 * t_empleados.foto_perfil (igual que gestor_descargar.php). Valores legacy
 * ("uploads/...") se sirven del disco como respaldo.
 *
 * Uso: /app/foto_perfil.php?id=<id_usuario>
 */

require_once __DIR__ . '/session_bootstrap.php';
require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/auth_guard.php';
require_once __DIR__ . '/helpers/InputSanitizer.php';
require_once __DIR__ . '/helpers/FileStorage/StorageFactory.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    die('Método no permitido.');
}

$idUsuario = InputSanitizer::boundedText($_GET['id'] ?? null, 20);
if ($idUsuario === null || $idUsuario === '') {
    http_response_code(400);
    die('Identificador de empleado no válido.');
}

try {
    $sentencia = $conexion->prepare(
        'SELECT foto_perfil, fec_update FROM t_empleados WHERE id_usuario = ? AND fec_delete IS NULL'
    );
    $sentencia->execute([$idUsuario]);
    $registro = $sentencia->fetch(PDO::FETCH_ASSOC);
    $sentencia->closeCursor();
} catch (PDOException $e) {
    error_log('foto_perfil: error de base de datos: ' . $e->getMessage());
    http_response_code(500);
    die('Ocurrió un error al buscar la foto.');
}

$fotoKey = ($registro === false || empty($registro['foto_perfil'])) ? null : (string) $registro['foto_perfil'];
$fecUpdate = ($registro === false) ? '' : (string) ($registro['fec_update'] ?? '');
if ($fotoKey === null || $fotoKey === '') {
    http_response_code(404);
    die('El empleado no tiene foto de perfil.');
}

// ETag derivado de la llave y de la última modificación del empleado: si el
// usuario cambia su foto, fec_update cambia y el navegador descarta su copia.
$etag = '"' . substr(hash('sha256', $fotoKey . '|' . $fecUpdate), 0, 32) . '"';

// Revalidación condicional: el navegador ya tiene la imagen → 304 sin descargar
// nada de R2 (la petición se resuelve solo contra la base de datos local).
if (isset($_SERVER['HTTP_IF_NONE_MATCH']) &&
    hash_equals($etag, trim((string) $_SERVER['HTTP_IF_NONE_MATCH']))) {
    header_remove('Cache-Control');
    header_remove('Pragma');
    header('ETag: ' . $etag);
    header('Cache-Control: private, max-age=3600');
    http_response_code(304);
    exit;
}

// Valores legacy guardados en disco (formato anterior: uploads/...).
if (str_starts_with($fotoKey, 'uploads/')) {
    $raizUploads = dirname(__DIR__) . '/uploads';
    $rutaReal = realpath($raizUploads . '/' . $fotoKey);
    $raizReal = realpath($raizUploads);

    if ($rutaReal === false || $raizReal === false ||
        strpos($rutaReal, $raizReal . DIRECTORY_SEPARATOR) !== 0 ||
        !is_file($rutaReal)) {
        http_response_code(404);
        die('Foto no encontrada.');
    }

    $contenido = file_get_contents($rutaReal);
    if ($contenido === false) {
        http_response_code(500);
        die('No se pudo leer la foto.');
    }
} else {
    // Llave R2: el bucket es privado y este proxy es el único punto de lectura.
    try {
        $contenido = StorageFactory::create()->download($fotoKey);
    } catch (Throwable $e) {
        error_log('foto_perfil: ' . $e->getMessage());
        http_response_code(404);
        if (getenv('APP_DEBUG') === '1') {
            die('Foto no encontrada. [DEBUG] ' . $e->getMessage());
        }
        die('Foto no encontrada.');
    }
}

$mime = (string) (finfo_buffer(finfo_open(FILEINFO_MIME_TYPE), $contenido) ?: 'application/octet-stream');
if (!in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true)) {
    http_response_code(415);
    die('Tipo de archivo no permitido.');
}

// auth_guard deja encabezados no-store; se retiran para permitir caché privada
// del navegador (la imagen ya está protegida por la sesión).
header_remove('Cache-Control');
header_remove('Pragma');
header('Content-Type: ' . $mime);
header('Content-Length: ' . (string) strlen($contenido));
header('ETag: ' . $etag);
header('Cache-Control: private, max-age=3600');
header('X-Content-Type-Options: nosniff');

echo $contenido;
exit;
