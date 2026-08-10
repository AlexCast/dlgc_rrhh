<?php
/**
 * register.php
 * Activación/registro de cuentas en t_usuarios: valida los datos recibidos
 * del formulario, verifica el formato del documento según tipo_documento,
 * hashea la contraseña y realiza el INSERT mediante sentencia preparada.
 */

require_once __DIR__ . '/session_bootstrap.php';
require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/csrf_guard.php';
require_once __DIR__ . '/helpers/Mailer.php';
require_once __DIR__ . '/helpers/RateLimiter.php';
require_once __DIR__ . '/helpers/InputSanitizer.php';

$urlLogin = '/dlgc_rrhh/templates/login.php';
$usrInsert = 'sistema_registro';

function redirigirRegistro(array $parametros): void
{
    global $urlLogin;
    header('Location: ' . $urlLogin . '?' . http_build_query($parametros));
    exit;
}

/**
 * Guarda los campos del paso 2 de registro en sesión para rellenarlos
 * si la validación falla. No almacena contraseñas.
 */
function guardarDatosRegistroEnSesion(array $datos): void
{
    $camposPermitidos = [
        'username', 'tipo_documento', 'id_usuario', 'primer_nombre',
        'segundo_nombre', 'primer_apellido', 'segundo_apellido', 'correo',
        'accept_terms', 'accept_privacy',
    ];

    $_SESSION['register_form_data'] = [];
    foreach ($camposPermitidos as $campo) {
        $_SESSION['register_form_data'][$campo] = $datos[$campo] ?? '';
    }
}

function generarCodigoRegistro(PDO $conexion): string
{
    $stmtExiste = $conexion->prepare("SELECT COUNT(*) FROM t_codigos_registro WHERE codigo = :codigo");
    do {
        $codigo = str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);
        $stmtExiste->execute([':codigo' => $codigo]);
        $existe = (int) $stmtExiste->fetchColumn() > 0;
    } while ($existe);
    return $codigo;
}

// Endpoint interno para limpiar el borrador del formulario de registro
// cuando el usuario pulsa "Volver al código".
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['clear_register_form_cache'])) {
    csrf_validate();
    unset($_SESSION['register_form_data']);
    http_response_code(204);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirigirRegistro([
        'tab' => 'register',
        'status' => 'error',
        'code' => 'metodo_no_valido',
    ]);
}

// Validate CSRF token for registration.
csrf_validate();

// --- Flujo de dos pasos: validar código antes de mostrar el formulario ---
$codigoIngresado = strtoupper(trim($_POST['codigo_registro'] ?? ''));
$esValidacionCodigo = $codigoIngresado !== '' && empty(trim($_POST['username'] ?? ''));

if ($esValidacionCodigo) {
    $stmtCodigo = $conexion->prepare(
        "SELECT id_codigo, tipo, fecha_expiracion
         FROM t_codigos_registro
         WHERE codigo = :codigo
           AND fec_delete IS NULL
           AND (
                (tipo = 'TEMPORAL' AND (fecha_expiracion IS NULL OR fecha_expiracion >= CURRENT_TIMESTAMP))
                OR
                (tipo = 'UNICO_USO' AND usado = FALSE)
           )"
    );
    $stmtCodigo->execute([':codigo' => $codigoIngresado]);
    $codigoBD = $stmtCodigo->fetch(PDO::FETCH_ASSOC);

    if (!$codigoBD) {
        redirigirRegistro([
            'tab' => 'register',
            'status' => 'error',
            'code' => 'codigo_registro_invalido',
            'msg' => 'El código de acceso no es válido, ya fue usado o expiró.',
        ]);
    }

    $_SESSION['codigo_registro_validado'] = true;
    $_SESSION['codigo_registro'] = $codigoIngresado;
    $_SESSION['codigo_registro_tipo'] = $codigoBD['tipo'];
    $_SESSION['codigo_registro_id'] = $codigoBD['id_codigo'];

    redirigirRegistro([
        'tab' => 'register',
        'step' => '2',
        'status' => 'success',
        'code' => 'codigo_valido',
    ]);
}

// --- 1. Recepción y saneamiento de los datos del formulario ---
$username         = InputSanitizer::username($_POST['username'] ?? '');
$tipoDocumento    = InputSanitizer::text($_POST['tipo_documento'] ?? '') ?? '';
$idUsuario        = InputSanitizer::text($_POST['id_usuario'] ?? '') ?? '';
$primerNombre     = InputSanitizer::text($_POST['primer_nombre'] ?? '') ?? '';
$segundoNombre    = InputSanitizer::text($_POST['segundo_nombre'] ?? '');
$primerApellido   = InputSanitizer::text($_POST['primer_apellido'] ?? '') ?? '';
$segundoApellido  = InputSanitizer::text($_POST['segundo_apellido'] ?? '');
$correo           = InputSanitizer::email($_POST['correo'] ?? '');
$contrasena       = $_POST['contrasena'] ?? '';
$confirmarContrasena = $_POST['confirmar_contrasena'] ?? '';

// --- 2. Validaciones de campos obligatorios ---
$errores = [];

if (
    $username === ''
    || strlen($username) < 3
    || strlen($username) > 30
    || !preg_match('/^[a-z][a-z0-9_.-]{2,29}$/', $username)
) {
    $errores[] = 'El usuario debe tener entre 3 y 30 caracteres, comenzar con una letra minúscula y solo puede contener letras minúsculas, números, puntos, guiones bajos y guiones. Sin espacios ni caracteres especiales.';
}

$tiposValidos = ['CC', 'PPT', 'CE'];
if (!in_array($tipoDocumento, $tiposValidos, true)) {
    $errores[] = 'Tipo de documento inválido.';
}

// Formato de documento según tipo (igual al CHECK de la base de datos)
$patronesDocumento = [
    'CC'  => '/^[0-9]{10}$/',
    'PPT' => '/^[A-Z0-9-]{5,20}$/',
    'CE'  => '/^[A-Z0-9]{5,20}$/',
];
$mensajesDocumento = [
    'CC'  => 'La cédula de ciudadanía debe tener exactamente 10 dígitos numéricos.',
    'PPT' => 'El pasaporte debe tener entre 5 y 20 caracteres alfanuméricos o guiones.',
    'CE'  => 'La cédula de extranjería debe tener entre 5 y 20 caracteres alfanuméricos.',
];
if (isset($patronesDocumento[$tipoDocumento]) && !preg_match($patronesDocumento[$tipoDocumento], $idUsuario)) {
    $errores[] = $mensajesDocumento[$tipoDocumento];
}

if ($primerNombre === '' || strlen($primerNombre) > 30) {
    $errores[] = 'El primer nombre es obligatorio y debe tener máximo 30 caracteres.';
}

if ($primerApellido === '' || strlen($primerApellido) > 30) {
    $errores[] = 'El primer apellido es obligatorio y debe tener máximo 30 caracteres.';
}

if (!InputSanitizer::validateEmail($correo) || strlen($correo) > 40) {
    $errores[] = 'El correo electrónico no es válido.';
}

$validacionContrasena = InputSanitizer::validatePassword($contrasena, $confirmarContrasena);
if (!$validacionContrasena['valid']) {
    foreach ($validacionContrasena['errors'] as $errorContrasena) {
        $errores[] = $errorContrasena;
    }
}

// --- Validación de aceptación de Términos y Política de Privacidad ---
$acceptTerms  = filter_input(INPUT_POST, 'accept_terms', FILTER_VALIDATE_BOOL) ?? false;
$acceptPrivacy = filter_input(INPUT_POST, 'accept_privacy', FILTER_VALIDATE_BOOL) ?? false;

if (!$acceptTerms) {
    $errores[] = 'Debes aceptar los Términos y Condiciones para continuar.';
}

if (!$acceptPrivacy) {
    $errores[] = 'Debes aceptar la Política de Privacidad para continuar.';
}

if (!empty($errores)) {
    guardarDatosRegistroEnSesion([
        'username'         => $username,
        'tipo_documento'   => $tipoDocumento,
        'id_usuario'       => $idUsuario,
        'primer_nombre'    => $primerNombre,
        'segundo_nombre'   => $segundoNombre,
        'primer_apellido'  => $primerApellido,
        'segundo_apellido' => $segundoApellido,
        'correo'           => $correo,
        'accept_terms'     => $acceptTerms ? '1' : '',
        'accept_privacy'   => $acceptPrivacy ? '1' : '',
    ]);

    redirigirRegistro([
        'tab'    => 'register',
        'step'   => '2',
        'status' => 'error',
        'code'   => 'validacion_registro',
        'msg'    => $errores[0],
    ]);
}

// --- Validación del código de acceso al registro ---
$codigoIngresado = strtoupper(trim($_POST['codigo_registro'] ?? $_SESSION['codigo_registro'] ?? ''));

if ($codigoIngresado === '') {
    unset($_SESSION['codigo_registro_validado'], $_SESSION['codigo_registro'], $_SESSION['codigo_registro_tipo'], $_SESSION['codigo_registro_id']);
    redirigirRegistro([
        'tab' => 'register',
        'status' => 'error',
        'code' => 'codigo_registro_invalido',
        'msg' => 'Debes ingresar el código de acceso.',
    ]);
}

$stmtCodigo = $conexion->prepare(
    "SELECT id_codigo, tipo, fecha_expiracion
     FROM t_codigos_registro
     WHERE codigo = :codigo
       AND fec_delete IS NULL
       AND (
            (tipo = 'TEMPORAL' AND (fecha_expiracion IS NULL OR fecha_expiracion >= CURRENT_TIMESTAMP))
            OR
            (tipo = 'UNICO_USO' AND usado = FALSE)
       )"
);
$stmtCodigo->execute([':codigo' => $codigoIngresado]);
$codigoBD = $stmtCodigo->fetch(PDO::FETCH_ASSOC);

if (!$codigoBD) {
    unset($_SESSION['codigo_registro_validado'], $_SESSION['codigo_registro'], $_SESSION['codigo_registro_tipo'], $_SESSION['codigo_registro_id']);
    redirigirRegistro([
        'tab' => 'register',
        'status' => 'error',
        'code' => 'codigo_registro_invalido',
        'msg' => 'El código de acceso no es válido, ya fue usado o expiró.',
    ]);
}

if ($codigoBD['tipo'] === 'UNICO_USO') {
    $conexion->prepare(
        "UPDATE t_codigos_registro
         SET usado = TRUE,
             usr_update = :usr_update,
             fec_update = CURRENT_TIMESTAMP
         WHERE id_codigo = :id_codigo"
    )->execute([
        ':id_codigo' => $codigoBD['id_codigo'],
        ':usr_update' => $usrInsert,
    ]);

    // El trigger tri_borrar_codigo_registro eliminará físicamente la fila marcada como usada.
    // Se genera el siguiente código único automáticamente.
    $nuevoCodigo = generarCodigoRegistro($conexion);
    $conexion->prepare(
        "INSERT INTO t_codigos_registro (codigo, tipo, usr_insert, fec_insert)
         VALUES (:codigo, 'UNICO_USO', :usr_insert, CURRENT_TIMESTAMP)"
    )->execute([
        ':codigo' => $nuevoCodigo,
        ':usr_insert' => $usrInsert,
    ]);
}

try {
    // --- 3.1 Resolución del rol por defecto para nuevos registros ---
    // Prioriza rol EMPLEADO; si no existe, usa el primer rol activo disponible.
    $sentenciaRol = $conexion->query(
        "SELECT COALESCE(
            (SELECT id_rol FROM t_roles WHERE UPPER(nombre_rol) = 'EMPLEADO' AND fec_delete IS NULL ORDER BY id_rol LIMIT 1),
            (SELECT id_rol FROM t_roles WHERE fec_delete IS NULL ORDER BY id_rol LIMIT 1)
        ) AS id_rol"
    );
    $idRol = (int) $sentenciaRol->fetchColumn();

    if ($idRol <= 0) {
        redirigirRegistro([
            'tab' => 'register',
            'status' => 'error',
            'code' => 'rol_no_configurado',
            'msg' => 'No hay roles activos configurados para registrar usuarios.',
        ]);
    }

    // --- 3. Hash seguro de la contraseña (nunca se guarda en texto plano) ---
    $hashContrasena = password_hash($contrasena, PASSWORD_DEFAULT);

    // --- 4. Guardar registro pendiente de verificación ---
    $fechaExpiracionRegistro = (new DateTime('+24 hours'))->format('Y-m-d H:i:s');

    // Limpiar registros pendientes expirados del mismo documento/correo/usuario.
    $limpiarPendientes = $conexion->prepare(
        "DELETE FROM t_registros_pendientes
         WHERE (id_usuario = :id_usuario OR username = :username OR correo = :correo)
           AND fecha_expiracion < CURRENT_TIMESTAMP"
    );
    $limpiarPendientes->execute([
        ':id_usuario' => $idUsuario,
        ':username'   => $username,
        ':correo'     => $correo,
    ]);

    try {
        $insertarPendiente = $conexion->prepare(
            "INSERT INTO t_registros_pendientes
                (id_usuario, id_rol, username, tipo_documento, primer_nombre, segundo_nombre,
                 primer_apellido, segundo_apellido, correo, contrasena, fecha_expiracion, usr_insert, fec_insert)
             VALUES
                (:id_usuario, :id_rol, :username, :tipo_documento, :primer_nombre, :segundo_nombre,
                 :primer_apellido, :segundo_apellido, :correo, :contrasena, :fecha_expiracion, :usr_insert, CURRENT_TIMESTAMP)"
        );
        $insertarPendiente->execute([
            ':id_usuario'       => $idUsuario,
            ':id_rol'           => $idRol,
            ':username'         => $username,
            ':tipo_documento'   => $tipoDocumento,
            ':primer_nombre'    => $primerNombre,
            ':segundo_nombre'   => $segundoNombre,
            ':primer_apellido'  => $primerApellido,
            ':segundo_apellido' => $segundoApellido,
            ':correo'           => $correo,
            ':contrasena'       => $hashContrasena,
            ':fecha_expiracion' => $fechaExpiracionRegistro,
            ':usr_insert'       => $usrInsert,
        ]);
    } catch (PDOException $e) {
        if ($e->getCode() === '23505') {
            redirigirRegistro([
                'tab' => 'register',
                'status' => 'error',
                'code' => 'registro_no_completado',
                'msg' => 'El documento, usuario o correo ya tienen una activación pendiente.',
            ]);
        }
        throw $e;
    }

    $idRegistroPendiente = (int) $conexion->lastInsertId('t_registros_pendientes_id_registro_pendiente_seq');

    // --- 4b. Rate limiting antes de enviar correo ---
    $rateLimiter = new RateLimiter($conexion);
    $rateCheck = $rateLimiter->verificar($idUsuario, 'verificacion');

    if (!$rateCheck['permitido']) {
        redirigirRegistro([
            'tab' => 'register',
            'status' => 'error',
            'code' => 'demasiados_intentos',
            'msg' => 'Has enviado demasiadas solicitudes. Inténtalo de nuevo en ' . $rateCheck['minutos'] . ' minuto(s).',
        ]);
    }

    // --- 5. Generar token de verificación de correo ---
    $token = bin2hex(random_bytes(32));
    $tokenHash = hash('sha256', $token);
    $fechaExpiracion = (new DateTime('+24 hours'))->format('Y-m-d H:i:s');

    // Limpiar tokens expirados no usados del mismo registro pendiente.
    $limpiarExpirados = $conexion->prepare(
        "DELETE FROM t_verificacion_correo
         WHERE id_registro_pendiente = :id_registro_pendiente
           AND fecha_expiracion < CURRENT_TIMESTAMP
           AND fecha_uso IS NULL"
    );
    $limpiarExpirados->execute([':id_registro_pendiente' => $idRegistroPendiente]);

    // Invalidar tokens anteriores no usados del mismo registro pendiente.
    $invalidar = $conexion->prepare(
        "UPDATE t_verificacion_correo
         SET fec_delete = CURRENT_TIMESTAMP,
             usr_delete = :usr_delete
         WHERE id_registro_pendiente = :id_registro_pendiente
           AND fecha_uso IS NULL
           AND fec_delete IS NULL"
    );
    $invalidar->execute([
        ':id_registro_pendiente' => $idRegistroPendiente,
        ':usr_delete'            => $usrInsert,
    ]);

    // Insertar nuevo token.
    $insertarToken = $conexion->prepare(
        "INSERT INTO t_verificacion_correo
            (id_registro_pendiente, token_hash, fecha_expiracion, usr_insert, fec_insert)
         VALUES
            (:id_registro_pendiente, :token_hash, :fecha_expiracion, :usr_insert, CURRENT_TIMESTAMP)"
    );
    $insertarToken->execute([
        ':id_registro_pendiente' => $idRegistroPendiente,
        ':token_hash'            => $tokenHash,
        ':fecha_expiracion'      => $fechaExpiracion,
        ':usr_insert'            => $usrInsert,
    ]);

    // --- 6. Construir enlace y enviar correo de verificación ---
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host   = $_SERVER['HTTP_HOST'];
    $link   = $scheme . '://' . $host . '/dlgc_rrhh/app/verificar_correo.php?token=' . $token;

    $nombreDestinatario = trim($primerNombre . ' ' . $primerApellido);
    if ($nombreDestinatario === '') {
        $nombreDestinatario = $username;
    }

    $subject = 'Verifica tu correo - Portal DLGC';

    $body = sprintf(
        '<!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Verificación de Correo</title>
            <style>
                body { margin: 0; padding: 0; background-color: #ececec; font-family: Inter, system-ui, -apple-system, sans-serif; color: #0D130F; }
                .wrapper { width: 100%%; padding: 40px 16px; }
                .card { max-width: 480px; margin: 0 auto; background-color: #ffffff; border-radius: 24px; border: 2px solid #3ba86a; box-shadow: 0 12px 30px rgba(59, 168, 106, 0.12); overflow: hidden; }
                .header { background-color: #3ba86a; padding: 24px; text-align: center; }
                .header h1 { color: #ffffff; margin: 0; font-size: 1.25rem; font-weight: 700; }
                .content { padding: 32px; }
                .content p { margin: 0 0 16px; line-height: 1.6; font-size: 0.95rem; color: #0D130F; }
                .content p strong { color: #0D130F; }
                .content a { color: #0D130F; text-decoration: none; }
                .btn-container { text-align: center; margin: 24px 0; }
                .btn { display: inline-block; background-color: #3ba86a; color: #ffffff !important; text-decoration: none; padding: 14px 28px; border-radius: 12px; font-weight: 600; font-size: 1rem; }
                .btn:hover { background-color: #298350; }
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
                        <p>Hola <strong>%s</strong>,</p>
                        <p>Gracias por registrarte. Haz clic en el botón de abajo para verificar tu correo y activar tu cuenta:</p>
                        <div class="btn-container">
                            <a href="%s" class="btn" style="color: #ffffff !important; text-decoration: none;">Verificar Correo</a>
                        </div>
                        <p class="notice">Este enlace expirará en <strong>24 horas</strong>. Si no creaste esta cuenta, ignora este mensaje.</p>
                        <p>Saludos,<br><strong>Portal DLGC RRHH</strong></p>
                    </div>
                    <div class="footer">
                        &copy; Distribuciones La Gran Cacharrería. Todos los derechos reservados.
                    </div>
                </div>
            </div>
        </body>
        </html>',
        htmlspecialchars($nombreDestinatario, ENT_QUOTES, 'UTF-8'),
        htmlspecialchars($link, ENT_QUOTES, 'UTF-8')
    );

    $altBody = "Portal DLGC RRHH\n\n"
             . "Hola {$nombreDestinatario},\n\n"
             . "Gracias por registrarte.\n\n"
             . "Verifica tu correo visitando el siguiente enlace (válido por 24 horas):\n{$link}\n\n"
             . "Si no creaste esta cuenta, ignora este mensaje.\n\n"
             . "Distribuciones La Gran Cacharrería";

    try {
        $mailer = new Mailer();
        $enviado = $mailer->send($correo, $nombreDestinatario, $subject, $body, $altBody);

        if (!$enviado) {
            error_log('No se pudo enviar correo de verificación a: ' . $correo);
        }
    } catch (Exception $e) {
        error_log('Error enviando correo de verificación: ' . $e->getMessage());
    }

    // --- 7. Registrar intento de envío ---
    $rateLimiter->registrar($idUsuario, 'verificacion');

    // --- 8. Redirección al login tras registro exitoso ---
    unset(
        $_SESSION['codigo_registro_validado'],
        $_SESSION['codigo_registro'],
        $_SESSION['codigo_registro_tipo'],
        $_SESSION['codigo_registro_id'],
        $_SESSION['register_form_data']
    );
    redirigirRegistro([
        'tab' => 'login',
        'status' => 'success',
        'code' => 'registro_pendiente_verificacion',
    ]);

} catch (PDOException $e) {
    // Si falla algo después de insertar el registro pendiente, lo revertimos
    // para no dejar datos huérfanos si el usuario nunca verifica.
    if (isset($idRegistroPendiente) && $idRegistroPendiente > 0) {
        try {
            $conexion->prepare("DELETE FROM t_registros_pendientes WHERE id_registro_pendiente = :id")
                     ->execute([':id' => $idRegistroPendiente]);
        } catch (PDOException $cleanupError) {
            error_log('Error limpiando registro pendiente: ' . $cleanupError->getMessage());
        }
    }

    guardarDatosRegistroEnSesion([
        'username'         => $username ?? '',
        'tipo_documento'   => $tipoDocumento ?? '',
        'id_usuario'       => $idUsuario ?? '',
        'primer_nombre'    => $primerNombre ?? '',
        'segundo_nombre'   => $segundoNombre ?? '',
        'primer_apellido'  => $primerApellido ?? '',
        'segundo_apellido' => $segundoApellido ?? '',
        'correo'           => $correo ?? '',
        'accept_terms'     => ($acceptTerms ?? false) ? '1' : '',
        'accept_privacy'   => ($acceptPrivacy ?? false) ? '1' : '',
    ]);

    error_log('Error en el proceso de registro: ' . $e->getMessage());
    redirigirRegistro([
        'tab'    => 'register',
        'step'   => '2',
        'status' => 'error',
        'code'   => 'error_servidor',
    ]);
}
