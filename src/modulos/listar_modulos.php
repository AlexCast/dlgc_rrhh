<?php

$moduleId = 18;
require_once __DIR__ . '/../../app/src_guard.php';
require_once __DIR__ . '/../../app/conexion.php';

$sentencia = $conexion->query("
    SELECT id_modulo, nombre_modulo, fec_delete
    FROM t_modulos
    ORDER BY id_modulo
");
$registros = $sentencia->fetchAll(PDO::FETCH_OBJ);

$activos = array_filter($registros, function ($row) {
    return empty($row->fec_delete);
});

$eliminados = array_filter($registros, function ($row) {
    return !empty($row->fec_delete);
});
?>

<?php include_once 'encab_modulos.php'; ?>

<main class="main-container">
    <div class="row">
        <div class="col-12">
            <h1>Módulos Registrados</h1>

            <div class="d-flex gap-3 mb-4">
                <span class="badge bg-primary p-2">Activos: <?php echo count($activos); ?></span>
                <span class="badge bg-danger p-2" id="btnEliminados" data-deleted-modal-target="#modalEliminados" style="cursor:pointer;">Eliminados: <?php echo count($eliminados); ?></span>
            </div>

            <div class="modal" id="modalEliminados" data-deleted-modal aria-labelledby="modalEliminadosLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header bg-danger text-white">
                            <h5 class="modal-title" id="modalEliminadosLabel">Módulos Eliminados</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                        </div>
                        <div class="modal-body">
                            <?php if (count($eliminados) === 0): ?>
                                <div class="alert alert-info">No hay registros eliminados.</div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>ID Módulo</th><th>Nombre Módulo</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($eliminados as $row): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars((string) ($row->id_modulo ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars((string) ($row->nombre_modulo ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                                        
                                                    <td>
                                                        <?php if (has_module_permission(18, 'actualizar')): ?>
                                                        <?php if (has_module_permission($moduleId, 'restaurar')): ?>
                                                        <form method="POST" action="restore_modulos.php" style="display:inline-block;">
                                                            <?php echo csrf_input(); ?>
                                                            <input type="hidden" name="id_modulo" value="<?php echo htmlspecialchars((string) $row->id_modulo, ENT_QUOTES, 'UTF-8'); ?>">
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
                                <th>ID Módulo</th><th>Nombre Módulo</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($activos) === 0): ?>
                                <tr><td colspan="3" class="text-center">No hay registros</td></tr>
                            <?php else: ?>
                                <?php foreach ($activos as $row): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars((string) ($row->id_modulo ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars((string) ($row->nombre_modulo ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                                        
                                        <td class="actions-cell">
                                            <?php if (has_module_permission(18, 'actualizar')): ?>
                                            <a class="btn btn-warning btn-sm" href="editar_modulos.php?id_modulo=<?php echo urlencode((string) $row->id_modulo); ?>">Editar</a>
                                            <?php endif; ?>
                                            <?php if (has_module_permission(18, 'eliminar')): ?>
                                            <form method="POST" action="eliminar_modulos.php" style="display:inline-block;">
                                                <?php echo csrf_input(); ?>
                                                <input type="hidden" name="id_modulo" value="<?php echo htmlspecialchars((string) $row->id_modulo, ENT_QUOTES, 'UTF-8'); ?>">
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
        </div>
    </div>
</main>

<?php include_once 'pie_modulos.php'; ?>