<?php

declare(strict_types=1);

$moduleId = 24;
$requiredAction = 'crear';
require_once __DIR__ . '/../../app/src_guard.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate();
}

if (
    !isset($_POST['titulo']) ||
    !isset($_POST['contenido']) ||
    !isset($_POST['categoria'])
) {
    header('Location: forma_comunicados.php?error=datos');
    exit();
}

$titulo = trim((string) $_POST['titulo']);
$contenido = trim((string) $_POST['contenido']);
$categoria = trim((string) $_POST['categoria']);

if ($titulo === '' || $contenido === '' || $categoria === '') {
    header('Location: forma_comunicados.php?error=datos');
    exit();
}

require_once __DIR__ . '/../../app/conexion.php';

$sentencia = $conexion->prepare('SELECT fun_insert_comunicados(?, ?, ?);');
$sentencia->execute([$titulo, $contenido, $categoria]);
$resultado = (string) $sentencia->fetchColumn();

if (stripos($resultado, 'correctamente') !== false) {
    header('Location: listar_comunicados.php?success=' . urlencode('Comunicado publicado correctamente.'));
    exit();
}

header('Location: forma_comunicados.php?error=' . urlencode($resultado));
exit();
