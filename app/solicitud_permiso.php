<?php
declare(strict_types=1);

/**
 * Endpoint para crear una solicitud de permiso/vacaciones.
 * Recibe POST (multipart, para permitir adjuntos) desde templates/solicitud_permiso.php,
 * valida permiso 'crear' en el módulo 3, detecta el jefe directo del empleado, crea la
 * solicitud + motivos + las 2 filas de aprobación (JEFE/RRHH) de forma atómica, sube las
 * evidencias al proveedor externo y notifica por correo al jefe y a RRHH.
 */

require_once __DIR__ . '/session_bootstrap.php';
require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/auth_guard.php';
require_once __DIR__ . '/helpers/Mailer.php';
require_once __DIR__ . '/helpers/FileStorage/EvidenceValidator.php';
require_once __DIR__ . '/helpers/FileStorage/StorageFactory.php';

header('Content-Type: application/json; charset=utf-8');

function responderJson(bool $success, string $message, int $httpCode = 200, array $extra = []): void
{
    http_response_code($httpCode);
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $extra), JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responderJson(false, 'Método no permitido.', 405);
}

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
$idTipoPermiso = filter_input(INPUT_POST, 'motivo', FILTER_VALIDATE_INT) ?: 0;
$detalleMotivo = trim((string) ($_POST['detalle_motivo'] ?? ''));

$idsTiposPermiso = $idTipoPermiso > 0 ? [$idTipoPermiso] : [];
$detallesMotivo  = [$detalleMotivo];

$errores = [];

if (!in_array($tipoSolicitud, ['dias', 'horas'], true)) {
    $errores[] = 'El tipo de solicitud es obligatorio.';
}
if ($fechaInicio === '' || $fechaFin === '') {
    $errores[] = 'Las fechas de inicio y fin son obligatorias.';
} elseif ($tipoSolicitud === 'horas' && $fechaInicio !== $fechaFin) {
    // Un permiso por horas es de un único día; fecha_inicio y fecha_fin deben coincidir.
    $errores[] = 'El permiso por horas debe corresponder a un único día.';
}
if (empty($idsTiposPermiso)) {
    $errores[] = 'Debes seleccionar al menos un motivo.';
}

if (!empty($errores)) {
    responderJson(false, implode(' ', $errores), 400);
}

$esPorHoras = ($tipoSolicitud === 'horas');
// El método de descuento (remunerado o no) lo define RRHH al aprobar, no el empleado.
$metodoDescuento = null;

try {
    // --- Jefe directo (foto al momento de radicar) ---
    $sentenciaJefe = $conexion->prepare(
        'SELECT e.id_jefe, u.correo AS correo_jefe, u.primer_nombre AS nombre_jefe,
                ue.correo AS correo_empleado, ue.primer_nombre AS nombre_empleado, ue.primer_apellido AS apellido_empleado
         FROM t_empleados e
         INNER JOIN t_usuarios ue ON ue.id_usuario = e.id_usuario
         LEFT JOIN t_usuarios u ON u.id_usuario = e.id_jefe
         WHERE e.id_usuario = :id_empleado'
    );
    $sentenciaJefe->execute([':id_empleado' => $idEmpleado]);
    $filaJefe = $sentenciaJefe->fetch(PDO::FETCH_ASSOC);

    if ($filaJefe === false) {
        responderJson(false, 'No se encontró el registro de empleado en el sistema.', 404);
    }

    $idJefeResponsable = $filaJefe['id_jefe'] ?? null;
    if (!is_string($idJefeResponsable) || trim($idJefeResponsable) === '') {
        responderJson(false, 'No tienes un jefe directo asignado. Contacta a RRHH antes de continuar.', 400);
    }

    // --- Validar tipos de permiso y si requieren evidencia ---
    $placeholders = implode(',', array_fill(0, count($idsTiposPermiso), '?'));
    $sentenciaTipos = $conexion->prepare(
        "SELECT id_tipo_permiso, requiere_evidencia FROM t_tipos_permisos
         WHERE id_tipo_permiso IN ({$placeholders}) AND fec_delete IS NULL"
    );
    $sentenciaTipos->execute($idsTiposPermiso);
    $tiposEncontrados = $sentenciaTipos->fetchAll(PDO::FETCH_KEY_PAIR); // id => requiere_evidencia

    if (count($tiposEncontrados) !== count(array_unique($idsTiposPermiso))) {
        responderJson(false, 'Uno o más motivos seleccionados no son válidos.', 400);
    }

    $requiereEvidencia = in_array(true, array_map('boolval', $tiposEncontrados), true);

    // --- Validación y subida de evidencias (antes de crear la solicitud) ---
    $archivosSubidos = []; // [{storage_key, url_publica, nombre_original, mime_type, tamano_bytes}]
    $archivosEvidencia = $_FILES['evidencias'] ?? null;
    $cantidadArchivos = is_array($archivosEvidencia['name'] ?? null) ? count($archivosEvidencia['name']) : 0;

    if ($requiereEvidencia && $cantidadArchivos === 0) {
        responderJson(false, 'El motivo seleccionado requiere adjuntar evidencia (JPG, PNG o PDF).', 400);
    }

    if ($cantidadArchivos > 0) {
        $storage = StorageFactory::create();
        for ($i = 0; $i < $cantidadArchivos; $i++) {
            $archivo = [
                'name'     => $archivosEvidencia['name'][$i],
                'type'     => $archivosEvidencia['type'][$i],
                'tmp_name' => $archivosEvidencia['tmp_name'][$i],
                'error'    => $archivosEvidencia['error'][$i],
                'size'     => $archivosEvidencia['size'][$i],
            ];

            // Si no hay archivo real en este índice (por name="evidencias[]" vacío), saltarlo.
            if (($archivo['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
                continue;
            }

            $erroresArchivo = EvidenceValidator::validate($archivo);
            if (!empty($erroresArchivo)) {
                responderJson(false, implode(' ', $erroresArchivo), 400);
            }

            $mimeType = EvidenceValidator::detectMimeType($archivo['tmp_name']);
            $extension = pathinfo($archivo['name'], PATHINFO_EXTENSION);
            $storageKey = EvidenceValidator::buildStorageKey($idEmpleado, $extension);

            $resultado = $storage->upload($archivo['tmp_name'], $storageKey, $mimeType);

            $archivosSubidos[] = [
                'storage_key'     => $resultado['storage_key'],
                'url_publica'     => $resultado['url_publica'] ?? null,
                'nombre_original' => $archivo['name'],
                'mime_type'       => $mimeType,
                'tamano_bytes'    => (int) $archivo['size'],
            ];
        }
    }

    // --- Creación atómica de la solicitud (solicitud + motivos + aprobaciones JEFE/RRHH) ---
    $tiposLiteral = '{' . implode(',', array_map('intval', $idsTiposPermiso)) . '}';
    $detallesLiteral = '{' . implode(',', array_map(
        static fn(string $d): string => '"' . str_replace(['\\', '"'], ['\\\\', '\\"'], $d) . '"',
        array_pad($detallesMotivo, count($idsTiposPermiso), '')
    )) . '}';

    $sentenciaCrear = $conexion->prepare(
        'SELECT * FROM fun_insert_solicitudes_permisos(
            :id_empleado, :id_jefe_responsable, :es_por_horas, :fecha_inicio, :fecha_fin,
            :tipos_permiso, :detalles_motivo, :hora_inicio, :hora_fin, :metodo_descuento
        )'
    );
    $sentenciaCrear->execute([
        ':id_empleado'         => $idEmpleado,
        ':id_jefe_responsable' => $idJefeResponsable,
        ':es_por_horas'        => $esPorHoras ? 'true' : 'false',
        ':fecha_inicio'        => $fechaInicio,
        ':fecha_fin'           => $fechaFin,
        ':tipos_permiso'       => $tiposLiteral,
        ':detalles_motivo'     => $detallesLiteral,
        ':hora_inicio'         => $horaInicio,
        ':hora_fin'            => $horaFin,
        ':metodo_descuento'    => $metodoDescuento,
    ]);
    $resultadoCreacion = $sentenciaCrear->fetch(PDO::FETCH_ASSOC);

    if ($resultadoCreacion === false || !filter_var($resultadoCreacion['exito'], FILTER_VALIDATE_BOOLEAN)) {
        responderJson(false, $resultadoCreacion['mensaje'] ?? 'No se pudo crear la solicitud.', 400);
    }

    $idPermiso = (int) $resultadoCreacion['id_permiso'];

    // --- Registro de evidencias ya subidas ---
    if (!empty($archivosSubidos)) {
        $sentenciaEvidencia = $conexion->prepare(
            'SELECT fun_insert_permisos_evidencias(
                :id_permiso, :proveedor, :storage_key, :url_publica, :nombre_original, :mime_type, :tamano_bytes
            )'
        );
        foreach ($archivosSubidos as $evidencia) {
            $sentenciaEvidencia->execute([
                ':id_permiso'       => $idPermiso,
                ':proveedor'        => 'BUNNY',
                ':storage_key'      => $evidencia['storage_key'],
                ':url_publica'      => $evidencia['url_publica'],
                ':nombre_original'  => $evidencia['nombre_original'],
                ':mime_type'        => $evidencia['mime_type'],
                ':tamano_bytes'     => $evidencia['tamano_bytes'],
            ]);
        }
    }

    notificarSolicitudCreada($conexion, $idPermiso, $filaJefe);

    responderJson(true, 'Solicitud enviada correctamente.', 200, ['id_permiso' => $idPermiso]);

} catch (PDOException $e) {
    error_log('Error al crear solicitud de permiso: ' . $e->getMessage());
    responderJson(false, 'Error del servidor al procesar la solicitud.', 500);
} catch (Throwable $e) {
    error_log('Error al subir evidencia o notificar solicitud de permiso: ' . $e->getMessage());
    responderJson(false, 'No se pudo completar la solicitud: ' . $e->getMessage(), 500);
}

/**
 * Notifica por correo al jefe directo y a todos los usuarios con permiso VER del módulo 27
 * (bandeja RRHH). Nunca debe tumbar la respuesta de éxito si el envío falla.
 */
function notificarSolicitudCreada(PDO $conexion, int $idPermiso, array $filaJefe): void
{
    try {
        $nombreEmpleado = trim(($filaJefe['nombre_empleado'] ?? '') . ' ' . ($filaJefe['apellido_empleado'] ?? ''));
        $mailer = new Mailer();
        $subject = "Nueva solicitud de permiso #{$idPermiso} para tu revisión";

        $nombreEmpleadoHtml = htmlspecialchars($nombreEmpleado, ENT_QUOTES, 'UTF-8');

        $body = sprintf(
            '<!DOCTYPE html>
            <html lang="es">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Nueva solicitud de permiso</title>
                <style>
                    body { margin: 0; padding: 0; background-color: #ececec; font-family: Inter, system-ui, -apple-system, sans-serif; color: #0D130F; }
                    .wrapper { width: 100%%; padding: 40px 16px; }
                    .card { max-width: 480px; margin: 0 auto; background-color: #ffffff; border-radius: 24px; border: 2px solid #3ba86a; box-shadow: 0 12px 30px rgba(59, 168, 106, 0.12); overflow: hidden; }
                    .header { background-color: #3ba86a; padding: 24px; text-align: center; }
                    .header h1 { color: #ffffff; margin: 0; font-size: 1.25rem; font-weight: 700; }
                    .content { padding: 32px; }
                    .content p { margin: 0 0 16px; line-height: 1.6; font-size: 0.95rem; color: #0D130F; }
                    .content p strong { color: #0D130F; }
                    .badge { display: inline-block; background-color: #eafaf1; color: #298350; border-radius: 8px; padding: 4px 12px; font-weight: 600; font-size: 0.85rem; }
                    .notice { font-size: 0.85rem; color: #6b7280; }
                    .notice strong { color: #6b7280; }
                    .footer { text-align: center; padding: 16px 32px; font-size: 0.8rem; color: #6b7280; border-top: 1px solid #e5e7eb; }
                </style>
            </head>
            <body>
                <div class="wrapper">
                    <div class="card">
                        <div class="header">
                            <h1>Portal DLGC RRHH</h1>
                        </div>
                        <div class="content">
                            <p>Hola,</p>
                            <p><span class="badge">Solicitud #%d</span></p>
                            <p><strong>%s</strong> ha radicado una solicitud de permiso y está pendiente de tu aprobación.</p>
                            <p class="notice">Ingresa al portal DLGC RRHH para revisarla y dar respuesta a la brevedad.</p>
                            <p>Saludos,<br><strong>Portal DLGC RRHH</strong></p>
                        </div>
                        <div class="footer">
                            &copy; Distribuciones La Gran Cacharrería. Todos los derechos reservados.
                        </div>
                    </div>
                </div>
            </body>
            </html>',
            $idPermiso,
            $nombreEmpleadoHtml
        );
        $altBody = "Portal DLGC RRHH\n\n"
                 . "{$nombreEmpleado} ha radicado la solicitud de permiso #{$idPermiso}, pendiente de tu aprobación.\n\n"
                 . "Ingresa al portal DLGC RRHH para revisarla.\n\n"
                 . "Distribuciones La Gran Cacharrería";

        if (!empty($filaJefe['correo_jefe'])) {
            $mailer->send($filaJefe['correo_jefe'], (string) ($filaJefe['nombre_jefe'] ?? 'Jefe directo'), $subject, $body, $altBody);
        }

        $sentenciaRrhh = $conexion->prepare(
            "SELECT DISTINCT u.id_usuario, u.correo
             FROM t_usuarios u
             INNER JOIN t_roles_operaciones ro ON ro.id_rol = u.id_rol AND ro.fec_delete IS NULL
             INNER JOIN t_operaciones o ON o.id_operacion = ro.id_operacion AND o.fec_delete IS NULL
             WHERE o.id_modulo = 27 AND o.nombre_operacion = 'VER' AND u.fec_delete IS NULL
             UNION
             SELECT DISTINCT u.id_usuario, u.correo
             FROM t_usuarios u
             INNER JOIN t_usuarios_operaciones uo ON uo.id_usuario = u.id_usuario AND uo.fec_delete IS NULL
             INNER JOIN t_operaciones o ON o.id_operacion = uo.id_operacion AND o.fec_delete IS NULL
             WHERE o.id_modulo = 27 AND o.nombre_operacion = 'VER' AND u.fec_delete IS NULL"
        );
        $sentenciaRrhh->execute();
        $usuariosRrhh = $sentenciaRrhh->fetchAll(PDO::FETCH_ASSOC);

        foreach ($usuariosRrhh as $usuarioRrhh) {
            if (!empty($usuarioRrhh['correo'])) {
                $mailer->send($usuarioRrhh['correo'], $usuarioRrhh['id_usuario'], $subject, $body, $altBody);
            }
        }
    } catch (Throwable $e) {
        error_log('notificarSolicitudCreada: no se pudo enviar la notificación: ' . $e->getMessage());
    }
}
