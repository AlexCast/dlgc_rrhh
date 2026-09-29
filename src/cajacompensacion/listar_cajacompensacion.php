<?php

$moduleId = 11;
require_once __DIR__ . '/../../app/src_guard.php';
require_once __DIR__ . '/../../app/conexion.php';

$sentencia = $conexion->query('
    SELECT id_caja, nombre_caja, fec_delete
    FROM t_caja_compensacion
    ORDER BY nombre_caja
');
$cajasRegistros = $sentencia->fetchAll(PDO::FETCH_OBJ);

$cajasActivas = array_filter($cajasRegistros, function ($caja) {
    return empty($caja->fec_delete);
});

$cajasEliminadas = array_filter($cajasRegistros, function ($caja) {
    return !empty($caja->fec_delete);
});
?>

<?php include_once 'encab_cajacompensacion.php'; ?>
<?php require_once __DIR__ . '/../../app/alert_helper.php'; ?>
<?php render_alert_banner(); ?>


<main class="main-container">
    <div class="row">
        <div class="col-12">
            <h1>Cajas de Compensación Registradas</h1>

            <div class="d-flex gap-3 mb-4">
                <span class="badge bg-primary p-2">Activas: <?php echo count($cajasActivas); ?></span>
                <span class="badge bg-danger p-2" id="btnEliminados" data-deleted-modal-target="#modalEliminados" style="cursor:pointer;">Eliminadas: <?php echo count($cajasEliminadas); ?></span>
            </div>

            <div class="modal" id="modalEliminados" data-deleted-modal aria-labelledby="modalEliminadosLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header bg-danger text-white">
                            <h5 class="modal-title" id="modalEliminadosLabel">Cajas de Compensación Eliminadas</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                        </div>
                        <div class="modal-body">
                            <?php if (count($cajasEliminadas) === 0): ?>
                                <div class="alert alert-info">No hay cajas de compensación eliminadas.</div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Nombre</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($cajasEliminadas as $caja): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars((string) $caja->nombre_caja, ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td>
                                                        <?php if (has_module_permission($moduleId, 'restaurar')): ?>
                                                        <form method="POST" action="restore_cajacompensacion.php" style="display:inline-block;">
                                                            <?php echo csrf_input(); ?>
                                                            <input type="hidden" name="id_caja" value="<?php echo (int) $caja->id_caja; ?>">
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
                                <th>Nombre</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($cajasActivas) === 0): ?>
                                <tr><td colspan="2" class="text-center">No hay registros</td></tr>
                            <?php else: ?>
                                <?php foreach ($cajasActivas as $caja): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars((string) $caja->nombre_caja, ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td class="actions-cell">
                                            <?php if (has_module_permission(11, 'actualizar')): ?>
                                            <a class="btn btn-warning btn-sm" href="editar_cajacompensacion.php?id_caja=<?php echo (int) $caja->id_caja; ?>">Editar</a>
                                            <?php endif; ?>
                                            <?php if (has_module_permission(11, 'eliminar')): ?>
                                            <form method="POST" action="eliminar_cajacompensacion.php" style="display:inline-block;">
                                                <?php echo csrf_input(); ?>
                                                <input type="hidden" name="id_caja" value="<?php echo (int) $caja->id_caja; ?>">
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
                    <?php foreach ($cajasActivas as $caja): ?>
                        <div class="col-12 mb-3">
                            <div class="mantenimiento-card card">
                                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0"><?php echo htmlspecialchars((string) $caja->nombre_caja, ENT_QUOTES, 'UTF-8'); ?></h5>
                                    <div class="d-flex gap-2">
                                        <?php if (has_module_permission(11, 'actualizar')): ?>
                                            <a class="btn btn-warning btn-sm" href="editar_cajacompensacion.php?id_caja=<?php echo (int) $caja->id_caja; ?>">Editar</a>
                                            <?php endif; ?>
                                        <?php if (has_module_permission(11, 'eliminar')): ?>
                                            <form method="POST" action="eliminar_cajacompensacion.php" style="display:inline-block;">
                                                <?php echo csrf_input(); ?>
                                                <input type="hidden" name="id_caja" value="<?php echo (int) $caja->id_caja; ?>">
                                                <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                                            </form>
                                            <?php endif; ?>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <p class="card-text"><strong>Nombre:</strong> <?php echo htmlspecialchars((string) $caja->nombre_caja, ENT_QUOTES, 'UTF-8'); ?></p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include_once 'pie_cajacompensacion.php'; ?>