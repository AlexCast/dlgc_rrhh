<?php

$moduleId = 23;
require_once __DIR__ . '/src_guard.php';
require_once __DIR__ . '/conexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /dlgc_rrhh/templates/codigos_registro.php');
    exit;
}

csrf_validate();

$usuarioSesion = $_SESSION['id_usuario'] ?? 'sistema';
$accion = $_POST['accion'] ?? '';

function generarCodigo(PDO $conexion): string
{
    $stmtExiste = $conexion->prepare("SELECT COUNT(*) FROM t_codigos_registro WHERE codigo = :codigo");
    do {
        $codigo = str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);
        $stmtExiste->execute([':codigo' => $codigo]);
        $existe = (int) $stmtExiste->fetchColumn() > 0;
    } while ($existe);
    return $codigo;
}

function setFlash(string $mensaje, string $tipo = 'success'): void
{
    $_SESSION['flash_mensaje'] = $mensaje;
    $_SESSION['flash_tipo'] = $tipo;
}

try {
    switch ($accion) {
        case 'generar_temporal':
            if (!has_module_permission($moduleId, 'crear')) {
                setFlash('No tienes permiso para generar códigos temporales.', 'error');
                break;
            }

            $dias = (int) ($_POST['dias'] ?? 7);
            if ($dias < 1) {
                $dias = 1;
            }
            if ($dias > 7) {
                $dias = 7;
            }
            $expiracion = (new DateTime("+{$dias} days"))->format('Y-m-d H:i:s');
            $nuevoCodigo = generarCodigo($conexion);

            $conexion->prepare(
                "UPDATE t_codigos_registro
                 SET fec_delete = CURRENT_TIMESTAMP, usr_delete = :usr_delete
                 WHERE tipo = 'TEMPORAL' AND fec_delete IS NULL"
            )->execute([':usr_delete' => $usuarioSesion]);

            $conexion->prepare(
                "INSERT INTO t_codigos_registro (codigo, tipo, fecha_expiracion, usr_insert, fec_insert)
                 VALUES (:codigo, 'TEMPORAL', :expiracion, :usr_insert, CURRENT_TIMESTAMP)"
            )->execute([
                ':codigo' => $nuevoCodigo,
                ':expiracion' => $expiracion,
                ':usr_insert' => $usuarioSesion,
            ]);

            setFlash("Código temporal generado: {$nuevoCodigo}. Vence el {$expiracion}.");
            break;

        case 'cancelar_temporal':
            if (!has_module_permission($moduleId, 'eliminar')) {
                setFlash('No tienes permiso para cancelar códigos temporales.', 'error');
                break;
            }

            $afectados = $conexion->prepare(
                "UPDATE t_codigos_registro
                 SET fec_delete = CURRENT_TIMESTAMP, usr_delete = :usr_delete
                 WHERE tipo = 'TEMPORAL' AND fec_delete IS NULL"
            );
            $afectados->execute([':usr_delete' => $usuarioSesion]);

            if ($afectados->rowCount() > 0) {
                setFlash('Código temporal cancelado correctamente.');
            } else {
                setFlash('No había código temporal activo para cancelar.', 'error');
            }
            break;

        case 'generar_unico':
            if (!has_module_permission($moduleId, 'crear')) {
                setFlash('No tienes permiso para generar códigos únicos.', 'error');
                break;
            }

            $nuevoCodigo = generarCodigo($conexion);

            $conexion->prepare(
                "UPDATE t_codigos_registro
                 SET fec_delete = CURRENT_TIMESTAMP, usr_delete = :usr_delete
                 WHERE tipo = 'UNICO_USO' AND usado = FALSE AND fec_delete IS NULL"
            )->execute([':usr_delete' => $usuarioSesion]);

            $conexion->prepare(
                "INSERT INTO t_codigos_registro (codigo, tipo, usr_insert, fec_insert)
                 VALUES (:codigo, 'UNICO_USO', :usr_insert, CURRENT_TIMESTAMP)"
            )->execute([
                ':codigo' => $nuevoCodigo,
                ':usr_insert' => $usuarioSesion,
            ]);

            setFlash("Código único generado: {$nuevoCodigo}.");
            break;

        case 'cancelar_unico':
            if (!has_module_permission($moduleId, 'eliminar')) {
                setFlash('No tienes permiso para cancelar códigos únicos.', 'error');
                break;
            }

            $afectados = $conexion->prepare(
                "UPDATE t_codigos_registro
                 SET fec_delete = CURRENT_TIMESTAMP, usr_delete = :usr_delete
                 WHERE tipo = 'UNICO_USO' AND usado = FALSE AND fec_delete IS NULL"
            );
            $afectados->execute([':usr_delete' => $usuarioSesion]);

            if ($afectados->rowCount() > 0) {
                setFlash('Código único cancelado correctamente.');
            } else {
                setFlash('No había código único activo para cancelar.', 'error');
            }
            break;

        default:
            setFlash('Acción no reconocida.', 'error');
    }
} catch (PDOException $e) {
    error_log('Error en accion_codigos_registro: ' . $e->getMessage());
    setFlash('Ocurrió un error al procesar la acción.', 'error');
}

header('Location: /dlgc_rrhh/templates/codigos_registro.php');
exit;
