<?php

declare(strict_types=1);

$moduleId = 24;
$requiredAction = 'actualizar';
require_once __DIR__ . '/../../app/src_guard.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate();
}

if (
    !isset($_POST['id_comunicado']) ||
    !isset($_POST['titulo']) ||
    !isset($_POST['contenido']) ||
    !isset($_POST['categoria'])
) {
    header('Location: listar_comunicados.php?error=datos');
    exit();
}

require_once __DIR__ . '/../../app/helpers/HtmlSanitizer.php';

$id_comunicado = (int) trim($_POST['id_comunicado']);
$titulo = trim((string) $_POST['titulo']);
$contenido = trim(HtmlSanitizer::clean((string) $_POST['contenido']));
$categoria = trim((string) $_POST['categoria']);

if ($id_comunicado <= 0 || $titulo === '' || strip_tags($contenido) === '' || $categoria === '') {
    header('Location: listar_comunicados.php?error=datos');
    exit();
}

require_once __DIR__ . '/../../app/conexion.php';

$sentencia = $conexion->prepare('SELECT fun_update_comunicados(?, ?, ?, ?);');
$sentencia->execute([$id_comunicado, $titulo, $contenido, $categoria]);
$resultado = (string) $sentencia->fetchColumn();

if (stripos($resultado, 'correctamente') !== false) {
    header('Location: listar_comunicados.php?success=' . urlencode('Comunicado actualizado correctamente.'));
    exit();
}

header('Location: listar_comunicados.php?error=' . urlencode($resultado));
exit();
