<?php

$moduleId = 28;
require_once __DIR__ . '/../../app/src_guard.php';
require_once __DIR__ . '/../../app/conexion.php';

$sentencia = $conexion->query('
    SELECT id_festivo, fecha, descripcion, tipo_festivo, descuenta_salario, fec_delete
    FROM t_dias_festivos
    ORDER BY ABS(fecha - CURRENT_DATE)
');
$festivoRegistros = $sentencia->fetchAll(PDO::FETCH_OBJ);

$festivoActivos = array_filter($festivoRegistros, function ($f) {
    return empty($f->fec_delete);
});

$festivoEliminados = array_filter($festivoRegistros, function ($f) {
    return !empty($f->fec_delete) && $f->tipo_festivo === 'EMPRESA';
});
?>

<?php include_once 'encab_dias_festivos.php'; ?>
<?php require_once __DIR__ . '/../../app/alert_helper.php'; ?>
<?php render_alert_banner(); ?>

<main class="main-container">
    <div class="row">
        <div class="col-12">
            <h1>Días Festivos</h1>
            <p>Los festivos <strong>Nacional</strong> se calculan automáticamente para Colombia (Ley Emiliani) y no son editables. Los festivos <strong>Empresa</strong> los administra RRHH y sí pueden marcarse para descontar del sueldo.</p>

            <div class="d-flex gap-3 mb-4">
                <span class="badge bg-primary p-2">Activos: <?php echo count($festivoActivos); ?></span>
                <span class="badge bg-danger p-2" id="btnEliminados" data-deleted-modal-target="#modalEliminados" style="cursor:pointer;">Eliminados (Empresa): <?php echo count($festivoEliminados); ?></span>
            </div>

            <div class="modal" id="modalEliminados" data-deleted-modal aria-labelledby="modalEliminadosLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header bg-danger text-white">
                            <h5 class="modal-title" id="modalEliminadosLabel">Festivos de Empresa Eliminados</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                        </div>
                        <div class="modal-body">
                            <?php if (count($festivoEliminados) === 0): ?>
                                <div class="alert alert-info">No hay festivos de empresa eliminados.</div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Fecha</th>
                                                <th>Descripción</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($festivoEliminados as $f): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars((string) $f->fecha); ?></td>
                                                    <td><?php echo htmlspecialchars((string) $f->descripcion, ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td>
                                                        <?php if (has_module_permission(28, 'restaurar')): ?>
                                                        <form method="POST" action="restore_dias_festivos.php" style="display:inline-block;">
                                                            <?php echo csrf_input(); ?>
                                                            <input type="hidden" name="id_festivo" value="<?php echo (int) $f->id_festivo; ?>">
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
                                <th>Fecha</th>
                                <th>Descripción</th>
                                <th>Tipo</th>
                                <th>¿Descuenta sueldo?</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($festivoActivos) === 0): ?>
                                <tr><td colspan="5" class="text-center">No hay registros</td></tr>
                            <?php else: ?>
                                <?php foreach ($festivoActivos as $f): ?>
                                    <?php $esEmpresa = $f->tipo_festivo === 'EMPRESA'; ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars((string) $f->fecha); ?></td>
                                        <td><?php echo htmlspecialchars((string) $f->descripcion, ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td>
                                            <span class="badge <?php echo $esEmpresa ? 'bg-warning text-dark' : 'bg-secondary'; ?>">
                                                <?php echo $esEmpresa ? 'Empresa' : 'Nacional'; ?>
                                            </span>
                                        </td>
                                        <td><?php echo filter_var($f->descuenta_salario, FILTER_VALIDATE_BOOLEAN) ? 'Sí' : 'No'; ?></td>
                                        <td class="actions-cell">
                                            <?php if ($esEmpresa): ?>
                                                <?php if (has_module_permission(28, 'actualizar')): ?>
                                                <a class="btn btn-warning btn-sm" href="editar_dias_festivos.php?id_festivo=<?php echo (int) $f->id_festivo; ?>">Editar</a>
                                                <?php endif; ?>
                                                <?php if (has_module_permission(28, 'eliminar')): ?>
                                                <form method="POST" action="eliminar_dias_festivos.php" style="display:inline-block;">
                                                    <?php echo csrf_input(); ?>
                                                    <input type="hidden" name="id_festivo" value="<?php echo (int) $f->id_festivo; ?>">
                                                    <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                                                </form>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <span class="actions-placeholder">Solo lectura</span>
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
                    <?php foreach ($festivoActivos as $f): ?>
                        <?php $esEmpresa = $f->tipo_festivo === 'EMPRESA'; ?>
                        <div class="col-12 mb-3">
                            <div class="mantenimiento-card card">
                                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0"><?php echo htmlspecialchars((string) $f->fecha); ?></h5>
                                    <div class="d-flex gap-2">
                                        <?php if ($esEmpresa): ?>
                                            <?php if (has_module_permission(28, 'actualizar')): ?>
                                            <a class="btn btn-warning btn-sm" href="editar_dias_festivos.php?id_festivo=<?php echo (int) $f->id_festivo; ?>">Editar</a>
                                            <?php endif; ?>
                                            <?php if (has_module_permission(28, 'eliminar')): ?>
                                            <form method="POST" action="eliminar_dias_festivos.php" style="display:inline-block;">
                                                <?php echo csrf_input(); ?>
                                                <input type="hidden" name="id_festivo" value="<?php echo (int) $f->id_festivo; ?>">
                                                <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                                            </form>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="actions-placeholder">Solo lectura</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <p class="card-text"><strong>Descripción:</strong> <?php echo htmlspecialchars((string) $f->descripcion, ENT_QUOTES, 'UTF-8'); ?></p>
                                    <p class="card-text"><strong>Tipo:</strong>
                                        <span class="badge <?php echo $esEmpresa ? 'bg-warning text-dark' : 'bg-secondary'; ?>">
                                            <?php echo $esEmpresa ? 'Empresa' : 'Nacional'; ?>
                                        </span>
                                    </p>
                                    <p class="card-text"><strong>¿Descuenta sueldo?:</strong> <?php echo filter_var($f->descuenta_salario, FILTER_VALIDATE_BOOLEAN) ? 'Sí' : 'No'; ?></p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include_once 'pie_dias_festivos.php'; ?>
