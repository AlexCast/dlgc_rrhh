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
    ORDER BY nombre_usuario, m.nombre_modulo, o.nombre_operacion
');
$relaciones = $sentencia->fetchAll(PDO::FETCH_OBJ);

$relacionesEliminadas = array_filter($relaciones, function ($rel) {
    return !empty($rel->fec_delete);
});
?>

<?php include_once 'encab_permisos_usuarios.php'; ?>
<?php require_once __DIR__ . '/../../app/alert_helper.php'; ?>
<?php render_alert_banner(); ?>


<main class="main-container">
    <div class="row">
        <div class="col-12">
            <h1>Permisos por Usuario Registrados</h1>

            <div class="d-flex gap-3 mb-4">
                <span class="badge bg-primary p-2">Total: <?php echo count($relaciones); ?> registros</span>
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

            <div class="desktop-view">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-primary">
                            <tr>
                                <th>Usuario</th>
                                <th>Operación</th>
                                <th>Módulo</th>
                                <th>Usuario Inserto</th>
                                <th>Fecha Insercion</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($relaciones) === 0): ?>
                                <tr><td colspan="6" class="text-center">No hay registros</td></tr>
                            <?php else: ?>
                                <?php foreach ($relaciones as $rel): ?>
                                    <?php if (!empty($rel->fec_delete)) { continue; } ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars((string) ($rel->nombre_usuario ?? $rel->username ?? $rel->id_usuario ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars((string) ($rel->nombre_operacion ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars((string) ($rel->nombre_modulo ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
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
                                                <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                                            </form>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mobile-view">
                <div class="row">
                    <?php foreach ($relaciones as $rel): ?>
                        <?php if (!empty($rel->fec_delete)) { continue; } ?>
                        <div class="col-12 mb-3">
                            <div class="mantenimiento-card card">
                                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0"><?php echo htmlspecialchars((string) ($rel->nombre_usuario ?? $rel->username ?? $rel->id_usuario ?? ''), ENT_QUOTES, 'UTF-8'); ?></h5>
                                    <div class="d-flex gap-2">
                                        <?php if (has_module_permission(21, 'actualizar')): ?>
                                        <a class="btn btn-warning btn-sm" href="editar_permisos_usuarios.php?id_usuario=<?php echo urlencode((string) $rel->id_usuario); ?>&id_operacion=<?php echo (int) $rel->id_operacion; ?>">Editar</a>
                                        <?php endif; ?>
                                        <?php if (has_module_permission(21, 'eliminar')): ?>
                                        <form method="POST" action="eliminar_permisos_usuarios.php" style="display:inline-block;">
                                            <?php echo csrf_input(); ?>
                                            <input type="hidden" name="id_usuario" value="<?php echo htmlspecialchars((string) $rel->id_usuario, ENT_QUOTES, 'UTF-8'); ?>">
                                            <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                                        </form>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <ul class="list-group list-group-flush">
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <strong>Operación</strong>
                                            <span><?php echo htmlspecialchars((string) ($rel->nombre_operacion ?? ''), ENT_QUOTES, 'UTF-8'); ?></span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <strong>Módulo</strong>
                                            <span><?php echo htmlspecialchars((string) ($rel->nombre_modulo ?? ''), ENT_QUOTES, 'UTF-8'); ?></span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <strong>Usuario Inserto</strong>
                                            <span><?php echo htmlspecialchars((string) ($rel->usr_insert ?? ''), ENT_QUOTES, 'UTF-8'); ?></span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <strong>Fecha Insercion</strong>
                                            <span><?php echo htmlspecialchars((string) ($rel->fec_insert ?? ''), ENT_QUOTES, 'UTF-8'); ?></span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include_once 'pie_permisos_usuarios.php'; ?>