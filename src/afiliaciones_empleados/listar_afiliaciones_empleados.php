<?php

$moduleId = 7;
require_once __DIR__ . '/../../app/src_guard.php';
require_once __DIR__ . '/../../app/conexion.php';

$sentencia = $conexion->query(<<<SQL
    SELECT
        ae.id_usuario,
        COALESCE(
            NULLIF(TRIM(CONCAT_WS(' ', u.primer_nombre, NULLIF(u.segundo_nombre, ''), u.primer_apellido, NULLIF(u.segundo_apellido, ''))), ''),
            'Sin nombre'
        ) AS nombre_usuario,
        eps.nombre_eps,
        arl.nombre_arl,
        caja.nombre_caja,
        pension.nombre_pension,
        cesantia.nombre_cesantia,
        ae.fec_delete
    FROM t_afiliaciones_empleados ae
    LEFT JOIN t_usuarios u ON u.id_usuario = ae.id_usuario
    LEFT JOIN t_eps eps ON eps.id_eps = ae.id_eps
    LEFT JOIN t_arl arl ON arl.id_arl = ae.id_arl
    LEFT JOIN t_caja_compensacion caja ON caja.id_caja = ae.id_caja
    LEFT JOIN t_pension pension ON pension.id_pension = ae.id_pension
    LEFT JOIN t_cesantias cesantia ON cesantia.id_cesantia = ae.id_cesantia
    ORDER BY nombre_usuario, ae.id_usuario
SQL
);
$afiliaciones_empleadosRegistros = $sentencia->fetchAll(PDO::FETCH_OBJ);

$afiliaciones_empleadosActivas = array_filter($afiliaciones_empleadosRegistros, function ($afiliaciones_empleados) {
    return empty($afiliaciones_empleados->fec_delete);
});

$afiliaciones_empleadosEliminadas = array_filter($afiliaciones_empleadosRegistros, function ($afiliaciones_empleados) {
    return !empty($afiliaciones_empleados->fec_delete);
});
?>

<?php include_once 'encab_afiliaciones_empleados.php'; ?>

<main class="main-container">
    <div class="row">
        <div class="col-12">
            <h1>Afiliaciones de Empleados Registradas</h1>

            <div class="d-flex gap-3 mb-4">
                <span class="badge bg-primary p-2">Activas: <?php echo count($afiliaciones_empleadosActivas); ?></span>
                <span class="badge bg-danger p-2" id="btnEliminados" data-deleted-modal-target="#modalEliminados" style="cursor:pointer;">Eliminadas: <?php echo count($afiliaciones_empleadosEliminadas); ?></span>
            </div>

            <div class="modal" id="modalEliminados" data-deleted-modal aria-labelledby="modalEliminadosLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header bg-danger text-white">
                            <h5 class="modal-title" id="modalEliminadosLabel">Afiliaciones Eliminadas</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                        </div>
                        <div class="modal-body">
                            <?php if (count($afiliaciones_empleadosEliminadas) === 0): ?>
                                <div class="alert alert-info">No hay afiliaciones eliminadas.</div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Usuario</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($afiliaciones_empleadosEliminadas as $afiliaciones_empleados): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars((string) $afiliaciones_empleados->nombre_usuario, ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td>
                                                        <?php if (has_module_permission($moduleId, 'restaurar')): ?>
                                                        <form method="POST" action="restore_afiliaciones_empleados.php" style="display:inline-block;">
                                                            <?php echo csrf_input(); ?>
                                                            <input type="hidden" name="id_usuario" value="<?php echo htmlspecialchars((string) $afiliaciones_empleados->id_usuario, ENT_QUOTES, 'UTF-8'); ?>">
                                                            <button type="submit" class="btn btn-sm btn-restore">Restaurar</button>
                                                        </form>
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
                                <th>EPS</th>
                                <th>ARL</th>
                                <th>Caja Comp.</th>
                                <th>Pensión</th>
                                <th>Cesantías</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($afiliaciones_empleadosActivas) === 0): ?>
                                <tr><td colspan="7" class="text-center">No hay registros</td></tr>
                            <?php else: ?>
                                <?php foreach ($afiliaciones_empleadosActivas as $afiliaciones_empleados): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars((string) $afiliaciones_empleados->nombre_usuario, ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars((string) $afiliaciones_empleados->nombre_eps, ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars((string) $afiliaciones_empleados->nombre_arl, ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars((string) $afiliaciones_empleados->nombre_caja, ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars((string) $afiliaciones_empleados->nombre_pension, ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars((string) $afiliaciones_empleados->nombre_cesantia, ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td class="actions-cell">
                                            <?php if (has_module_permission(7, 'actualizar')): ?>
                                            <a class="btn btn-warning btn-sm" href="editar_afiliaciones_empleados.php?id_usuario=<?php echo urlencode((string) $afiliaciones_empleados->id_usuario); ?>">Editar</a>
                                            <?php endif; ?>
                                            <?php if (has_module_permission(7, 'eliminar')): ?>
                                            <form method="POST" action="eliminar_afiliaciones_empleados.php" style="display:inline-block;">
                                                <?php echo csrf_input(); ?>
                                                <input type="hidden" name="id_usuario" value="<?php echo htmlspecialchars((string) $afiliaciones_empleados->id_usuario, ENT_QUOTES, 'UTF-8'); ?>">
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
                    <?php foreach ($afiliaciones_empleadosActivas as $afiliaciones_empleados): ?>
                        <div class="col-12 mb-3">
                            <div class="mantenimiento-card card">
                                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0">Usuario: <?php echo htmlspecialchars((string) $afiliaciones_empleados->nombre_usuario, ENT_QUOTES, 'UTF-8'); ?></h5>
                                    <div class="d-flex gap-2">
                                        <?php if (has_module_permission(7, 'actualizar')): ?>
                                            <a class="btn btn-warning btn-sm" href="editar_afiliaciones_empleados.php?id_usuario=<?php echo urlencode((string) $afiliaciones_empleados->id_usuario); ?>">Editar</a>
                                            <?php endif; ?>
                                        <?php if (has_module_permission(7, 'eliminar')): ?>
                                            <form method="POST" action="eliminar_afiliaciones_empleados.php" style="display:inline-block;">
                                                <?php echo csrf_input(); ?>
                                                <input type="hidden" name="id_usuario" value="<?php echo htmlspecialchars((string) $afiliaciones_empleados->id_usuario, ENT_QUOTES, 'UTF-8'); ?>">
                                                <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                                            </form>
                                            <?php endif; ?>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <p class="card-text"><strong>EPS:</strong> <?php echo htmlspecialchars((string) $afiliaciones_empleados->nombre_eps, ENT_QUOTES, 'UTF-8'); ?></p>
                                    <p class="card-text"><strong>ARL:</strong> <?php echo htmlspecialchars((string) $afiliaciones_empleados->nombre_arl, ENT_QUOTES, 'UTF-8'); ?></p>
                                    <p class="card-text"><strong>Caja Compensación:</strong> <?php echo htmlspecialchars((string) $afiliaciones_empleados->nombre_caja, ENT_QUOTES, 'UTF-8'); ?></p>
                                    <p class="card-text"><strong>Pensión:</strong> <?php echo htmlspecialchars((string) $afiliaciones_empleados->nombre_pension, ENT_QUOTES, 'UTF-8'); ?></p>
                                    <p class="card-text"><strong>Cesantías:</strong> <?php echo htmlspecialchars((string) $afiliaciones_empleados->nombre_cesantia, ENT_QUOTES, 'UTF-8'); ?></p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include_once 'pie_afiliaciones_empleados.php'; ?>