<?php

declare(strict_types=1);

$moduleId = 26;
$requiredAction = 'actualizar';
require_once __DIR__ . '/../../app/src_guard.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate();
}

if (
    !isset($_POST['id_miembro']) ||
    !isset($_POST['nombre_completo']) ||
    !isset($_POST['cargo']) ||
    !isset($_POST['tipo_comite'])
) {
    header('Location: listar_comite.php?error=datos');
    exit();
}

require_once __DIR__ . '/../../app/helpers/InputSanitizer.php';

$idMiembro = InputSanitizer::intValue($_POST['id_miembro'] ?? null, 1);
$nombreCompleto = InputSanitizer::boundedText($_POST['nombre_completo'] ?? null, 100);
$cargo = InputSanitizer::boundedText($_POST['cargo'] ?? null, 50);
$tipoComite = InputSanitizer::whitelist($_POST['tipo_comite'] ?? null, ['COPASST', 'COMITE_CONVIVENCIA']);
$correo = InputSanitizer::email((string) ($_POST['correo'] ?? ''));
$telefono = InputSanitizer::telefono($_POST['telefono'] ?? '') ?? '';
$orden = InputSanitizer::intValue($_POST['orden_visualizacion'] ?? null, 0) ?? 0;

if ($idMiembro === null || $nombreCompleto === null || $cargo === null || $tipoComite === null) {
    header('Location: listar_comite.php?error=datos');
    exit();
}

require_once __DIR__ . '/../../app/conexion.php';

$sentencia = $conexion->prepare('SELECT fun_update_sst_comite_miembro(?, ?, ?, ?, ?, ?, ?);');
$sentencia->execute([$idMiembro, $nombreCompleto, $cargo, $tipoComite, $correo, $telefono, $orden]);
$resultado = (string) $sentencia->fetchColumn();

if (stripos($resultado, 'correctamente') !== false) {
    header('Location: listar_comite.php?success=' . urlencode('Miembro del comité actualizado correctamente.'));
    exit();
}

header('Location: listar_comite.php?error=' . urlencode($resultado));
exit();
