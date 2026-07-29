<?php

declare(strict_types=1);

require_once __DIR__ . '/../../app/auth_guard.php';
require_once __DIR__ . '/../../app/conexion.php';

// Cualquier usuario autenticado puede ver quién leyó un comunicado que ya vio.
// El SRC (módulo 24) ya protege la vista administrativa; este endpoint es usado
// también desde el portal de comunicados (módulo 5) por usuarios comunes.
if (!has_module_access(5) && !has_module_access(24)) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'Acceso denegado']);
    exit();
}

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Método no permitido']);
    exit();
}

csrf_validate(false);

$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) {
    $input = $_POST;
}

$id_comunicado = isset($input['id_comunicado']) ? (int) $input['id_comunicado'] : 0;

if ($id_comunicado <= 0) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'ID de comunicado inválido']);
    exit();
}

try {
    $sentencia = $conexion->prepare("
        SELECT
            v.id_usuario,
            COALESCE(
                NULLIF(TRIM(CONCAT_WS(' ', u.primer_nombre, u.segundo_nombre, u.primer_apellido, u.segundo_apellido)), ''),
                u.username
            ) AS nombre_completo,
            e.foto_perfil,
            v.fec_visto
        FROM t_comunicados_vistos v
        INNER JOIN t_usuarios u ON u.id_usuario = v.id_usuario
        LEFT JOIN t_empleados e ON e.id_usuario = v.id_usuario
        WHERE v.id_comunicado = :id_comunicado
        ORDER BY v.fec_visto DESC
    ");
    $sentencia->execute([':id_comunicado' => $id_comunicado]);
    $lectores = $sentencia->fetchAll(PDO::FETCH_OBJ);

    foreach ($lectores as $lector) {
        $lector->id_usuario = htmlspecialchars((string) $lector->id_usuario, ENT_QUOTES, 'UTF-8');
        $lector->nombre_completo = htmlspecialchars((string) $lector->nombre_completo, ENT_QUOTES, 'UTF-8');
        $lector->foto_perfil = !empty($lector->foto_perfil)
            ? htmlspecialchars((string) $lector->foto_perfil, ENT_QUOTES, 'UTF-8')
            : null;
        $lector->fec_visto = $lector->fec_visto
            ? htmlspecialchars((string) $lector->fec_visto, ENT_QUOTES, 'UTF-8')
            : null;
    }

    echo json_encode(['ok' => true, 'lectores' => $lectores]);
    exit();
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Error al consultar los lectores']);
    exit();
}
