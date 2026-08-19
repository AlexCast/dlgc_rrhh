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

$nombreCompleto = trim((string) $_POST['nombre_completo']);
$cargo = trim((string) $_POST['cargo']);
$tipoComite = trim((string) $_POST['tipo_comite']);
$correo = trim((string) ($_POST['correo'] ?? ''));
$telefono = trim((string) ($_POST['telefono'] ?? ''));
$orden = filter_input(INPUT_POST, 'orden_visualizacion', FILTER_VALIDATE_INT);

if ($nombreCompleto === '' || $cargo === '' || $tipoComite === '') {
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
