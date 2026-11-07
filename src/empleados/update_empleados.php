<?php

$moduleId = 4;
$requiredAction = 'actualizar';
require_once __DIR__ . '/../../app/src_guard.php';

// Validate CSRF token before processing mutation.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate();
}

// Verificación anti-bot (Cloudflare Turnstile) antes de procesar la subida de la foto.
require_once __DIR__ . '/../../app/helpers/TurnstileHelper.php';
if (!TurnstileHelper::verify($_POST['cf-turnstile-response'] ?? null, 'empleado_foto')) {
    $idCrudo = rawurlencode(trim((string) ($_POST['id_usuario'] ?? '')));
    header('Location: editar_empleados.php?id_usuario=' . $idCrudo . '&error=turnstile');
    exit();
}

if (
    !isset($_POST['id_usuario']) ||
    !isset($_POST['id_municipio']) ||
    !isset($_POST['fecha_ingreso']) ||
    !isset($_POST['genero']) ||
    !isset($_POST['fecha_nacimiento']) ||
    !isset($_POST['tipo_sangre']) ||
    !isset($_POST['estado_civil']) ||
    !isset($_POST['direccion_casa']) ||
    !isset($_POST['numero_celular'])
) {
    header('Location: listar_empleados.php?error=datos');
    exit();
}

require_once __DIR__ . '/../../app/helpers/InputSanitizer.php';

$id_usuario = InputSanitizer::boundedText($_POST['id_usuario'] ?? null, 20);
$id_jefe = InputSanitizer::boundedText($_POST['id_jefe'] ?? null, 20);
$id_municipio = InputSanitizer::intValue($_POST['id_municipio'] ?? null, 1);
$fecha_ingreso = InputSanitizer::date($_POST['fecha_ingreso'] ?? null);
$fecha_egreso = InputSanitizer::date($_POST['fecha_egreso'] ?? null);
$genero = InputSanitizer::whitelist($_POST['genero'] ?? null, ['MASCULINO', 'FEMENINO', 'OTRO']);
$fecha_nacimiento = InputSanitizer::date($_POST['fecha_nacimiento'] ?? null);
$tipo_sangre = InputSanitizer::whitelist($_POST['tipo_sangre'] ?? null, ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-']);
$estado_civil = InputSanitizer::whitelist($_POST['estado_civil'] ?? null, ['SOLTERO(A)', 'CASADO(A)', 'DIVORCIADO(A)', 'VIUDO(A)', 'UNION LIBRE', 'SEPARADO(A)']);
$direccion_casa = InputSanitizer::boundedText($_POST['direccion_casa'] ?? null, 100);
$numero_celular = InputSanitizer::digits($_POST['numero_celular'] ?? null, 10);

if ($id_usuario === null || $id_municipio === null || $fecha_ingreso === null || $genero === null || $fecha_nacimiento === null || $tipo_sangre === null || $estado_civil === null || $direccion_casa === null || $numero_celular === null) {
    header('Location: listar_empleados.php?error=datos');
    exit();
}

require_once __DIR__ . '/../../app/conexion.php';
require_once __DIR__ . '/../../app/helpers/ImageCompressor.php';
require_once __DIR__ . '/../../app/helpers/FileStorage/StorageFactory.php';
require_once __DIR__ . '/../../app/helpers/UploadRateLimiter.php';

// Rate limiting de subida de fotos de perfil (5 por 10 minutos).
$limiteFoto = new UploadRateLimiter($conexion, 5, 10);
$chequeoFoto = $limiteFoto->verificar($id_usuario, 'foto_perfil');
if (!$chequeoFoto['permitido']) {
    header('Location: editar_empleados.php?id_usuario=' . rawurlencode($id_usuario) . '&error=' . urlencode('Has superado el límite de subidas de fotos. Inténtalo de nuevo en ' . $chequeoFoto['minutos'] . ' minuto(s).'));
    exit();
}
$limiteFoto->registrar($id_usuario, 'foto_perfil');

// --- Foto de perfil: conservar, reemplazar o quitar -------------------------
$quitarFoto = ($_POST['quitar_foto'] ?? '') === '1';
$fotoNuevaSubida = null; // Llave del objeto recién subido (para rollback si el update falla).

try {
    $sentenciaFoto = $conexion->prepare('SELECT foto_perfil FROM t_empleados WHERE id_usuario = ?');
    $sentenciaFoto->execute([$id_usuario]);
    $valorFoto = $sentenciaFoto->fetchColumn();
    $sentenciaFoto->closeCursor();
    $fotoAnterior = ($valorFoto === false || $valorFoto === null) ? null : (string) $valorFoto;
} catch (PDOException $e) {
    error_log('update_empleados: error leyendo foto actual: ' . $e->getMessage());
    header('Location: editar_empleados.php?id_usuario=' . rawurlencode($id_usuario) . '&error=' . urlencode('No se pudo leer la foto actual.'));
    exit();
}

$foto_perfil = $fotoAnterior;

$errorArchivo = $_FILES['foto_perfil']['error'] ?? UPLOAD_ERR_NO_FILE;
if (is_array($errorArchivo)) {
    $errorArchivo = UPLOAD_ERR_NO_FILE;
}

if ($quitarFoto) {
    $foto_perfil = null;
} elseif ($errorArchivo === UPLOAD_ERR_OK) {
    $tmpName = (string) ($_FILES['foto_perfil']['tmp_name'] ?? '');
    if ($tmpName === '' || !is_uploaded_file($tmpName)) {
        header('Location: editar_empleados.php?id_usuario=' . rawurlencode($id_usuario) . '&error=foto');
        exit();
    }

    $temporalComprimido = tempnam(sys_get_temp_dir(), 'foto_perfil_');
    if ($temporalComprimido === false) {
        header('Location: editar_empleados.php?id_usuario=' . rawurlencode($id_usuario) . '&error=foto');
        exit();
    }

    try {
        $comprimida = ImageCompressor::compress($tmpName, $temporalComprimido);
        $storage = StorageFactory::create();
        $subida = $storage->upload(
            $temporalComprimido,
            'fotos/perfiles/' . $id_usuario . '.' . $comprimida['extension'],
            $comprimida['mime']
        );
        $foto_perfil = $subida['storage_key'];
        $fotoNuevaSubida = $subida['storage_key'];
    } catch (Throwable $e) {
        @unlink($temporalComprimido);
        error_log('update_empleados: error procesando foto: ' . $e->getMessage());
        header('Location: editar_empleados.php?id_usuario=' . rawurlencode($id_usuario) . '&error=' . urlencode('No se pudo procesar la foto de perfil.'));
        exit();
    }
    @unlink($temporalComprimido);
} elseif ($errorArchivo !== UPLOAD_ERR_NO_FILE) {
    header('Location: editar_empleados.php?id_usuario=' . rawurlencode($id_usuario) . '&error=' . urlencode('La foto supera el tamaño máximo permitido (2 MB).'));
    exit();
}

$sentencia = $conexion->prepare('SELECT fun_update_empleados(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?);');
$sentencia->execute([
    $id_usuario,
    $fecha_ingreso,
    $genero,
    $fecha_nacimiento,
    $tipo_sangre,
    $estado_civil,
    $direccion_casa,
    $numero_celular,
    $id_jefe,
    $id_municipio,
    $fecha_egreso,
    $foto_perfil
]);
$resultado = (string) $sentencia->fetchColumn();

if (stripos($resultado, 'funcion') !== false || stripos($resultado, 'correctamente') !== false) {
    // Limpieza del objeto anterior solo si la referencia cambió (best-effort).
    if ($fotoAnterior !== null && $fotoAnterior !== $foto_perfil && str_starts_with($fotoAnterior, 'fotos/perfiles/')) {
        try {
            StorageFactory::create()->delete($fotoAnterior);
        } catch (Throwable $e) {
            error_log('update_empleados: no se pudo eliminar la foto anterior en R2: ' . $e->getMessage());
        }
    }

    header('Location: listar_empleados.php?success=' . urlencode('Empleado actualizado correctamente.'));
    exit();
}

// Rollback: si se subió una foto nueva y el update falló, eliminarla de R2.
if ($fotoNuevaSubida !== null) {
    try {
        StorageFactory::create()->delete($fotoNuevaSubida);
    } catch (Throwable $e) {
        error_log('update_empleados: rollback de foto falló: ' . $e->getMessage());
    }
}

header('Location: listar_empleados.php?error=' . urlencode($resultado));
exit();