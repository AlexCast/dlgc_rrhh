<?php
declare(strict_types=1);

/**
 * Endpoint del Gestor de Archivos (30): sube una nueva versión de un archivo.
 *
 * Reglas:
 *  - Acceso restringido por RBAC (módulo 30, acción CREAR).
 *  - Protección CSRF obligatoria (multipart/form-data con csrf_token).
 *  - El archivo se valida (MIME real vía finfo, tamaño 1 byte .. 10 MB, extensión)
 *    y se guarda en el proveedor de objetos configurado: R2 (bucket privado,
 *    llave documentos/<id_documento>/<hex>.<ext>) o LOCAL (disco, desarrollo).
 *    En la base de datos solo queda la referencia mediante el procedimiento
 *    proc_subir_version_documento.
 *  - La versión anterior queda en el historial: nada se sobrescribe.
 */

require_once __DIR__ . '/session_bootstrap.php';
require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/auth_guard.php';
require_once __DIR__ . '/helpers/InputSanitizer.php';
require_once __DIR__ . '/helpers/TurnstileHelper.php';

header('Content-Type: application/json; charset=utf-8');

function responderGestorSubirJson(bool $success, string $message, int $httpCode = 200, array $extra = []): void
{
    http_response_code($httpCode);
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $extra), JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responderGestorSubirJson(false, 'Método no permitido.', 405);
}

if (!csrf_validate(false)) {
    responderGestorSubirJson(false, 'Token de seguridad inválido o ausente. Recarga la página e inténtalo de nuevo.', 403);
}

// Verificación anti-bot (Cloudflare Turnstile) antes de procesar el archivo.
if (!TurnstileHelper::verify($_POST['cf-turnstile-response'] ?? null, 'gestor_archivo')) {
    responderGestorSubirJson(false, 'No pudimos verificar que no eres un robot. Resuelve la verificación de seguridad e inténtalo de nuevo.', 400);
}

if (!has_module_access(30)) {
    responderGestorSubirJson(false, 'No tienes acceso al Gestor de Archivos.', 403);
}

if (!has_module_permission(30, 'crear')) {
    responderGestorSubirJson(false, 'No tienes permiso para subir archivos en el Gestor de Archivos.', 403);
}

$idUsuario = $_SESSION['id_usuario'] ?? null;
if (!is_string($idUsuario) || trim($idUsuario) === '') {
    responderGestorSubirJson(false, 'Sesión inválida.', 401);
}

// Rate limiting de subidas (20 por hora por usuario).
require_once __DIR__ . '/helpers/UploadRateLimiter.php';
$limiteSubidas = new UploadRateLimiter($conexion, 20, 60);
$chequeoSubidas = $limiteSubidas->verificar($idUsuario, 'gestor_subida');
if (!$chequeoSubidas['permitido']) {
    responderGestorSubirJson(false, 'Has superado el límite de subidas. Inténtalo de nuevo en ' . $chequeoSubidas['minutos'] . ' minuto(s).', 429);
}
$limiteSubidas->registrar($idUsuario, 'gestor_subida');

$idDocumento = InputSanitizer::intValue($_POST['id_documento'] ?? null, 1);
$observacion = InputSanitizer::boundedText($_POST['observacion'] ?? null, 255);

if ($idDocumento === null) {
    responderGestorSubirJson(false, 'El identificador del documento no es válido.', 400);
}

// --- Validación del archivo recibido ----------------------------------------
$archivo = $_FILES['archivo'] ?? null;

if (!is_array($archivo) || !isset($archivo['error']) || is_array($archivo['error'])) {
    responderGestorSubirJson(false, 'No se recibió ningún archivo.', 400);
}

switch ($archivo['error']) {
    case UPLOAD_ERR_OK:
        break;
    case UPLOAD_ERR_INI_SIZE:
    case UPLOAD_ERR_FORM_SIZE:
        responderGestorSubirJson(false, 'El archivo supera el tamaño máximo permitido (10 MB).', 400);
        // responderGestorSubirJson termina la ejecución; no hace falta break.
    case UPLOAD_ERR_NO_FILE:
        responderGestorSubirJson(false, 'No se recibió ningún archivo.', 400);
    default:
        responderGestorSubirJson(false, 'Ocurrió un error al recibir el archivo.', 500);
}

$rutaTemporal = (string) ($archivo['tmp_name'] ?? '');
if ($rutaTemporal === '' || !is_uploaded_file($rutaTemporal)) {
    responderGestorSubirJson(false, 'El archivo recibido no es válido.', 400);
}

$tamanoBytes = (int) filesize($rutaTemporal);
if ($tamanoBytes <= 0 || $tamanoBytes > 10485760) {
    responderGestorSubirJson(false, 'El archivo debe pesar entre 1 byte y 10 MB.', 400);
}

$mimesPermitidos = [
    'application/pdf' => 'pdf',
    'image/jpeg'      => 'jpg',
    'image/png'       => 'png',
    'image/webp'      => 'webp',
];

$finfo = new finfo(FILEINFO_MIME_TYPE);
$mimeReal = (string) $finfo->file($rutaTemporal);

if (!isset($mimesPermitidos[$mimeReal])) {
    responderGestorSubirJson(false, 'Solo se permiten archivos PDF, JPG, PNG o WebP.', 400);
}

$nombreOriginal = basename((string) ($archivo['name'] ?? ''));
if ($nombreOriginal === '' || mb_strlen($nombreOriginal) > 255) {
    responderGestorSubirJson(false, 'El nombre del archivo no es válido.', 400);
}

$hashOriginal = hash_file('sha256', $rutaTemporal);
if ($hashOriginal === false || strlen($hashOriginal) !== 64) {
    responderGestorSubirJson(false, 'No se pudo calcular la integridad del archivo.', 500);
}

// --- Compresión antes de subir (imágenes con GD, PDFs con Ghostscript) ---
require_once __DIR__ . '/helpers/FileCompressor.php';

$archivoFinal = $rutaTemporal;
$tmpComprimido = null;

try {
    $tmpComprimido = tempnam(sys_get_temp_dir(), 'doc_comp_');
    if ($tmpComprimido === false) {
        responderGestorSubirJson(false, 'No se pudo preparar el archivo para su subida.', 500);
    }

    $comp = FileCompressor::compress($rutaTemporal, $tmpComprimido, $mimeReal);
    $archivoFinal = $tmpComprimido;

    $mimeFinal = $comp['mime'];
    $tamanoFinal = (int) filesize($archivoFinal);
    $hashFinal = hash_file('sha256', $archivoFinal);
    if ($hashFinal === false || strlen($hashFinal) !== 64) {
        throw new RuntimeException('No se pudo calcular la integridad del archivo procesado.');
    }
} catch (RuntimeException $e) {
    if ($tmpComprimido !== null) {
        @unlink($tmpComprimido);
    }
    error_log('gestor_subir: error comprimiendo: ' . $e->getMessage());
    responderGestorSubirJson(false, 'No se pudo procesar el archivo.', 500);
}

// --- Persistencia del archivo (R2 si está configurado; LOCAL como respaldo) ---
require_once __DIR__ . '/helpers/FileStorage/StorageFactory.php';

$storage = null;
$proveedor = 'LOCAL';

$configPath = dirname(__DIR__) . '/config/storage_config.php';
if (is_file($configPath)) {
    $configStorage = require $configPath;
    if (strtoupper((string) ($configStorage['provider'] ?? '')) === 'R2') {
        $storage = StorageFactory::create();
        $proveedor = 'R2';
    }
}

$nombreAlmacen = bin2hex(random_bytes(8)) . '.' . $comp['extension'];
$storageKey = 'documentos/' . $idDocumento . '/' . $nombreAlmacen;

$rutaLocalAbsoluta = null;

if ($storage !== null) {
    try {
        $storage->upload($archivoFinal, $storageKey, $mimeFinal);
    } catch (Throwable $e) {
        @unlink($tmpComprimido);
        error_log('gestor_subir: error subiendo a R2: ' . $e->getMessage());
        responderGestorSubirJson(false, 'No se pudo guardar el archivo en el almacenamiento.', 500);
    }
    if ($tmpComprimido !== null) {
        @unlink($tmpComprimido);
    }
} else {
    // Modo LOCAL (sin R2 configurado): flujo de disco anterior.
    $directorioAbsoluto = dirname(__DIR__) . '/uploads/documentos/' . $idDocumento;

    if (!is_dir($directorioAbsoluto) && !mkdir($directorioAbsoluto, 0750, true) && !is_dir($directorioAbsoluto)) {
        @unlink($tmpComprimido);
        responderGestorSubirJson(false, 'No se pudo preparar el almacenamiento de archivos.', 500);
    }

    $rutaLocalAbsoluta = $directorioAbsoluto . '/' . $nombreAlmacen;

    // rename() admite tanto el temporal de PHP como el comprimido.
    if (!rename($archivoFinal, $rutaLocalAbsoluta)) {
        @unlink($tmpComprimido);
        responderGestorSubirJson(false, 'No se pudo guardar el archivo en el almacenamiento.', 500);
    }
    if ($tmpComprimido !== null) {
        @unlink($tmpComprimido);
    }
}

try {
    $sentencia = $conexion->prepare('CALL proc_subir_version_documento(?, ?, ?, ?, ?, ?, ?, ?, ?);');
    $sentencia->execute([
        $idDocumento,
        $proveedor,
        $storageKey,
        $nombreOriginal,
        $mimeFinal,
        $tamanoFinal,
        $hashFinal,
        $observacion ?? '',
        $idUsuario,
    ]);

    $resultado = (string) $sentencia->fetchColumn();
    $sentencia->closeCursor();

    if (stripos($resultado, 'correctamente') === false) {
        // Rollback del objeto subido para no dejar huérfanos.
        if ($storage !== null) {
            try {
                $storage->delete($storageKey);
            } catch (Throwable $eRollback) {
                error_log('gestor_subir: rollback R2 falló: ' . $eRollback->getMessage());
            }
        } elseif ($rutaLocalAbsoluta !== null) {
            @unlink($rutaLocalAbsoluta);
        }
        responderGestorSubirJson(false, $resultado, 400);
    }

    responderGestorSubirJson(true, $resultado);
} catch (PDOException $e) {
    if ($storage !== null) {
        try {
            $storage->delete($storageKey);
        } catch (Throwable $eRollback) {
            error_log('gestor_subir: rollback R2 falló: ' . $eRollback->getMessage());
        }
    } elseif ($rutaLocalAbsoluta !== null) {
        @unlink($rutaLocalAbsoluta);
    }
    error_log('Error en gestor_subir: ' . $e->getMessage());
    responderGestorSubirJson(false, 'Ocurrió un error al registrar la versión del archivo.', 500);
}
