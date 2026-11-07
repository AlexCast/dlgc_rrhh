<?php
declare(strict_types=1);

/**
 * Endpoint de descarga de versiones de archivo.
 *
 * Acceso: usuarios del Gestor de Archivos (módulo 30) o el dueño del documento
 * (módulo Documentación 31, transversal). La ruta de almacenamiento NUNCA proviene
 * del usuario: se resuelve en la base de datos (proc_obtener_version_archivo).
 * Con proveedor 'R2' el contenido se descarga del bucket privado vía
 * StorageFactory; con 'LOCAL' (versiones legacy) se sirve solo si la ruta real
 * queda dentro de uploads/. En ambos casos se verifica el hash SHA-256.
 */

require_once __DIR__ . '/session_bootstrap.php';
require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/auth_guard.php';
require_once __DIR__ . '/helpers/InputSanitizer.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    die('Método no permitido.');
}

$idVersion = InputSanitizer::intValue($_GET['id_version'] ?? null, 1);
if ($idVersion === null) {
    http_response_code(400);
    die('El identificador de la versión no es válido.');
}

// Acceso: gestor (módulo 30) o, para el módulo Documentación (31) transversal,
// el dueño del documento puede descargar sus propios archivos.
$tieneModuloGestor = has_module_access(30);
$esDuenoDocumento = false;

if (!$tieneModuloGestor) {
    try {
        $sentenciaDueno = $conexion->prepare(
            'SELECT COUNT(*)
               FROM t_documentos_versiones v
               INNER JOIN t_documentos_rrhh d ON d.id_documento = v.id_documento
              WHERE v.id_version = ? AND v.fec_delete IS NULL AND d.fec_delete IS NULL
                AND d.id_usuario = ?'
        );
        $sentenciaDueno->execute([$idVersion, $_SESSION['id_usuario'] ?? '']);
        $esDuenoDocumento = (int) $sentenciaDueno->fetchColumn() > 0;
    } catch (PDOException $e) {
        error_log('gestor_descargar: error verificando dueño del documento: ' . $e->getMessage());
    }
}

if (!$tieneModuloGestor && !$esDuenoDocumento) {
    header('Location: /templates/access_denied.php', true, 302);
    exit;
}

try {
    $sentencia = $conexion->prepare('CALL proc_obtener_version_archivo(?)');
    $sentencia->execute([$idVersion]);
    $version = $sentencia->fetch(PDO::FETCH_ASSOC);
    $sentencia->closeCursor();
} catch (PDOException $e) {
    error_log('Error en gestor_descargar: ' . $e->getMessage());
    http_response_code(500);
    die('Ocurrió un error al buscar el archivo.');
}

if ($version === false) {
    http_response_code(404);
    die('Archivo no encontrado.');
}

$mimesPermitidos = ['application/pdf', 'image/jpeg', 'image/png', 'image/webp'];
$mime = (string) ($version['mime_type'] ?? '');
if (!in_array($mime, $mimesPermitidos, true)) {
    http_response_code(415);
    die('El tipo de archivo solicitado no está permitido.');
}

$proveedor = strtoupper((string) ($version['proveedor'] ?? 'LOCAL'));

// --- Proveedor R2 (bucket privado): descarga vía StorageFactory -----------------
if ($proveedor === 'R2') {
    require_once __DIR__ . '/helpers/FileStorage/StorageFactory.php';

    try {
        $contenido = StorageFactory::create()->download((string) $version['storage_key']);
    } catch (Throwable $e) {
        error_log('gestor_descargar: error descargando de R2 para id_version=' . $idVersion . ': ' . $e->getMessage());
        http_response_code(404);
        die('Archivo no encontrado en el almacenamiento.');
    }

    $hashCalculado = hash('sha256', $contenido);
    if (!hash_equals((string) ($version['hash_sha256'] ?? ''), (string) $hashCalculado)) {
        error_log('gestor_descargar: hash SHA-256 no coincide para id_version=' . $idVersion);
        http_response_code(500);
        die('El archivo no superó la verificación de integridad.');
    }

    $nombreDescarga = preg_replace('/[^\w.\- ]/u', '_', (string) ($version['nombre_original'] ?? 'archivo'));
    $nombreDescarga = ($nombreDescarga === null || $nombreDescarga === '') ? 'archivo' : $nombreDescarga;

    header('Content-Type: ' . $mime);
    header('Content-Length: ' . (string) strlen($contenido));
    header('Content-Disposition: attachment; filename="' . $nombreDescarga . '"');
    header('X-Content-Type-Options: nosniff');
    header('Cache-Control: private, no-store');

    echo $contenido;
    exit;
}

// --- Proveedor LOCAL (versiones legacy): flujo de disco anterior ---------------

$raizUploads = dirname(__DIR__) . '/uploads';
$rutaReal = realpath($raizUploads . '/' . (string) $version['storage_key']);
$raizReal = realpath($raizUploads);

if ($rutaReal === false || $raizReal === false ||
    strpos($rutaReal, $raizReal . DIRECTORY_SEPARATOR) !== 0 ||
    !is_file($rutaReal)) {
    http_response_code(404);
    die('Archivo no encontrado.');
}

$hashCalculado = hash_file('sha256', $rutaReal);
if (!hash_equals((string) ($version['hash_sha256'] ?? ''), (string) $hashCalculado)) {
    error_log('gestor_descargar: hash SHA-256 no coincide para id_version=' . $idVersion);
    http_response_code(500);
    die('El archivo no superó la verificación de integridad.');
}

$nombreDescarga = preg_replace('/[^\w.\- ]/u', '_', (string) ($version['nombre_original'] ?? 'archivo'));
$nombreDescarga = ($nombreDescarga === null || $nombreDescarga === '') ? 'archivo' : $nombreDescarga;

header('Content-Type: ' . $mime);
header('Content-Length: ' . (string) filesize($rutaReal));
header('Content-Disposition: attachment; filename="' . $nombreDescarga . '"');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: private, no-store');

readfile($rutaReal);
exit;
