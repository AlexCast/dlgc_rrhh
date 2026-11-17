<?php
declare(strict_types=1);

require_once __DIR__ . '/session_bootstrap.php';
require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/auth_guard.php';
require_once __DIR__ . '/helpers/InputSanitizer.php';
require_once __DIR__ . '/helpers/FileStorage/StorageFactory.php';

header('Content-Type: application/json; charset=utf-8');

function responderGestorRechazarJson(bool $success, string $message, int $httpCode = 200): void
{
    http_response_code($httpCode);
    echo json_encode(['success' => $success, 'message' => $message], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responderGestorRechazarJson(false, 'Método no permitido.', 405);
}

if (!csrf_validate(false)) {
    responderGestorRechazarJson(false, 'Token de seguridad inválido o ausente.', 403);
}

if (!has_module_access(30) || !has_module_permission(30, 'eliminar')) {
    responderGestorRechazarJson(false, 'No tienes permiso para rechazar documentos.', 403);
}

$idVersion = InputSanitizer::intValue($_POST['id_version'] ?? null, 1);
$motivo = InputSanitizer::boundedText($_POST['motivo'] ?? null, 255);
$idRevisor = $_SESSION['id_usuario'] ?? null;

if ($idVersion === null || $motivo === null || mb_strlen(trim($motivo)) < 3 ||
    !is_string($idRevisor) || trim($idRevisor) === '') {
    responderGestorRechazarJson(false, 'El motivo y los datos de rechazo son obligatorios.', 400);
}

try {
    $sentencia = $conexion->prepare('CALL proc_obtener_version_archivo(?)');
    $sentencia->execute([$idVersion]);
    $version = $sentencia->fetch(PDO::FETCH_ASSOC);
    $sentencia->closeCursor();
} catch (PDOException $e) {
    error_log('Error consultando versión en gestor_rechazar: ' . $e->getMessage());
    responderGestorRechazarJson(false, 'No se pudo consultar la versión.', 500);
}

if ($version === false || ($version['estado_aprobacion'] ?? '') !== 'PENDIENTE') {
    responderGestorRechazarJson(false, 'Solo se pueden rechazar versiones pendientes.', 409);
}

try {
    $sentencia = $conexion->prepare('CALL proc_rechazar_version_documento(?, ?, ?)');
    $sentencia->execute([$idVersion, $idRevisor, $motivo]);
    $resultado = (string) $sentencia->fetchColumn();
    $sentencia->closeCursor();

    if (stripos($resultado, 'correctamente') === false) {
        responderGestorRechazarJson(false, $resultado, 409);
    }

    $proveedor = strtoupper((string) ($version['proveedor'] ?? 'LOCAL'));
    $storageKey = (string) ($version['storage_key'] ?? '');
    $eliminado = false;

    if ($proveedor === 'R2') {
        $eliminado = StorageFactory::create()->delete($storageKey);
    } elseif ($proveedor === 'LOCAL') {
        $raizUploads = realpath(dirname(__DIR__) . '/uploads');
        $rutaReal = realpath(dirname(__DIR__) . '/uploads/' . $storageKey);
        $eliminado = $raizUploads !== false && $rutaReal !== false
            && strpos($rutaReal, $raizUploads . DIRECTORY_SEPARATOR) === 0
            && is_file($rutaReal)
            && unlink($rutaReal);
    }

    if (!$eliminado) {
        error_log('gestor_rechazar: no se pudo eliminar almacenamiento para id_version=' . $idVersion);
        responderGestorRechazarJson(false, 'La versión fue rechazada, pero no se pudo limpiar el archivo. Requiere revisión administrativa.', 500);
    }

    $sentencia = $conexion->prepare('CALL proc_marcar_version_eliminada(?, ?)');
    $sentencia->execute([$idVersion, $idRevisor]);
    $eliminada = (int) $sentencia->fetchColumn();
    $sentencia->closeCursor();

    if ($eliminada !== 1) {
        error_log('gestor_rechazar: no se pudo marcar versión eliminada para id_version=' . $idVersion);
        responderGestorRechazarJson(false, 'El archivo fue eliminado, pero no se pudo completar su limpieza lógica.', 500);
    }

    responderGestorRechazarJson(true, 'Versión rechazada y eliminada correctamente.');
} catch (Throwable $e) {
    error_log('Error en gestor_rechazar: ' . $e->getMessage());
    responderGestorRechazarJson(false, 'No se pudo completar el rechazo de la versión.', 500);
}
