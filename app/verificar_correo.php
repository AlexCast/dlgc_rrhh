<?php
/**
 * verificar_correo.php
 * Valida el token de verificación de correo y activa la cuenta del usuario.
 */

declare(strict_types=1);

require_once __DIR__ . '/session_bootstrap.php';
require_once __DIR__ . '/conexion.php';

$urlLogin = '/templates/login.php';

function redirigirLogin(array $parametros): void
{
    global $urlLogin;
    header('Location: ' . $urlLogin . '?' . http_build_query($parametros));
    exit;
}

$token = trim($_GET['token'] ?? '');

if ($token === '' || !ctype_xdigit($token) || strlen($token) !== 64) {
    redirigirLogin([
        'tab' => 'login',
        'status' => 'error',
        'code' => 'token_invalido',
    ]);
}

$tokenHash = hash('sha256', $token);

try {
    $sentencia = $conexion->prepare(
        "SELECT vc.id_verificacion, vc.id_registro_pendiente, vc.fecha_expiracion, vc.fecha_uso, vc.fec_delete,
                rp.id_usuario, rp.id_rol, rp.username, rp.tipo_documento, rp.primer_nombre, rp.segundo_nombre,
                rp.primer_apellido, rp.segundo_apellido, rp.correo, rp.contrasena
         FROM t_verificacion_correo vc
         INNER JOIN t_registros_pendientes rp ON rp.id_registro_pendiente = vc.id_registro_pendiente
         WHERE vc.token_hash = ?
         LIMIT 1"
    );
    $sentencia->execute([$tokenHash]);
    $verificacion = $sentencia->fetch();

    if (!$verificacion) {
        redirigirLogin([
            'tab' => 'login',
            'status' => 'error',
            'code' => 'token_invalido',
        ]);
    }

    $ahora = new DateTime();
    $expiracion = new DateTime($verificacion['fecha_expiracion']);

    if (
        $verificacion['fecha_uso'] !== null ||
        $verificacion['fec_delete'] !== null ||
        $ahora > $expiracion
    ) {
        redirigirLogin([
            'tab' => 'login',
            'status' => 'error',
            'code' => 'token_expirado',
        ]);
    }

    // Crear usuario real a partir del registro pendiente.
    $sentenciaInsert = $conexion->prepare(
        'SELECT fun_insert_usuarios(
            ?, ?, ?, ?, ?,
            ?, ?, ?,
            ?, ?
         ) AS resultado'
    );
    $sentenciaInsert->execute([
        $verificacion['id_usuario'],
        $verificacion['id_rol'],
        $verificacion['username'],
        $verificacion['tipo_documento'],
        $verificacion['primer_nombre'],
        $verificacion['primer_apellido'],
        $verificacion['correo'],
        $verificacion['contrasena'],
        $verificacion['segundo_nombre'],
        $verificacion['segundo_apellido'],
    ]);
    $resultado = $sentenciaInsert->fetchColumn();

    $mensajeExito = 'OK';
    if ($resultado !== $mensajeExito) {
        // Si el usuario ya existe, es posible que un intento previo haya
        // creado la fila en t_usuarios pero no haya marcado el token como
        // usado (por ejemplo, por un problema de codificación en la
        // respuesta de la función). En ese caso, completamos la activación.
        $sentenciaExiste = $conexion->prepare(
            "SELECT id_usuario
             FROM t_usuarios
             WHERE id_usuario = ?
               AND correo = ?
             LIMIT 1"
        );
        $sentenciaExiste->execute([
            $verificacion['id_usuario'],
            $verificacion['correo'],
        ]);
        $usuarioExistente = $sentenciaExiste->fetch();

        if (!$usuarioExistente) {
            error_log('Error creando usuario tras verificación: ' . $resultado);
            redirigirLogin([
                'tab' => 'login',
                'status' => 'error',
                'code' => 'error_servidor',
            ]);
        }
    }

    // Marcar correo como verificado.
    $actualizar = $conexion->prepare(
        "UPDATE t_usuarios
         SET correo_verificado = TRUE,
             usr_update = ?,
             fec_update = CURRENT_TIMESTAMP
         WHERE id_usuario = ?"
    );
    $actualizar->execute([
        $verificacion['id_usuario'],
        $verificacion['id_usuario'],
    ]);

    // Marcar token como usado (los triggers borrarán token y registro pendiente).
    // Se usa transacción para garantizar atomicidad.
    $conexion->beginTransaction();

    try {
        $usarToken = $conexion->prepare(
            "UPDATE t_verificacion_correo
             SET fecha_uso = CURRENT_TIMESTAMP,
                 usr_update = ?,
                 fec_update = CURRENT_TIMESTAMP
             WHERE id_verificacion = ?"
        );
        $usarToken->execute([
            $verificacion['id_usuario'],
            $verificacion['id_verificacion'],
        ]);

        $conexion->commit();
    } catch (PDOException $e) {
        $conexion->rollBack();
        throw $e;
    }

    redirigirLogin([
        'tab' => 'login',
        'status' => 'success',
        'code' => 'correo_verificado',
    ]);

} catch (PDOException $e) {
    error_log('Error en verificación de correo: ' . $e->getMessage());
    redirigirLogin([
        'tab' => 'login',
        'status' => 'error',
        'code' => 'error_servidor',
    ]);
}
