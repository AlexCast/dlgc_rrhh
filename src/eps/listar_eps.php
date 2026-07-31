<?php

$moduleId = 14;
require_once __DIR__ . '/../../app/src_guard.php';
require_once __DIR__ . '/../../app/conexion.php';

$sentencia = $conexion->query('
    SELECT id_eps, nombre_eps, fec_delete
    FROM t_eps
    ORDER BY nombre_eps
');
$epsRegistros = $sentencia->fetchAll(PDO::FETCH_OBJ);

$epsActivas = array_filter($epsRegistros, function ($eps) {
    return empty($eps->fec_delete);
});

$epsEliminadas = array_filter($epsRegistros, function ($eps) {
    return !empty($eps->fec_delete);
});
?>

<?php include_once 'encab_eps.php'; ?>
<?php require_once __DIR__ . '/../../app/alert_helper.php'; ?>
<?php render_alert_banner(); ?>


<main class="main-container">
    <div class="row">
        <div class="col-12">
            <h1>EPS Registradas</h1>

            <div class="d-flex gap-3 mb-4">
                <span class="badge bg-primary p-2">Activas: <?php echo count($epsActivas); ?></span>
                <span class="badge bg-danger p-2" id="btnEliminados" data-deleted-modal-target="#modalEliminados" style="cursor:pointer;">Eliminadas: <?php echo count($epsEliminadas); ?></span>
            </div>

            <div class="modal" id="modalEliminados" data-deleted-modal aria-labelledby="modalEliminadosLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header bg-danger text-white">
                            <h5 class="modal-title" id="modalEliminadosLabel">EPS Eliminadas</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                        </div>
                        <div class="modal-body">
                            <?php if (count($epsEliminadas) === 0): ?>
                                <div class="alert alert-info">No hay EPS eliminadas.</div>
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
                                            <?php foreach ($epsEliminadas as $eps): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars((string) $eps->nombre_eps, ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td>
                                                        <?php if (has_module_permission($moduleId, 'restaurar')): ?>
                                                        <form method="POST" action="restore_eps.php" style="display:inline-block;">
                                                            <?php echo csrf_input(); ?>
                                                            <input type="hidden" name="id_eps" value="<?php echo (int) $eps->id_eps; ?>">
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
                            <?php if (count($epsActivas) === 0): ?>
                                <tr><td colspan="2" class="text-center">No hay registros</td></tr>
                            <?php else: ?>
                                <?php foreach ($epsActivas as $eps): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars((string) $eps->nombre_eps, ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td class="actions-cell">
                                            <?php if (has_module_permission(14, 'actualizar')): ?>
                                            <a class="btn btn-warning btn-sm" href="editar_eps.php?id_eps=<?php echo (int) $eps->id_eps; ?>">Editar</a>
                                            <?php endif; ?>
                                            <?php if (has_module_permission(14, 'eliminar')): ?>
                                            <form method="POST" action="eliminar_eps.php" style="display:inline-block;">
                                                <?php echo csrf_input(); ?>
                                                <input type="hidden" name="id_eps" value="<?php echo (int) $eps->id_eps; ?>">
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
                    <?php foreach ($epsActivas as $eps): ?>
                        <div class="col-12 mb-3">
                            <div class="mantenimiento-card card">
                                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0"><?php echo htmlspecialchars((string) $eps->nombre_eps, ENT_QUOTES, 'UTF-8'); ?></h5>
                                    <div class="d-flex gap-2">
                                        <?php if (has_module_permission(14, 'actualizar')): ?>
                                            <a class="btn btn-warning btn-sm" href="editar_eps.php?id_eps=<?php echo (int) $eps->id_eps; ?>">Editar</a>
                                            <?php endif; ?>
                                        <?php if (has_module_permission(14, 'eliminar')): ?>
                                            <form method="POST" action="eliminar_eps.php" style="display:inline-block;">
                                                <?php echo csrf_input(); ?>
                                                <input type="hidden" name="id_eps" value="<?php echo (int) $eps->id_eps; ?>">
                                                <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                                            </form>
                                            <?php endif; ?>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <p class="card-text"><strong>Nombre:</strong> <?php echo htmlspecialchars((string) $eps->nombre_eps, ENT_QUOTES, 'UTF-8'); ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include_once 'pie_eps.php'; ?>