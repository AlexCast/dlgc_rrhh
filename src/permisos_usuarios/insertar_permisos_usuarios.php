<?php

$moduleId = 21;
$requiredAction = 'crear';
require_once __DIR__ . '/../../app/src_guard.php';

// Validate CSRF token before processing mutation.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate();
}
if (!isset($_POST['id_usuario'])) {
    header('Location: forma_permisos_usuarios.php?error=datos');
    exit();
}

$id_usuario = trim($_POST['id_usuario']);

// Asignación en lote: id_operaciones[] (nuevo formulario).
// Se conserva id_operacion (único) por compatibilidad.
$idOperaciones = [];
if (isset($_POST['id_operaciones']) && is_array($_POST['id_operaciones'])) {
    foreach ($_POST['id_operaciones'] as $crudo) {
        $valor = (int) $crudo;
        if ($valor > 0) {
            $idOperaciones[] = $valor;
        }
    }
} elseif (isset($_POST['id_operacion'])) {
    $valor = (int) $_POST['id_operacion'];
    if ($valor > 0) {
        $idOperaciones[] = $valor;
    }
}

$idOperaciones = array_values(array_unique($idOperaciones));

if ($id_usuario === '' || count($idOperaciones) === 0) {
    header('Location: forma_permisos_usuarios.php?error=' . urlencode('No se recibió un usuario u operaciones válidos.'));
    exit();
}

if (count($idOperaciones) > 200) {
    header('Location: forma_permisos_usuarios.php?error=' . urlencode('No puede asignar más de 200 operaciones a la vez.'));
    exit();
}

require_once __DIR__ . '/../../app/conexion.php';

$sentencia = $conexion->prepare('SELECT fun_insert_permisos_usuarios(?, ?);');

$asignados = 0;
$fallidos = [];
foreach ($idOperaciones as $id_operacion) {
    $sentencia->execute([$id_usuario, $id_operacion]);
    $resultado = (string) $sentencia->fetchColumn();

    if (stripos($resultado, 'correctamente') !== false) {
        $asignados++;
    } else {
        $fallidos[] = $resultado;
    }
}

if ($asignados > 0) {
    $mensaje = 'Se asignaron ' . $asignados . (count($idOperaciones) === 1 ? ' permiso' : ' permisos') . ' correctamente.';
    if (count($fallidos) > 0) {
        $mensaje .= ' No se pudieron asignar ' . count($fallidos) . ' más: ' . $fallidos[0];
    }
    header('Location: listar_permisos_usuarios.php?success=' . urlencode($mensaje));
    exit();
}

$mensaje = count($fallidos) > 0 ? $fallidos[0] : 'No se pudo asignar ningún permiso.';
header('Location: forma_permisos_usuarios.php?error=' . urlencode($mensaje));
exit();
