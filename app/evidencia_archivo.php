<?php
declare(strict_types=1);

/**
 * Endpoint de descarga/visualización de evidencias de solicitudes de permiso.
 * El bucket de R2 es privado: este proxy autenticado es el único punto de lectura.
 *
 * Control de acceso (idéntico a permiso_detalle.php): solo el empleado dueño de
 * la solicitud, su jefe responsable o un usuario con acceso al módulo 27 (RRHH).
 * La llave de almacenamiento NUNCA proviene del usuario: se resuelve en la BD.
 *
 * Uso: /app/evidencia_archivo.php?id_evidencia=<id>
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

$idEvidencia = InputSanitizer::intValue($_GET['id_evidencia'] ?? null, 1);
if ($idEvidencia === null) {
    http_response_code(400);
    die('Identificador de evidencia no válido.');
}

$idUsuario = $_SESSION['id_usuario'] ?? null;

try {
    $sentencia = $conexion->prepare(
        'SELECT e.id_evidencia, e.storage_key, e.nombre_original, e.mime_type, e.tamano_bytes,
                sp.id_empleado, sp.id_jefe_responsable
           FROM t_permisos_evidencias e
           INNER JOIN t_solicitudes_permisos sp ON sp.id_permiso = e.id_permiso AND sp.fec_delete IS NULL
          WHERE e.id_evidencia = ? AND e.fec_delete IS NULL'
    );
    $sentencia->execute([$idEvidencia]);
    $evidencia = $sentencia->fetch(PDO::FETCH_ASSOC);
    $sentencia->closeCursor();
} catch (PDOException $e) {
    error_log('evidencia_archivo: error de base de datos: ' . $e->getMessage());
    http_response_code(500);
    die('Ocurrió un error al buscar la evidencia.');
}

if ($evidencia === false) {
    http_response_code(404);
    die('Evidencia no encontrada.');
}

// Control de acceso: dueño, jefe responsable o RRHH (módulo 27).
$esPropia = ($evidencia['id_empleado'] ?? null) === $idUsuario;
$esJefeResponsable = ($evidencia['id_jefe_responsable'] ?? null) === $idUsuario;
$esRrhh = has_module_access(27);

if (!$esPropia && !$esJefeResponsable && !$esRrhh) {
    http_response_code(403);
    die('No tienes permiso para ver esta evidencia.');
}

$mime = (string) ($evidencia['mime_type'] ?? '');
if (!in_array($mime, ['application/pdf', 'image/jpeg', 'image/png', 'image/webp'], true)) {
    http_response_code(415);
    die('El tipo de archivo solicitado no está permitido.');
}

$storageKey = (string) ($evidencia['storage_key'] ?? '');
if ($storageKey === '') {
    http_response_code(404);
    die('La evidencia no tiene archivo asociado.');
}

// La llave es única e inmutable por subida: el ETag derivado de ella es estable.
$etag = '"' . substr(hash('sha256', 'evidencia|' . $storageKey), 0, 32) . '"';

if (isset($_SERVER['HTTP_IF_NONE_MATCH']) &&
    hash_equals($etag, trim((string) $_SERVER['HTTP_IF_NONE_MATCH']))) {
    header_remove('Cache-Control');
    header_remove('Pragma');
    header('ETag: ' . $etag);
    header('Cache-Control: private, max-age=3600');
    http_response_code(304);
    exit;
}

try {
    $contenido = StorageFactory::create()->download($storageKey);
} catch (Throwable $e) {
    error_log('evidencia_archivo: ' . $e->getMessage());
    http_response_code(404);
    die('El archivo de la evidencia no está disponible.');
}

$nombre = preg_replace('/[^\w.\- ]/u', '_', (string) ($evidencia['nombre_original'] ?? 'evidencia'));
$nombre = ($nombre === null || $nombre === '') ? 'evidencia' : $nombre;

header_remove('Cache-Control');
header_remove('Pragma');
header('Content-Type: ' . $mime);
header('Content-Length: ' . (string) strlen($contenido));
header('ETag: ' . $etag);
header('Cache-Control: private, max-age=3600');
header('Content-Disposition: inline; filename="' . $nombre . '"');
header('X-Content-Type-Options: nosniff');

echo $contenido;
exit;
