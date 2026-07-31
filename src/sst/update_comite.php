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

$idMiembro = (int) trim($_POST['id_miembro']);
$nombreCompleto = trim((string) $_POST['nombre_completo']);
$cargo = trim((string) $_POST['cargo']);
$tipoComite = trim((string) $_POST['tipo_comite']);
$correo = trim((string) ($_POST['correo'] ?? ''));
$telefono = trim((string) ($_POST['telefono'] ?? ''));
$orden = filter_input(INPUT_POST, 'orden_visualizacion', FILTER_VALIDATE_INT);

if ($idMiembro <= 0 || $nombreCompleto === '' || $cargo === '' || $tipoComite === '') {
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
