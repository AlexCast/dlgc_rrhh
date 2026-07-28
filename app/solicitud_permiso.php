<?php
declare(strict_types=1);

/**
 * Endpoint para crear una solicitud de permiso/vacaciones.
 * Recibe POST desde templates/solicitud_permiso.php, valida permiso 'crear'
 * en el módulo 3 y persiste la solicitud junto con su motivo.
 */

session_start();

require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/auth_guard.php';

header('Content-Type: application/json; charset=utf-8');

function responderJson(bool $success, string $message, int $httpCode = 200): void
{
    http_response_code($httpCode);
    echo json_encode(['success' => $success, 'message' => $message], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responderJson(false, 'Método no permitido.', 405);
}

// Validate CSRF token for AJAX request.
if (!csrf_validate(false)) {
    responderJson(false, 'Token de seguridad inválido o ausente. Recarga la página e inténtalo de nuevo.', 403);
}

if (!has_module_permission(3, 'crear')) {
    responderJson(false, 'No tienes permiso para crear solicitudes.', 403);
}

$idEmpleado = $_SESSION['id_usuario'] ?? null;
if (!is_string($idEmpleado) || trim($idEmpleado) === '') {
    responderJson(false, 'Sesión inválida.', 401);
}

// --- Recepción de campos ---
$tipoSolicitud   = strtolower(trim($_POST['tipo_solicitud'] ?? ''));
$fechaInicio     = trim($_POST['fecha_inicio'] ?? '');
$fechaFin        = trim($_POST['fecha_fin'] ?? '');
$horaInicio      = trim($_POST['hora_inicio'] ?? '') ?: null;
$horaFin         = trim($_POST['hora_fin'] ?? '') ?: null;
$motivo          = strtoupper(trim($_POST['motivo'] ?? ''));
$detalleMotivo   = trim($_POST['detalle_motivo'] ?? '') ?: null;
$permisoRemun    = strtoupper(trim($_POST['permiso_remunerado'] ?? ''));

// --- Validaciones básicas ---
$errores = [];

if (!in_array($tipoSolicitud, ['dias', 'horas'], true)) {
    $errores[] = 'El tipo de solicitud es obligatorio.';
}

if ($fechaInicio === '' || $fechaFin === '') {
    $errores[] = 'Las fechas de inicio y fin son obligatorias.';
}

if ($motivo === '') {
    $errores[] = 'El motivo de la solicitud es obligatorio.';
}

if (!in_array($permisoRemun, ['DINERO', 'VACACIONES', 'NO'], true)) {
    $errores[] = 'Debes seleccionar una opción de permiso remunerado.';
}

if (!empty($errores)) {
    responderJson(false, implode(' ', $errores), 400);
}

$esPorHoras = ($tipoSolicitud === 'horas');

// --- Mapeo de motivo a id_tipo_permiso ---
$mapaMotivos = [
    'ESTUDIO'         => 'Estudio',
    'CALAMIDAD'       => 'Calamidad Doméstica',
    'OTRO'            => 'Otro Motivo',
    'CONSULTA_MEDICA' => 'Consulta Médica',
    'FUERZA_MAYOR'    => 'Fuerza Mayor',
];

if (!array_key_exists($motivo, $mapaMotivos)) {
    responderJson(false, 'Motivo de permiso no válido.', 400);
}

$nombreTipoPermiso = $mapaMotivos[$motivo];

try {
    $sentenciaTipo = $conexion->prepare(
        'SELECT id_tipo_permiso FROM t_tipos_permisos WHERE nombre_tipo = :nombre AND fec_delete IS NULL LIMIT 1'
    );
    $sentenciaTipo->execute([':nombre' => $nombreTipoPermiso]);
    $idTipoPermiso = $sentenciaTipo->fetchColumn();

    if ($idTipoPermiso === false || $idTipoPermiso <= 0) {
        responderJson(false, 'El tipo de permiso seleccionado no está configurado.', 400);
    }
    $idTipoPermiso = (int) $idTipoPermiso;

    // Mapeo de metodo_descuento: 'NO' -> 'N/A'
    $metodoDescuento = ($permisoRemun === 'NO') ? 'N/A' : $permisoRemun;

    // --- Inserción de la solicitud ---
    $sentenciaInsert = $conexion->prepare(
        'INSERT INTO t_solicitudes_permisos (
            id_empleado,
            es_por_horas,
            fecha_inicio,
            fecha_fin,
            hora_inicio,
            hora_fin,
            estado,
            metodo_descuento,
            usr_insert,
            fec_insert
        ) VALUES (
            :id_empleado,
            :es_por_horas,
            :fecha_inicio,
            :fecha_fin,
            :hora_inicio,
            :hora_fin,
            :estado,
            :metodo_descuento,
            CURRENT_USER,
            CURRENT_TIMESTAMP
        ) RETURNING id_permiso'
    );

    $sentenciaInsert->execute([
        ':id_empleado'     => $idEmpleado,
        ':es_por_horas'   => $esPorHoras,
        ':fecha_inicio'   => $fechaInicio,
        ':fecha_fin'      => $fechaFin,
        ':hora_inicio'    => $horaInicio,
        ':hora_fin'       => $horaFin,
        ':estado'         => 'PENDIENTE',
        ':metodo_descuento' => $metodoDescuento,
    ]);

    $idPermiso = (int) $sentenciaInsert->fetchColumn();

    if ($idPermiso <= 0) {
        responderJson(false, 'No se pudo obtener el identificador de la solicitud.', 500);
    }

    // --- Inserción del motivo ---
    $sentenciaMotivo = $conexion->prepare(
        'INSERT INTO t_solicitudes_permisos_motivos (
            id_permiso,
            id_tipo_permiso,
            detalle_motivo,
            usr_insert,
            fec_insert
        ) VALUES (
            :id_permiso,
            :id_tipo_permiso,
            :detalle_motivo,
            CURRENT_USER,
            CURRENT_TIMESTAMP
        )'
    );

    $sentenciaMotivo->execute([
        ':id_permiso'     => $idPermiso,
        ':id_tipo_permiso' => $idTipoPermiso,
        ':detalle_motivo' => $detalleMotivo,
    ]);

    responderJson(true, 'Solicitud enviada correctamente.');

} catch (PDOException $e) {
    error_log('Error al crear solicitud de permiso: ' . $e->getMessage());
    responderJson(false, 'Error del servidor al procesar la solicitud.', 500);
}
