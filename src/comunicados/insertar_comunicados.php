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

require_once __DIR__ . '/../../app/helpers/HtmlSanitizer.php';
require_once __DIR__ . '/../../app/helpers/InputSanitizer.php';

$titulo = InputSanitizer::boundedText($_POST['titulo'] ?? null, 100);
$contenido = trim(HtmlSanitizer::clean((string) $_POST['contenido']));
$categoria = InputSanitizer::whitelist($_POST['categoria'] ?? null, ['GENERAL', 'URGENTE', 'EVENTO', 'INFORMACION', 'INSTITUCIONAL']);

if ($titulo === null || strip_tags($contenido) === '' || $categoria === null) {
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
