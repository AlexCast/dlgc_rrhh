<?php
declare(strict_types=1);

/**
 * Endpoint del módulo Documentación (31): registra un documento del empleado en sesión.
 *
 * Reglas de negocio:
 *  - Módulo transversal: cualquier usuario autenticado puede usarlo.
 *  - Solo permite VER e INSERTAR: este endpoint únicamente inserta; no existe
 *    actualización ni eliminación para este módulo.
 *  - Toda consulta se ejecuta exclusivamente a través de funciones/procedimientos
 *    almacenados (fun_* y proc_*) mediante PDO y sentencias preparadas.
 */

require_once __DIR__ . '/session_bootstrap.php';
require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/auth_guard.php';
require_once __DIR__ . '/helpers/InputSanitizer.php';
require_once __DIR__ . '/helpers/FileStorage/StorageFactory.php';
require_once __DIR__ . '/helpers/FileCompressor.php';
require_once __DIR__ . '/helpers/TurnstileHelper.php';

header('Content-Type: application/json; charset=utf-8');

function responderDocGuardarJson(bool $success, string $message, int $httpCode = 200): void
{
    http_response_code($httpCode);
    echo json_encode(['success' => $success, 'message' => $message], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responderDocGuardarJson(false, 'Método no permitido.', 405);
}

if (!csrf_validate(false)) {
    responderDocGuardarJson(false, 'Token de seguridad inválido o ausente. Recarga la página e inténtalo de nuevo.', 403);
}

// Verificación anti-bot (Cloudflare Turnstile) antes de procesar el archivo.
if (!TurnstileHelper::verify($_POST['cf-turnstile-response'] ?? null, 'doc_empleado')) {
    responderDocGuardarJson(false, 'No pudimos verificar que no eres un robot. Resuelve la verificación de seguridad e inténtalo de nuevo.', 400);
}

$idUsuario = $_SESSION['id_usuario'] ?? null;
if (!is_string($idUsuario) || trim($idUsuario) === '') {
    responderDocGuardarJson(false, 'Sesión inválida.', 401);
}

// Rate limiting de subidas (20 por hora por usuario).
require_once __DIR__ . '/helpers/UploadRateLimiter.php';
$limiteSubidas = new UploadRateLimiter($conexion, 20, 60);
$chequeoSubidas = $limiteSubidas->verificar($idUsuario, 'documento_subida');
if (!$chequeoSubidas['permitido']) {
    responderDocGuardarJson(false, 'Has superado el límite de subidas. Inténtalo de nuevo en ' . $chequeoSubidas['minutos'] . ' minuto(s).', 429);
}
$limiteSubidas->registrar($idUsuario, 'documento_subida');

$idTipoDocumento = InputSanitizer::intValue($_POST['id_tipo_documento'] ?? null, 1);
$idContrato      = InputSanitizer::intValue($_POST['id_contrato'] ?? null, 1);
$titulo          = InputSanitizer::boundedText($_POST['titulo'] ?? null, 150);

if ($idTipoDocumento === null) {
    responderDocGuardarJson(false, 'El tipo de documento no es válido.', 400);
}

if ($titulo === null || mb_strlen(trim($titulo)) < 3) {
    responderDocGuardarJson(false, 'El título debe tener entre 3 y 150 caracteres.', 400);
}

// --- Validación del archivo adjunto (obligatorio: la documentación es el archivo) ---
$archivo = $_FILES['archivo'] ?? null;

if (!is_array($archivo) || !isset($archivo['error']) || is_array($archivo['error'])) {
    responderDocGuardarJson(false, 'Debes adjuntar el archivo del documento (JPG, PNG, WebP o PDF).', 400);
}

switch ($archivo['error']) {
    case UPLOAD_ERR_OK:
        break;
    case UPLOAD_ERR_INI_SIZE:
    case UPLOAD_ERR_FORM_SIZE:
        responderDocGuardarJson(false, 'El archivo supera el tamaño máximo permitido (10 MB).', 400);
    case UPLOAD_ERR_NO_FILE:
        responderDocGuardarJson(false, 'Debes adjuntar el archivo del documento (JPG, PNG, WebP o PDF).', 400);
    default:
        responderDocGuardarJson(false, 'Ocurrió un error al recibir el archivo.', 500);
}

$rutaTemporal = (string) ($archivo['tmp_name'] ?? '');
if ($rutaTemporal === '' || !is_uploaded_file($rutaTemporal)) {
    responderDocGuardarJson(false, 'El archivo recibido no es válido.', 400);
}

$tamanoBytes = (int) filesize($rutaTemporal);
if ($tamanoBytes <= 0 || $tamanoBytes > 10485760) {
    responderDocGuardarJson(false, 'El archivo debe pesar entre 1 byte y 10 MB.', 400);
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
    responderDocGuardarJson(false, 'Solo se permiten archivos PDF, JPG, PNG o WebP.', 400);
}

$nombreOriginal = basename((string) ($archivo['name'] ?? ''));
if ($nombreOriginal === '' || mb_strlen($nombreOriginal) > 255) {
    responderDocGuardarJson(false, 'El nombre del archivo no es válido.', 400);
}

$hashOriginal = hash_file('sha256', $rutaTemporal);
if ($hashOriginal === false || strlen($hashOriginal) !== 64) {
    responderDocGuardarJson(false, 'No se pudo calcular la integridad del archivo.', 500);
}

// --- Compresión antes de subir (imágenes con GD, PDFs con Ghostscript) ---
$archivoFinal = $rutaTemporal;
$tmpComprimido = null;

try {
    $tmpComprimido = tempnam(sys_get_temp_dir(), 'doc31_comp_');
    if ($tmpComprimido === false) {
        responderDocGuardarJson(false, 'No se pudo preparar el archivo para su subida.', 500);
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
    error_log('doc_guardar: error comprimiendo: ' . $e->getMessage());
    responderDocGuardarJson(false, 'No se pudo procesar el archivo.', 500);
}

// --- Almacenamiento del archivo (R2 si está configurado; LOCAL como respaldo) ---
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
$storageKey = 'documentos/' . $nombreAlmacen;
$rutaLocalAbsoluta = null;

try {
    if ($storage !== null) {
        $storage->upload($archivoFinal, $storageKey, $mimeFinal);
    } else {
        $directorioAbsoluto = dirname(__DIR__) . '/uploads/documentos';

        if (!is_dir($directorioAbsoluto) && !mkdir($directorioAbsoluto, 0750, true) && !is_dir($directorioAbsoluto)) {
            throw new RuntimeException('No se pudo preparar el almacenamiento de archivos.');
        }

        $rutaLocalAbsoluta = $directorioAbsoluto . '/' . $nombreAlmacen;

        // rename() admite tanto el temporal de PHP como el comprimido.
        if (!rename($archivoFinal, $rutaLocalAbsoluta)) {
            throw new RuntimeException('No se pudo guardar el archivo en el almacenamiento.');
        }
    }
    if ($tmpComprimido !== null) {
        @unlink($tmpComprimido);
    }

    // Transacción en la BD: crea el documento + su primera versión y devuelve el id.
    $sentencia = $conexion->prepare('CALL proc_registrar_documento_archivo(?, ?, ?, ?, ?, ?, ?, ?, ?, ?);');
    $sentencia->execute([
        $idUsuario,
        $idTipoDocumento,
        $idContrato,
        trim($titulo),
        $proveedor,
        $storageKey,
        $nombreOriginal,
        $mimeFinal,
        $tamanoFinal,
        $hashFinal,
    ]);

    $fila = $sentencia->fetch(PDO::FETCH_ASSOC);
    $sentencia->closeCursor();

    if ($fila === false || stripos((string) ($fila['resultado'] ?? ''), 'correctamente') === false) {
        throw new RuntimeException((string) ($fila['resultado'] ?? 'No se pudo registrar el documento.'));
    }
} catch (Throwable $e) {
    // Rollback del objeto subido: el procedimiento es transaccional y no deja
    // documento huérfano en la BD.
    if ($storage !== null) {
        try {
            $storage->delete($storageKey);
        } catch (Throwable $eRollback) {
            error_log('doc_guardar: rollback R2 falló: ' . $eRollback->getMessage());
        }
    } elseif ($rutaLocalAbsoluta !== null) {
        @unlink($rutaLocalAbsoluta);
    }

    error_log('Error en doc_guardar: ' . $e->getMessage());
    responderDocGuardarJson(false, $e instanceof RuntimeException ? $e->getMessage() : 'Ocurrió un error al registrar el documento.', 400);
}

responderDocGuardarJson(true, 'Documento registrado correctamente con su archivo.');
