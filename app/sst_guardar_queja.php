<?php
declare(strict_types=1);

/**
 * Endpoint para enviar una queja/sugerencia al buzón SST.
 */

session_start();

require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/auth_guard.php';
require_once __DIR__ . '/helpers/Mailer.php';

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

if (!csrf_validate(false)) {
    responderJson(false, 'Token de seguridad inválido o ausente. Recarga la página e inténtalo de nuevo.', 403);
}

if (!has_module_access(25)) {
    responderJson(false, 'No tienes acceso al módulo SST.', 403);
}

$idUsuario = $_SESSION['id_usuario'] ?? null;
if (!is_string($idUsuario) || trim($idUsuario) === '') {
    responderJson(false, 'Sesión inválida.', 401);
}

$tipoPeticion = strtoupper(trim($_POST['tipo_peticion'] ?? ''));
$asunto       = trim($_POST['asunto'] ?? '');
$descripcion  = trim($_POST['descripcion'] ?? '');

if (!in_array($tipoPeticion, ['QUEJA', 'SUGERENCIA', 'RECLAMO', 'DENUNCIA'], true)) {
    responderJson(false, 'El tipo de petición no es válido.', 400);
}

if ($asunto === '' || mb_strlen($asunto) < 5) {
    responderJson(false, 'El asunto debe tener al menos 5 caracteres.', 400);
}

if ($descripcion === '' || mb_strlen($descripcion) < 10) {
    responderJson(false, 'La descripción debe tener al menos 10 caracteres.', 400);
}

try {
    $sentencia = $conexion->prepare('SELECT fun_insert_sst_queja(:id_usuario, :tipo_peticion, :asunto, :descripcion);');
    $sentencia->execute([
        ':id_usuario'    => $idUsuario,
        ':tipo_peticion' => $tipoPeticion,
        ':asunto'        => $asunto,
        ':descripcion'   => $descripcion,
    ]);

    $resultado = (string) $sentencia->fetchColumn();

    if (stripos($resultado, 'correctamente') === false) {
        responderJson(false, $resultado, 400);
    }

    // Notificar a encargados SST (módulo 26)
    notificarEncargadosSst($conexion, $idUsuario, $tipoPeticion, $asunto, $descripcion);

    responderJson(true, $resultado);
} catch (PDOException $e) {
    error_log('Error en sst_guardar_queja: ' . $e->getMessage());
    responderJson(false, 'Ocurrió un error al enviar la solicitud.', 500);
}

function notificarEncargadosSst(PDO $conexion, string $idUsuario, string $tipoPeticion, string $asunto, string $descripcion): void
{
    try {
        $sentenciaUsuario = $conexion->prepare(
            "SELECT primer_nombre, segundo_nombre, primer_apellido, segundo_apellido
             FROM t_usuarios
             WHERE id_usuario = :id_usuario
               AND fec_delete IS NULL"
        );
        $sentenciaUsuario->execute([':id_usuario' => $idUsuario]);
        $usuario = $sentenciaUsuario->fetch(PDO::FETCH_OBJ);

        $nombreUsuario = $usuario
            ? trim(($usuario->primer_nombre ?? '') . ' ' . ($usuario->segundo_nombre ?? '') . ' ' . ($usuario->primer_apellido ?? '') . ' ' . ($usuario->segundo_apellido ?? ''))
            : $idUsuario;

        $sentenciaEncargados = $conexion->prepare(
            "SELECT DISTINCT u.id_usuario, u.correo
             FROM t_usuarios u
             INNER JOIN t_roles_operaciones ro ON ro.id_rol = u.id_rol AND ro.fec_delete IS NULL
             INNER JOIN t_operaciones o ON o.id_operacion = ro.id_operacion AND o.fec_delete IS NULL
             WHERE o.id_modulo = 26
               AND u.fec_delete IS NULL
             UNION
             SELECT DISTINCT u.id_usuario, u.correo
             FROM t_usuarios u
             INNER JOIN t_usuarios_operaciones uo ON uo.id_usuario = u.id_usuario AND uo.fec_delete IS NULL
             INNER JOIN t_operaciones o ON o.id_operacion = uo.id_operacion AND o.fec_delete IS NULL
             WHERE o.id_modulo = 26
               AND u.fec_delete IS NULL"
        );
        $sentenciaEncargados->execute();
        $encargados = $sentenciaEncargados->fetchAll(PDO::FETCH_OBJ);

        if (empty($encargados)) {
            error_log('sst_guardar_queja: No hay encargados SST configurados para notificar.');
            return;
        }

        $mailer = new Mailer();
        $subject = 'Nueva ' . ucfirst(strtolower($tipoPeticion)) . ' en el Buzón SST';

        $body = '<h2>Nueva solicitud en el Buzón SST</h2>';
        $body .= '<p><strong>Tipo:</strong> ' . htmlspecialchars($tipoPeticion) . '</p>';
        $body .= '<p><strong>De:</strong> ' . htmlspecialchars($nombreUsuario) . ' (' . htmlspecialchars($idUsuario) . ')</p>';
        $body .= '<p><strong>Asunto:</strong> ' . htmlspecialchars($asunto) . '</p>';
        $body .= '<p><strong>Descripción:</strong></p>';
        $body .= '<p>' . nl2br(htmlspecialchars($descripcion)) . '</p>';
        $body .= '<hr><p>Ingresa al módulo de Administración SST para gestionar la solicitud.</p>';

        $altBody = "Nueva solicitud en el Buzón SST\n";
        $altBody .= "Tipo: {$tipoPeticion}\n";
        $altBody .= "De: {$nombreUsuario} ({$idUsuario})\n";
        $altBody .= "Asunto: {$asunto}\n";
        $altBody .= "Descripción:\n{$descripcion}\n";

        foreach ($encargados as $encargado) {
            $ok = $mailer->send($encargado->correo, $encargado->id_usuario, $subject, $body, $altBody);
            if (!$ok) {
                error_log('sst_guardar_queja: No se pudo notificar a ' . $encargado->correo);
            }
        }
    } catch (Throwable $e) {
        error_log('sst_guardar_queja error notificando: ' . $e->getMessage());
    }
}
