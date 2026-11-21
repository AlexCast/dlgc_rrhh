<?php
declare(strict_types=1);

$cargoPerfil = 'Sin cargo';
$idUsuarioPerfil = $_SESSION['id_usuario'] ?? null;

if (isset($conexion) && ($idUsuarioPerfil !== null && (is_string($idUsuarioPerfil) || is_int($idUsuarioPerfil)))) {
    $sentenciaCargo = $conexion->prepare(
        "SELECT c.puesto
         FROM t_contratos_empleados c
         WHERE c.id_usuario = ?
           AND c.fec_delete IS NULL
         ORDER BY c.fecha_inicio_puesto DESC, c.id_contrato DESC
         LIMIT 1"
    );
    $sentenciaCargo->execute([(string) $idUsuarioPerfil]);
    $puestoCargo = $sentenciaCargo->fetchColumn();

    if ($puestoCargo !== false && trim((string) $puestoCargo) !== '') {
        $cargoPerfil = (string) $puestoCargo;
    }
}

echo htmlspecialchars($cargoPerfil, ENT_QUOTES, 'UTF-8');
