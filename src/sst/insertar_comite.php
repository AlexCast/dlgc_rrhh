<?php

declare(strict_types=1);

$moduleId = 26;
$requiredAction = 'crear';
require_once __DIR__ . '/../../app/src_guard.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate();
}

if (
    !isset($_POST['nombre_completo']) ||
    !isset($_POST['cargo']) ||
    !isset($_POST['tipo_comite'])
) {
    header('Location: forma_comite.php?error=datos');
    exit();
}

require_once __DIR__ . '/../../app/helpers/InputSanitizer.php';

$nombreCompleto = InputSanitizer::boundedText($_POST['nombre_completo'] ?? null, 100);
$cargo = InputSanitizer::boundedText($_POST['cargo'] ?? null, 50);
$tipoComite = InputSanitizer::whitelist($_POST['tipo_comite'] ?? null, ['COPASST', 'COMITE_CONVIVENCIA']);
$correo = InputSanitizer::email((string) ($_POST['correo'] ?? ''));
$telefono = InputSanitizer::telefono($_POST['telefono'] ?? '') ?? '';
$orden = InputSanitizer::intValue($_POST['orden_visualizacion'] ?? null, 0) ?? 0;

if ($nombreCompleto === null || $cargo === null || $tipoComite === null) {
    header('Location: forma_comite.php?error=datos');
    exit();
}

require_once __DIR__ . '/../../app/conexion.php';

$sentencia = $conexion->prepare('SELECT fun_insert_sst_comite_miembro(?, ?, ?, ?, ?, ?);');
$sentencia->execute([$nombreCompleto, $cargo, $tipoComite, $correo, $telefono, $orden]);
$resultado = (string) $sentencia->fetchColumn();

if (stripos($resultado, 'correctamente') !== false) {
    header('Location: listar_comite.php?success=' . urlencode('Miembro del comité creado correctamente.'));
    exit();
}

header('Location: forma_comite.php?error=' . urlencode($resultado));
exit();
