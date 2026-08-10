<?php
declare(strict_types=1);

/**
 * Consulta de saldo de vacaciones (ciclo aniversario vigente) y su historial de ajustes manuales.
 * - Sin ?id_empleado: cada usuario consulta su propio saldo.
 * - Con ?id_empleado=X: solo permitido a usuarios con acceso al módulo 29 (Gestión de Vacaciones).
 * - ?accion=empleados (solo RRHH): lista de empleados activos para el selector de la UI.
 */

require_once __DIR__ . '/session_bootstrap.php';
require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/auth_guard.php';

header('Content-Type: application/json; charset=utf-8');

function responderJson(bool $success, string $message, int $httpCode = 200, array $extra = []): void
{
    http_response_code($httpCode);
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $extra), JSON_UNESCAPED_UNICODE);
    exit;
}

$idUsuario = $_SESSION['id_usuario'] ?? null;
if (!is_string($idUsuario) || trim($idUsuario) === '') {
    responderJson(false, 'Sesión inválida.', 401);
}

$esRrhh = has_module_access(29);
$accion = strtolower(trim($_GET['accion'] ?? ''));

if ($accion === 'empleados') {
    if (!$esRrhh) {
        responderJson(false, 'No tienes acceso al módulo de RRHH.', 403);
    }

    try {
        $sentencia = $conexion->prepare(
            "SELECT e.id_usuario, u.primer_nombre, u.primer_apellido
               FROM t_empleados e
               INNER JOIN t_usuarios u ON u.id_usuario = e.id_usuario
              WHERE e.fec_delete IS NULL AND (e.fecha_egreso IS NULL OR e.fecha_egreso > CURRENT_DATE)
              ORDER BY u.primer_apellido, u.primer_nombre"
        );
        $sentencia->execute();
        responderJson(true, 'Empleados listados correctamente.', 200, ['empleados' => $sentencia->fetchAll(PDO::FETCH_ASSOC)]);
    } catch (Throwable $e) {
        error_log('Error en vacaciones_saldo.php (empleados): ' . $e->getMessage());
        responderJson(false, 'Ocurrió un error al listar los empleados.', 500);
    }
}

$idEmpleado = trim($_GET['id_empleado'] ?? '');
if ($idEmpleado === '') {
    $idEmpleado = $idUsuario;
} elseif ($idEmpleado !== $idUsuario && !$esRrhh) {
    responderJson(false, 'No tienes permiso para consultar el saldo de otro empleado.', 403);
}

try {
    $sentenciaSaldo = $conexion->prepare('SELECT * FROM fun_calcular_saldo_vacaciones(:id_empleado)');
    $sentenciaSaldo->execute([':id_empleado' => $idEmpleado]);
    $saldo = $sentenciaSaldo->fetch(PDO::FETCH_ASSOC);

    if ($saldo === false) {
        responderJson(false, 'No se encontró el empleado indicado.', 404);
    }

    $ajustes = [];
    if ($esRrhh) {
        $sentenciaAjustes = $conexion->prepare('SELECT * FROM fun_listar_vacaciones_ajustes(:id_empleado)');
        $sentenciaAjustes->execute([':id_empleado' => $idEmpleado]);
        $ajustes = $sentenciaAjustes->fetchAll(PDO::FETCH_ASSOC);
    }

    responderJson(true, 'Saldo consultado correctamente.', 200, ['saldo' => $saldo, 'ajustes' => $ajustes]);
} catch (Throwable $e) {
    error_log('Error en vacaciones_saldo.php: ' . $e->getMessage());
    responderJson(false, 'Ocurrió un error al consultar el saldo de vacaciones.', 500);
}
