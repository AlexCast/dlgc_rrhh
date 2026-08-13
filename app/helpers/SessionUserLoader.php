<?php
declare(strict_types=1);

/**
 * Carga en $_SESSION los datos básicos y permisos de un usuario ya autenticado.
 *
 * Se usa desde app/login.php tras la contraseña y desde app/auth_guard.php
 * cuando se reautentica vía cookie "Recordarme".
 */

require_once __DIR__ . '/../conexion.php';

/**
 * Carga los datos del usuario y sus permisos en la sesión activa.
 *
 * @param PDO $conexion Conexión activa a PostgreSQL.
 * @param string $idUsuario ID del usuario.
 * @return bool true si se cargó correctamente, false si el usuario no existe o está inactivo.
 */
function load_user_session(PDO $conexion, string $idUsuario): bool
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        throw new RuntimeException('La sesión debe estar activa para cargar el usuario.');
    }

    try {
        $stmt = $conexion->prepare(
            "SELECT id_usuario, username, id_rol, primer_nombre, primer_apellido, correo_verificado
             FROM t_usuarios
             WHERE id_usuario = :id_usuario
               AND fec_delete IS NULL"
        );
        $stmt->execute([':id_usuario' => $idUsuario]);
        $usuario = $stmt->fetch();

        if (!$usuario || empty($usuario['correo_verificado'])) {
            return false;
        }

        $permisos = merge_module_permissions(
            fetch_module_permissions_by_role($conexion, $idUsuario),
            fetch_module_permissions_by_user($conexion, $idUsuario)
        );

        $nombreCompleto = trim(($usuario['primer_nombre'] ?? '') . ' ' . ($usuario['primer_apellido'] ?? ''));
        if ($nombreCompleto === '') {
            $nombreCompleto = $usuario['username'];
        }

        $_SESSION['id_usuario']    = $usuario['id_usuario'];
        $_SESSION['username']      = $usuario['username'];
        $_SESSION['id_rol']        = isset($usuario['id_rol']) ? (int) $usuario['id_rol'] : null;
        $_SESSION['nombre_completo'] = $nombreCompleto;
        $_SESSION['user_id']       = $usuario['id_usuario'];
        $_SESSION['login_user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $_SESSION['last_activity_timestamp'] = time();
        $_SESSION['permisos']      = $permisos;

        return true;
    } catch (PDOException $e) {
        error_log('Error al cargar sesión de usuario: ' . $e->getMessage());
        return false;
    }
}

/**
 * Obtiene permisos por módulo derivados del rol del usuario.
 *
 * @return array<int, array<string, mixed>>
 */
function fetch_module_permissions_by_role(PDO $conexion, string $idUsuario): array
{
    $stmt = $conexion->prepare(
        "SELECT
            m.id_modulo,
            m.nombre_modulo,
            BOOL_OR(
                UPPER(o.nombre_operacion) IN ('CREAR', 'INSERTAR', 'NUEVO', 'NUEVA')
                OR UPPER(o.nombre_operacion) LIKE 'CREAR%'
                OR UPPER(o.nombre_operacion) LIKE 'INSERTAR%'
            ) AS permiso_crear,
            BOOL_OR(
                UPPER(o.nombre_operacion) IN ('ACTUALIZAR', 'EDITAR', 'MODIFICAR')
                OR UPPER(o.nombre_operacion) LIKE 'ACTUALIZAR%'
                OR UPPER(o.nombre_operacion) LIKE 'EDITAR%'
                OR UPPER(o.nombre_operacion) LIKE 'MODIFICAR%'
            ) AS permiso_actualizar,
            BOOL_OR(
                UPPER(o.nombre_operacion) IN ('ELIMINAR', 'BORRAR')
                OR UPPER(o.nombre_operacion) LIKE 'ELIMINAR%'
                OR UPPER(o.nombre_operacion) LIKE 'BORRAR%'
            ) AS permiso_eliminar,
            BOOL_OR(
                UPPER(o.nombre_operacion) IN ('RESTAURAR', 'RECUPERAR', 'REACTIVAR')
                OR UPPER(o.nombre_operacion) LIKE 'RESTAURAR%'
                OR UPPER(o.nombre_operacion) LIKE 'RECUPERAR%'
                OR UPPER(o.nombre_operacion) LIKE 'REACTIVAR%'
            ) AS permiso_restaurar
         FROM t_usuarios u
         INNER JOIN t_roles_operaciones ro ON ro.id_rol = u.id_rol AND ro.fec_delete IS NULL
         INNER JOIN t_operaciones o ON o.id_operacion = ro.id_operacion AND o.fec_delete IS NULL
         INNER JOIN t_modulos m ON m.id_modulo = o.id_modulo AND m.fec_delete IS NULL
         WHERE u.id_usuario = :id_usuario
           AND u.fec_delete IS NULL
         GROUP BY m.id_modulo, m.nombre_modulo"
    );
    $stmt->execute([':id_usuario' => $idUsuario]);
    return normalize_permissions($stmt->fetchAll());
}

/**
 * Obtiene permisos por módulo asignados directamente al usuario.
 *
 * @return array<int, array<string, mixed>>
 */
function fetch_module_permissions_by_user(PDO $conexion, string $idUsuario): array
{
    $stmt = $conexion->prepare(
        "SELECT
            m.id_modulo,
            m.nombre_modulo,
            BOOL_OR(
                UPPER(o.nombre_operacion) IN ('CREAR', 'INSERTAR', 'NUEVO', 'NUEVA')
                OR UPPER(o.nombre_operacion) LIKE 'CREAR%'
                OR UPPER(o.nombre_operacion) LIKE 'INSERTAR%'
            ) AS permiso_crear,
            BOOL_OR(
                UPPER(o.nombre_operacion) IN ('ACTUALIZAR', 'EDITAR', 'MODIFICAR')
                OR UPPER(o.nombre_operacion) LIKE 'ACTUALIZAR%'
                OR UPPER(o.nombre_operacion) LIKE 'EDITAR%'
                OR UPPER(o.nombre_operacion) LIKE 'MODIFICAR%'
            ) AS permiso_actualizar,
            BOOL_OR(
                UPPER(o.nombre_operacion) IN ('ELIMINAR', 'BORRAR')
                OR UPPER(o.nombre_operacion) LIKE 'ELIMINAR%'
                OR UPPER(o.nombre_operacion) LIKE 'BORRAR%'
            ) AS permiso_eliminar,
            BOOL_OR(
                UPPER(o.nombre_operacion) IN ('RESTAURAR', 'RECUPERAR', 'REACTIVAR')
                OR UPPER(o.nombre_operacion) LIKE 'RESTAURAR%'
                OR UPPER(o.nombre_operacion) LIKE 'RECUPERAR%'
                OR UPPER(o.nombre_operacion) LIKE 'REACTIVAR%'
            ) AS permiso_restaurar
         FROM t_usuarios_operaciones uo
         INNER JOIN t_operaciones o ON o.id_operacion = uo.id_operacion AND o.fec_delete IS NULL
         INNER JOIN t_modulos m ON m.id_modulo = o.id_modulo AND m.fec_delete IS NULL
         WHERE uo.id_usuario = :id_usuario
           AND uo.fec_delete IS NULL
         GROUP BY m.id_modulo, m.nombre_modulo"
    );
    $stmt->execute([':id_usuario' => $idUsuario]);
    return normalize_permissions($stmt->fetchAll());
}

/**
 * Combina permisos por rol y por usuario preservando id_modulo como clave.
 *
 * IMPORTANTE: NO usar array_merge() aquí. Las claves de estos arreglos son
 * id_modulo (enteros), y array_merge() reindexa/renumera las claves numéricas
 * en vez de fusionarlas, lo que rompe has_module_access()/has_module_permission()
 * para cualquier módulo cuya posición secuencial no coincida con su id real.
 *
 * @param array<int, array<string, mixed>> $porRol
 * @param array<int, array<string, mixed>> $porUsuario
 * @return array<int, array<string, mixed>>
 */
function merge_module_permissions(array $porRol, array $porUsuario): array
{
    $resultado = $porRol;
    foreach ($porUsuario as $idModulo => $permisosModulo) {
        if (!array_key_exists($idModulo, $resultado)) {
            $resultado[$idModulo] = $permisosModulo;
            continue;
        }

        $resultado[$idModulo]['nombre_modulo']      = $resultado[$idModulo]['nombre_modulo'] ?? $permisosModulo['nombre_modulo'];
        $resultado[$idModulo]['permiso_crear']      = $resultado[$idModulo]['permiso_crear']      || $permisosModulo['permiso_crear'];
        $resultado[$idModulo]['permiso_actualizar'] = $resultado[$idModulo]['permiso_actualizar'] || $permisosModulo['permiso_actualizar'];
        $resultado[$idModulo]['permiso_eliminar']   = $resultado[$idModulo]['permiso_eliminar']   || $permisosModulo['permiso_eliminar'];
        $resultado[$idModulo]['permiso_restaurar']  = $resultado[$idModulo]['permiso_restaurar']  || $permisosModulo['permiso_restaurar'];
    }

    return $resultado;
}

/**
 * Normaliza un arreglo de filas de permisos al formato usado en sesión.
 *
 * @param array<int, array<string, mixed>> $rows
 * @return array<int, array<string, mixed>>
 */
function normalize_permissions(array $rows): array
{
    $resultado = [];
    foreach ($rows as $fila) {
        $id = (int) $fila['id_modulo'];
        $existente = $resultado[$id] ?? null;

        if ($existente === null) {
            $resultado[$id] = [
                'nombre_modulo'      => $fila['nombre_modulo'],
                'permiso_crear'      => (bool) $fila['permiso_crear'],
                'permiso_actualizar' => (bool) $fila['permiso_actualizar'],
                'permiso_eliminar'   => (bool) $fila['permiso_eliminar'],
                'permiso_restaurar'  => (bool) $fila['permiso_restaurar'],
            ];
        } else {
            $resultado[$id]['permiso_crear']      = $existente['permiso_crear']      || (bool) $fila['permiso_crear'];
            $resultado[$id]['permiso_actualizar'] = $existente['permiso_actualizar'] || (bool) $fila['permiso_actualizar'];
            $resultado[$id]['permiso_eliminar']   = $existente['permiso_eliminar']   || (bool) $fila['permiso_eliminar'];
            $resultado[$id]['permiso_restaurar']  = $existente['permiso_restaurar']  || (bool) $fila['permiso_restaurar'];
        }
    }
    return $resultado;
}
