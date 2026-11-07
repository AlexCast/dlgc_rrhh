<?php
declare(strict_types=1);

/**
 * Endpoint de autoservicio para actualizar SOLO los datos personales de la
 * ficha del usuario en sesión (nombres, fecha de nacimiento, género, tipo de
 * sangre, estado civil, dirección de residencia, celular y foto de perfil).
 *
 * Seguridad:
 *  - El destino de la actualización es SIEMPRE $_SESSION['id_usuario']; el
 *    id_usuario nunca se recibe desde el cliente (previene IDOR).
 *  - CSRF obligatorio, igual que el resto de endpoints.
 *  - Entradas validadas con InputSanitizer (misma línea de defensa del CRUD).
 *  - Cambios de foto exigen Turnstile (acción 'empleado_foto'), rate limiting
 *    (5/10 min) y compresión server-side; el nombre de la llave en R2 se
 *    genera desde el id de sesión, nunca desde la entrada del usuario.
 *  - La función fun_update_mi_ficha() no acepta campos privilegiados (rol,
 *    jefe, municipio, fechas laborales, correo), así que aunque el cliente
 *    envíe otros campos serán ignorados.
 */

require_once __DIR__ . '/auth_guard.php'; // Sesión + conexion.php + helpers CSRF.
require_once __DIR__ . '/helpers/InputSanitizer.php';
require_once __DIR__ . '/helpers/UploadRateLimiter.php';
require_once __DIR__ . '/helpers/TurnstileHelper.php';
require_once __DIR__ . '/helpers/ImageCompressor.php';
require_once __DIR__ . '/helpers/FileStorage/StorageFactory.php';

header('Content-Type: application/json; charset=utf-8');

function responderFichaJson(bool $success, string $message, int $httpCode = 200, array $extra = []): void
{
    http_response_code($httpCode);
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $extra), JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responderFichaJson(false, 'Método no permitido.', 405);
}

if (!csrf_validate(false)) {
    responderFichaJson(false, 'Token de seguridad inválido o ausente. Recarga la página e inténtalo de nuevo.', 403);
}

// Mismo control de acceso que la página que muestra la ficha.
if (!has_module_access(22)) {
    responderFichaJson(false, 'No tienes acceso a tu ficha técnica.', 403);
}

$idUsuario = $_SESSION['id_usuario'] ?? null;
if (!is_string($idUsuario) || trim($idUsuario) === '') {
    responderFichaJson(false, 'Sesión inválida.', 401);
}

// Rate limiting suave para ediciones de perfil (10 por hora por usuario).
// Si la tabla/constraint del limitador falla, se registra y se continúa:
// el control primario sigue siendo la sesión + CSRF + validación estricta.
try {
    $limitador = new UploadRateLimiter($conexion, 10, 60);
    $chequeo = $limitador->verificar($idUsuario, 'mi_ficha');
    if (!$chequeo['permitido']) {
        responderFichaJson(false, 'Has superado el límite de ediciones. Inténtalo de nuevo en ' . $chequeo['minutos'] . ' minuto(s).', 429);
    }
    $limitador->registrar($idUsuario, 'mi_ficha');
} catch (PDOException $e) {
    error_log('mi_ficha_actualizar: rate limiter no disponible: ' . $e->getMessage());
}

// --- Validación estricta de entradas ---
$primerNombre    = InputSanitizer::letters($_POST['primer_nombre'] ?? null, 30);
$segundoNombre   = InputSanitizer::letters($_POST['segundo_nombre'] ?? null, 30);
$primerApellido  = InputSanitizer::letters($_POST['primer_apellido'] ?? null, 30);
$segundoApellido = InputSanitizer::letters($_POST['segundo_apellido'] ?? null, 30);
$genero          = InputSanitizer::whitelist($_POST['genero'] ?? null, ['MASCULINO', 'FEMENINO', 'OTRO']);
$fechaNacimiento = InputSanitizer::date($_POST['fecha_nacimiento'] ?? null);
$tipoSangre      = InputSanitizer::whitelist($_POST['tipo_sangre'] ?? null, ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-']);
$estadoCivil     = InputSanitizer::whitelist($_POST['estado_civil'] ?? null, ['SOLTERO(A)', 'CASADO(A)', 'DIVORCIADO(A)', 'VIUDO(A)', 'UNION LIBRE', 'SEPARADO(A)']);
$direccionCasa   = InputSanitizer::boundedText($_POST['direccion_casa'] ?? null, 100);
$numeroCelular   = InputSanitizer::digits($_POST['numero_celular'] ?? null, 10);

// --- Foto de perfil: detectar si hay un cambio solicitado --------------------
$quitarFoto = ($_POST['quitar_foto'] ?? '') === '1';
$errorArchivo = $_FILES['foto_perfil']['error'] ?? UPLOAD_ERR_NO_FILE;
if (is_array($errorArchivo)) {
    $errorArchivo = UPLOAD_ERR_NO_FILE;
}
$hayCambioFoto = $quitarFoto || $errorArchivo === UPLOAD_ERR_OK;

$errores = [];

if ($primerNombre === null) {
    $errores[] = 'El primer nombre es obligatorio (solo letras, máximo 30 caracteres).';
}
if ($primerApellido === null) {
    $errores[] = 'El primer apellido es obligatorio (solo letras, máximo 30 caracteres).';
}
if ($genero === null) {
    $errores[] = 'El género seleccionado no es válido.';
}
if ($fechaNacimiento === null) {
    $errores[] = 'La fecha de nacimiento no es válida.';
} elseif ($fechaNacimiento > date('Y-m-d')) {
    $errores[] = 'La fecha de nacimiento no puede ser futura.';
}
if ($tipoSangre === null) {
    $errores[] = 'El tipo de sangre seleccionado no es válido.';
}
if ($estadoCivil === null) {
    $errores[] = 'El estado civil seleccionado no es válido.';
}
if ($direccionCasa === null) {
    $errores[] = 'La dirección de residencia es obligatoria (máximo 100 caracteres).';
}
if ($numeroCelular === null || strlen($numeroCelular) !== 10) {
    $errores[] = 'El número celular debe tener exactamente 10 dígitos.';
}

if (!empty($errores)) {
    responderFichaJson(false, implode(' ', $errores), 400);
}

// El cambio de foto es la operación más sensible del formulario: exige
// Turnstile (misma acción 'empleado_foto' del CRUD administrativo) y su
// propio rate limiting (5 subidas por 10 minutos, compartido con RRHH).
if ($hayCambioFoto) {
    if (!TurnstileHelper::verify($_POST['cf-turnstile-response'] ?? null, 'empleado_foto')) {
        responderFichaJson(false, 'No pudimos verificar que no eres un robot. Resuelve la verificación de seguridad e inténtalo de nuevo.', 400);
    }

    try {
        $limiteFoto = new UploadRateLimiter($conexion, 5, 10);
        $chequeoFoto = $limiteFoto->verificar($idUsuario, 'foto_perfil');
        if (!$chequeoFoto['permitido']) {
            responderFichaJson(false, 'Has superado el límite de cambios de foto. Inténtalo de nuevo en ' . $chequeoFoto['minutos'] . ' minuto(s).', 429);
        }
        $limiteFoto->registrar($idUsuario, 'foto_perfil');
    } catch (PDOException $e) {
        error_log('mi_ficha_actualizar: rate limiter de foto no disponible: ' . $e->getMessage());
    }
}

// --- Procesar la foto: conservar, reemplazar o quitar ------------------------
$fotoAnterior = null;
try {
    $sentenciaFoto = $conexion->prepare('SELECT foto_perfil FROM t_empleados WHERE id_usuario = ? AND fec_delete IS NULL');
    $sentenciaFoto->execute([$idUsuario]);
    $valorFoto = $sentenciaFoto->fetchColumn();
    $sentenciaFoto->closeCursor();
    $fotoAnterior = ($valorFoto === false || $valorFoto === null) ? null : (string) $valorFoto;
} catch (PDOException $e) {
    error_log('mi_ficha_actualizar: error leyendo foto actual: ' . $e->getMessage());
    responderFichaJson(false, 'No se pudo leer tu foto actual.', 500);
}

$fotoPerfil = $fotoAnterior;
$fotoNuevaSubida = null; // Llave del objeto recién subido (para rollback si falla el update).

if ($quitarFoto) {
    $fotoPerfil = null;
} elseif ($errorArchivo === UPLOAD_ERR_OK) {
    $tmpName = (string) ($_FILES['foto_perfil']['tmp_name'] ?? '');
    if ($tmpName === '' || !is_uploaded_file($tmpName)) {
        responderFichaJson(false, 'El archivo de foto recibido no es válido.', 400);
    }

    $temporalComprimido = tempnam(sys_get_temp_dir(), 'mi_ficha_foto_');
    if ($temporalComprimido === false) {
        responderFichaJson(false, 'No se pudo preparar la foto para su procesamiento.', 500);
    }

    try {
        $comprimida = ImageCompressor::compress($tmpName, $temporalComprimido);
        $storage = StorageFactory::create();
        $subida = $storage->upload(
            $temporalComprimido,
            'fotos/perfiles/' . $idUsuario . '.' . $comprimida['extension'],
            $comprimida['mime']
        );
        $fotoPerfil = $subida['storage_key'];
        $fotoNuevaSubida = $subida['storage_key'];
    } catch (Throwable $e) {
        @unlink($temporalComprimido);
        error_log('mi_ficha_actualizar: error procesando foto: ' . $e->getMessage());
        responderFichaJson(false, 'No se pudo procesar la foto de perfil.', 400);
    }
    @unlink($temporalComprimido);
} elseif ($errorArchivo !== UPLOAD_ERR_NO_FILE) {
    responderFichaJson(false, 'La foto supera el tamaño máximo permitido (2 MB) o no se subió correctamente.', 400);
}

try {
    $conexion->beginTransaction();

    $sentencia = $conexion->prepare('SELECT fun_update_mi_ficha(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
    $sentencia->execute([
        $idUsuario,
        $primerNombre,
        $segundoNombre,
        $primerApellido,
        $segundoApellido,
        $genero,
        $fechaNacimiento,
        $tipoSangre,
        $estadoCivil,
        $direccionCasa,
        $numeroCelular,
        $fotoPerfil,
    ]);
    $resultado = (string) $sentencia->fetchColumn();

    if (stripos($resultado, 'correctamente') !== false) {
        $conexion->commit();

        // Reflejar el nuevo nombre en la sesión (auditorías y sidebar sin re-login).
        $_SESSION['nombre_completo'] = trim(implode(' ', array_filter([
            $primerNombre,
            $segundoNombre,
            $primerApellido,
            $segundoApellido,
        ])));

        // Reflejar la foto en la sesión para que el sidebar la muestre sin re-login.
        $_SESSION['foto_perfil'] = ($fotoPerfil === null || $fotoPerfil === '')
            ? null
            : '/app/foto_perfil.php?id=' . rawurlencode($idUsuario);

        // Limpieza del objeto anterior solo si la referencia cambió (best-effort).
        if ($fotoAnterior !== null && $fotoAnterior !== $fotoPerfil && str_starts_with($fotoAnterior, 'fotos/perfiles/')) {
            try {
                StorageFactory::create()->delete($fotoAnterior);
            } catch (Throwable $e) {
                error_log('mi_ficha_actualizar: no se pudo eliminar la foto anterior en R2: ' . $e->getMessage());
            }
        }

        responderFichaJson(true, 'Tu información personal fue actualizada correctamente.', 200, [
            'foto_perfil' => $_SESSION['foto_perfil'],
        ]);
    }

    $conexion->rollBack();

    // Rollback del objeto: si se subió una foto nueva y el update falló, eliminarla de R2.
    if ($fotoNuevaSubida !== null) {
        try {
            StorageFactory::create()->delete($fotoNuevaSubida);
        } catch (Throwable $e) {
            error_log('mi_ficha_actualizar: rollback de foto falló: ' . $e->getMessage());
        }
    }

    responderFichaJson(false, $resultado !== '' ? $resultado : 'No se pudo actualizar tu ficha.', 400);
} catch (PDOException $e) {
    if ($conexion->inTransaction()) {
        $conexion->rollBack();
    }
    if ($fotoNuevaSubida !== null) {
        try {
            StorageFactory::create()->delete($fotoNuevaSubida);
        } catch (Throwable $eRollback) {
            error_log('mi_ficha_actualizar: rollback de foto falló: ' . $eRollback->getMessage());
        }
    }
    error_log('mi_ficha_actualizar: ' . $e->getMessage());
    responderFichaJson(false, 'Ocurrió un error al guardar los cambios. Inténtalo de nuevo.', 500);
}
