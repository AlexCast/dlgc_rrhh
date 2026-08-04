<?php

$moduleId = 21;
require_once __DIR__ . '/../../app/src_guard.php';
require_once __DIR__ . '/../../app/conexion.php';

$sentencia = $conexion->query('
    SELECT uo.id_usuario,
           uo.id_operacion,
           uo.usr_insert,
           uo.fec_insert,
           uo.usr_update,
           uo.fec_update,
           uo.usr_delete,
           uo.fec_delete,
           u.username,
           CONCAT_WS(\' \', u.primer_nombre, u.segundo_nombre, u.primer_apellido, u.segundo_apellido) AS nombre_usuario,
           o.nombre_operacion,
           m.id_modulo,
           m.nombre_modulo
    FROM t_usuarios_operaciones uo
    INNER JOIN t_usuarios u ON u.id_usuario = uo.id_usuario AND u.fec_delete IS NULL
    INNER JOIN t_operaciones o ON o.id_operacion = uo.id_operacion AND o.fec_delete IS NULL
    INNER JOIN t_modulos m ON m.id_modulo = o.id_modulo AND m.fec_delete IS NULL
    ORDER BY nombre_usuario, m.id_modulo::integer, o.id_operacion
');
$relaciones = $sentencia->fetchAll(PDO::FETCH_OBJ);

function construirArbolPermisos($relaciones) {
    $arbol = [];
    foreach ($relaciones as $rel) {
        if (!empty($rel->fec_delete)) {
            continue;
        }
        $idUsuario = (string) $rel->id_usuario;
        $nombreUsuario = htmlspecialchars((string) ($rel->nombre_usuario ?? $rel->username ?? $rel->id_usuario ?? ''), ENT_QUOTES, 'UTF-8');
        $idModulo = (string) $rel->id_modulo;
        $nombreModulo = htmlspecialchars((string) ($rel->nombre_modulo ?? ''), ENT_QUOTES, 'UTF-8');

        if (!isset($arbol[$idUsuario])) {
            $arbol[$idUsuario] = [
                'nombre' => $nombreUsuario,
                'username' => htmlspecialchars((string) ($rel->username ?? ''), ENT_QUOTES, 'UTF-8'),
                'modulos' => []
            ];
        }

        if (!isset($arbol[$idUsuario]['modulos'][$idModulo])) {
            $arbol[$idUsuario]['modulos'][$idModulo] = [
                'nombre' => $nombreModulo,
                'operaciones' => []
            ];
        }

        $arbol[$idUsuario]['modulos'][$idModulo]['operaciones'][] = $rel;
    }
    return $arbol;
}

$arbolPermisos = construirArbolPermisos($relaciones);
$relacionesEliminadas = array_filter($relaciones, function ($rel) {
    return !empty($rel->fec_delete);
});
$totalPermisosActivos = count($relaciones) - count($relacionesEliminadas);
?>

<link rel="stylesheet" href="/dlgc_rrhh/src/permisos_usuarios/permisos_usuarios.css">

<?php include_once 'encab_permisos_usuarios.php'; ?>
<?php require_once __DIR__ . '/../../app/alert_helper.php'; ?>
<?php render_alert_banner(); ?>


<main class="main-container">
    <div class="row">
        <div class="col-12">
            <h1>Permisos por Usuario Registrados</h1>

            <div class="d-flex gap-3 mb-4">
                <span class="badge bg-primary p-2">Total: <?php echo $totalPermisosActivos; ?> permisos</span>
                <span class="badge bg-danger p-2" id="btnEliminados" data-deleted-modal-target="#modalEliminados" style="cursor:pointer;">Eliminados: <?php echo count($relacionesEliminadas); ?></span>
            </div>

            <div class="modal" id="modalEliminados" data-deleted-modal aria-labelledby="modalEliminadosLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header bg-danger text-white">
                            <h5 class="modal-title" id="modalEliminadosLabel">Permisos por Usuario Eliminados</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                        </div>
                        <div class="modal-body">
                            <?php if (count($relacionesEliminadas) === 0): ?>
                                <div class="alert alert-info">No hay registros eliminados.</div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Usuario</th>
                                                <th>Operación</th>
                                                <th>Módulo</th>
                                                <th>Eliminado por</th>
                                                <th>Fecha Eliminacion</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($relacionesEliminadas as $rel): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars((string) ($rel->nombre_usuario ?? $rel->username ?? $rel->id_usuario ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td><?php echo htmlspecialchars((string) ($rel->nombre_operacion ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td><?php echo htmlspecialchars((string) ($rel->nombre_modulo ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td><?php echo htmlspecialchars((string) ($rel->usr_delete ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td><?php echo htmlspecialchars((string) ($rel->fec_delete ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td>
                                                        <?php if (has_module_permission(21, 'actualizar')): ?>
                                                        <?php if (has_module_permission($moduleId, 'restaurar')): ?>
                                                        <form method="POST" action="restore_permisos_usuarios.php" style="display:inline-block;">
                                                            <?php echo csrf_input(); ?>
                                                            <input type="hidden" name="id_usuario" value="<?php echo htmlspecialchars((string) $rel->id_usuario, ENT_QUOTES, 'UTF-8'); ?>">
                                                            <button type="submit" class="btn btn-sm btn-restore">Restaurar</button>
                                                        </form>
                                                        <?php endif; ?>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="permisos-acordeon">
                <?php if (empty($arbolPermisos)): ?>
                    <div class="alert alert-info">No hay permisos registrados.</div>
                <?php else: ?>
                    <?php foreach ($arbolPermisos as $idUsuario => $usuario): ?>
                        <div class="pu-acordeon-item">
                            <button type="button" class="pu-acordeon-cabecera pu-cabecera-usuario" aria-expanded="false">
                                <span class="pu-cabecera-texto">
                                    <i class="fas fa-user-circle pu-icono"></i>
                                    <?php echo $usuario['nombre']; ?>
                                    <?php if ($usuario['username'] !== ''): ?>
                                        <small class="pu-cabecera-sub">@<?php echo $usuario['username']; ?></small>
                                    <?php endif; ?>
                                </span>
                                <span class="pu-cabecera-meta">
                                    <span class="badge bg-primary p-2"><?php echo count($usuario['modulos']); ?> módulo(s)</span>
                                    <i class="fas fa-chevron-down pu-flecha"></i>
                                </span>
                            </button>
                            <div class="pu-acordeon-cuerpo pu-cuerpo-usuario">
                                <?php foreach ($usuario['modulos'] as $idModulo => $modulo): ?>
                                    <div class="pu-acordeon-item">
                                        <button type="button" class="pu-acordeon-cabecera pu-cabecera-modulo" aria-expanded="false">
                                            <span class="pu-cabecera-texto">
                                                <i class="fas fa-folder pu-icono"></i>
                                                <?php echo $modulo['nombre']; ?>
                                            </span>
                                            <span class="pu-cabecera-meta">
                                                <span class="badge bg-primary p-2"><?php echo count($modulo['operaciones']); ?> op.</span>
                                                <i class="fas fa-chevron-down pu-flecha"></i>
                                            </span>
                                        </button>
                                        <div class="pu-acordeon-cuerpo pu-cuerpo-modulo">
                                            <div class="table-responsive">
                                                <table class="table table-hover">
                                                    <thead class="table-primary">
                                                        <tr>
                                                            <th>Operación</th>
                                                            <th>Usuario Inserto</th>
                                                            <th>Fecha Inserción</th>
                                                            <th>Acciones</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($modulo['operaciones'] as $rel): ?>
                                                            <tr>
                                                                <td><?php echo htmlspecialchars((string) ($rel->nombre_operacion ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                                                                <td><?php echo htmlspecialchars((string) ($rel->usr_insert ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                                                                <td><?php echo htmlspecialchars((string) ($rel->fec_insert ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                                                                <td class="actions-cell">
                                                                    <?php if (has_module_permission(21, 'actualizar')): ?>
                                                                    <a class="btn btn-warning btn-sm" href="editar_permisos_usuarios.php?id_usuario=<?php echo urlencode((string) $rel->id_usuario); ?>&id_operacion=<?php echo (int) $rel->id_operacion; ?>">Editar</a>
                                                                    <?php endif; ?>
                                                                    <?php if (has_module_permission(21, 'eliminar')): ?>
                                                                    <form method="POST" action="eliminar_permisos_usuarios.php" style="display:inline-block;">
                                                                        <?php echo csrf_input(); ?>
                                                                        <input type="hidden" name="id_usuario" value="<?php echo htmlspecialchars((string) $rel->id_usuario, ENT_QUOTES, 'UTF-8'); ?>">
                                                                        <input type="hidden" name="id_operacion" value="<?php echo (int) $rel->id_operacion; ?>">
                                                                        <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                                                                    </form>
                                                                    <?php endif; ?>
                                                                </td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<script>
(function () {
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.pu-acordeon-cabecera').forEach(function (cabecera) {
            cabecera.addEventListener('click', function () {
                var expandido = this.getAttribute('aria-expanded') === 'true';
                var cuerpo = this.nextElementSibling;
                var flecha = this.querySelector('.pu-flecha');

                this.setAttribute('aria-expanded', String(!expandido));
                if (cuerpo) {
                    cuerpo.classList.toggle('pu-abierto', !expandido);
                }
                if (flecha) {
                    flecha.classList.toggle('pu-rotada', !expandido);
                }
            });
        });
    });
})();
</script>
<?php include_once 'pie_permisos_usuarios.php'; ?>