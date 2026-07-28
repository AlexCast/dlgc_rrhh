<?php
/**
 * login.php
 * Autenticación de usuarios contra t_usuarios, con verificación de hash,
 * regeneración de sesión y carga de permisos por módulo.
 */

session_start();

require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/csrf_guard.php';

$urlLogin = '/dlgc_rrhh/templates/login.php';

function redirigirLogin(array $parametros): void
{
    global $urlLogin;
    header('Location: ' . $urlLogin . '?' . http_build_query($parametros));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirigirLogin([
        'tab' => 'login',
        'status' => 'error',
        'code' => 'metodo_no_valido',
    ]);
}

// Validate CSRF token for login.
csrf_validate();

// --- 1. Recepción y normalización de credenciales enviadas por POST ---
$userInput  = trim($_POST['user'] ?? '');
$contrasena = $_POST['contrasena'] ?? '';

// Normalizar usuario/correo a minúsculas y limpiar espacios.
$userInput = strtolower(trim($userInput));

if ($userInput === '' || $contrasena === '') {
    redirigirLogin([
        'tab' => 'login',
        'status' => 'error',
        'code' => 'campos_obligatorios',
    ]);
}

if (strlen($userInput) > 40 || strlen($contrasena) > 255) {
    redirigirLogin([
        'tab' => 'login',
        'status' => 'error',
        'code' => 'credenciales_invalidas',
    ]);
}

// --- 2. Búsqueda del usuario mediante la función del motor fun_login_usuarios ---
// (acepta tanto username como correo, la función resuelve la coincidencia)
try {
    $sentencia = $conexion->prepare('SELECT * FROM fun_login_usuarios(:userInput)');
    $sentencia->execute([':userInput' => $userInput]);
    $usuario = $sentencia->fetch();

    // --- 3. Verificación del hash de la contraseña ---
    if (!$usuario || !password_verify($contrasena, $usuario['contrasena'])) {
        redirigirLogin([
            'tab' => 'login',
            'status' => 'error',
            'code' => 'credenciales_invalidas',
            'user' => $userInput,
        ]);
    }

    // --- 3b. Verificación de correo electrónico ---
    if (empty($usuario['correo_verificado'])) {
        redirigirLogin([
            'tab' => 'login',
            'status' => 'error',
            'code' => 'correo_no_verificado',
            'user' => $userInput,
        ]);
    }

    // --- 4. Prevención de fijación de sesión ---
    session_regenerate_id(true);

    // --- 4b. Regenerar token CSRF tras autenticación exitosa ---
    csrf_regenerate_token();

    // --- 5. Consulta de permisos por módulo (rol -> operaciones -> módulos) ---
    $sentenciaPermisos = $conexion->prepare(
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
    $sentenciaPermisos->execute([':id_usuario' => $usuario['id_usuario']]);
    $modulosPermisosRol = $sentenciaPermisos->fetchAll();

    // --- 5b. Consulta de permisos individuales del usuario ---
    $sentenciaPermisosUsuario = $conexion->prepare(
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
    $sentenciaPermisosUsuario->execute([':id_usuario' => $usuario['id_usuario']]);
    $modulosPermisosUsuario = $sentenciaPermisosUsuario->fetchAll();

    // Fusionar permisos de rol y permisos individuales (suma/OR)
    $modulosPermisos = [];
    foreach ($modulosPermisosRol as $modulo) {
        $modulosPermisos[$modulo['id_modulo']] = [
            'nombre_modulo'       => $modulo['nombre_modulo'],
            'permiso_crear'       => (bool) $modulo['permiso_crear'],
            'permiso_actualizar'  => (bool) $modulo['permiso_actualizar'],
            'permiso_eliminar'    => (bool) $modulo['permiso_eliminar'],
            'permiso_restaurar'   => (bool) $modulo['permiso_restaurar'],
        ];
    }
    foreach ($modulosPermisosUsuario as $modulo) {
        $id = $modulo['id_modulo'];
        if (!isset($modulosPermisos[$id])) {
            $modulosPermisos[$id] = [
                'nombre_modulo'       => $modulo['nombre_modulo'],
                'permiso_crear'       => (bool) $modulo['permiso_crear'],
                'permiso_actualizar'  => (bool) $modulo['permiso_actualizar'],
                'permiso_eliminar'    => (bool) $modulo['permiso_eliminar'],
                'permiso_restaurar'   => (bool) $modulo['permiso_restaurar'],
            ];
        } else {
            $modulosPermisos[$id]['permiso_crear']      = $modulosPermisos[$id]['permiso_crear']      || (bool) $modulo['permiso_crear'];
            $modulosPermisos[$id]['permiso_actualizar'] = $modulosPermisos[$id]['permiso_actualizar'] || (bool) $modulo['permiso_actualizar'];
            $modulosPermisos[$id]['permiso_eliminar']   = $modulosPermisos[$id]['permiso_eliminar']   || (bool) $modulo['permiso_eliminar'];
            $modulosPermisos[$id]['permiso_restaurar']  = $modulosPermisos[$id]['permiso_restaurar']  || (bool) $modulo['permiso_restaurar'];
        }
    }

    // --- 6. Almacenamiento de datos de sesión ---
    $_SESSION['id_usuario']    = $usuario['id_usuario'];
    $_SESSION['username']      = $usuario['username'];
    $_SESSION['id_rol']        = isset($usuario['id_rol']) ? (int) $usuario['id_rol'] : null;
    
    // --- NUEVO: Construimos y guardamos el nombre completo para el dashboard ---
    $nombre = $usuario['primer_nombre'] ?? '';
    $apellido = $usuario['primer_apellido'] ?? '';
    $nombreCompleto = trim($nombre . ' ' . $apellido);
    
    // Si la BD no trajo nombres, usamos el username como respaldo
    $_SESSION['nombre_completo'] = $nombreCompleto !== '' ? $nombreCompleto : $usuario['username'];

    // Arreglo multidimensional indexado por id_modulo para validación rápida
    $_SESSION['permisos'] = [];
    foreach ($modulosPermisos as $id_modulo => $permisos) {
        $_SESSION['permisos'][$id_modulo] = [
            'nombre_modulo'       => $permisos['nombre_modulo'],
            'permiso_crear'       => (bool) $permisos['permiso_crear'],
            'permiso_actualizar'  => (bool) $permisos['permiso_actualizar'],
            'permiso_eliminar'    => (bool) $permisos['permiso_eliminar'],
            'permiso_restaurar'   => (bool) $permisos['permiso_restaurar'],
        ];
    }

    // Variables de seguridad requeridas por auth_guard.php
    $_SESSION['user_id'] = $usuario['id_usuario'];
    $_SESSION['login_user_agent'] = $_SERVER['HTTP_USER_AGENT'];
    $_SESSION['last_activity_timestamp'] = time();

    // --- 7. Redirección al dashboard tras login exitoso ---
    // Administradores van al panel de RRHH; el resto al dashboard de empleado.
    $dashboardUrl = ($_SESSION['id_rol'] === 1)
        ? '/dlgc_rrhh/templates/secondpage.php'
        : '/dlgc_rrhh/templates/firstpage.php';

    header('Location: ' . $dashboardUrl);
    exit;

} catch (PDOException $e) {
    error_log('Error en el proceso de login: ' . $e->getMessage());
    redirigirLogin([
        'tab' => 'login',
        'status' => 'error',
        'code' => 'error_servidor',
    ]);
}