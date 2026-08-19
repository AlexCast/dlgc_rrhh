<?php

$moduleId = 15;
require_once __DIR__ . '/../../app/src_guard.php';
require_once __DIR__ . '/../../app/conexion.php';

$sentencia = $conexion->query("
    SELECT 
        n.id_nomina, 
        n.id_usuario, 
        n.id_banco, 
        n.num_cuenta, 
        n.salario, 
        n.fec_delete,
        b.nombre_banco,
        CONCAT_WS(' ', u.primer_nombre, NULLIF(u.segundo_nombre, ''), u.primer_apellido, NULLIF(u.segundo_apellido, '')) AS nombre_completo
    FROM t_nomina n
    INNER JOIN t_bancos b ON n.id_banco = b.id_banco
    INNER JOIN t_empleados e ON n.id_usuario = e.id_usuario
    INNER JOIN t_usuarios u ON e.id_usuario = u.id_usuario
    ORDER BY nombre_completo
");
$nominaRegistros = $sentencia->fetchAll(PDO::FETCH_OBJ);

$nominasActivas = array_filter($nominaRegistros, function ($nomina) {
    return empty($nomina->fec_delete);
});

$nominasEliminadas = array_filter($nominaRegistros, function ($nomina) {
    return !empty($nomina->fec_delete);
});
?>

<?php include_once 'encab_nomina.php'; ?>
<?php require_once __DIR__ . '/../../app/alert_helper.php'; ?>
<?php render_alert_banner(); ?>


<main class="main-container">
    <div class="row">
        <div class="col-12">
            <h1>Nóminas Registradas</h1>

            <div class="d-flex gap-3 mb-4">
                <span class="badge bg-primary p-2">Activas: <?php echo count($nominasActivas); ?></span>
                <span class="badge bg-danger p-2" id="btnEliminados" data-deleted-modal-target="#modalEliminados" style="cursor:pointer;">Eliminadas: <?php echo count($nominasEliminadas); ?></span>
            </div>

            <div class="modal" id="modalEliminados" data-deleted-modal aria-labelledby="modalEliminadosLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header bg-danger text-white">
                            <h5 class="modal-title" id="modalEliminadosLabel">Nóminas Eliminadas</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                        </div>
                        <div class="modal-body">
                            <?php if (count($nominasEliminadas) === 0): ?>
                                <div class="alert alert-info">No hay nóminas eliminadas.</div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Empleado</th>
                                                <th>Banco</th>
                                                <th>Cuenta</th>
                                                <th>Salario</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($nominasEliminadas as $nomina): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars((string) $nomina->nombre_completo, ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td><?php echo htmlspecialchars((string) $nomina->nombre_banco, ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td><?php echo htmlspecialchars((string) $nomina->num_cuenta, ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td>$<?php echo number_format((float) $nomina->salario, 2); ?></td>
                                                    <td>
                                                        <?php if (has_module_permission($moduleId, 'restaurar')): ?>
                                                        <form method="POST" action="restore_nomina.php" style="display:inline-block;">
                                                            <?php echo csrf_input(); ?>
                                                            <input type="hidden" name="id_nomina" value="<?php echo (int) $nomina->id_nomina; ?>">
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
                                <th>Empleado</th>
                                <th>Banco</th>
                                <th>Cuenta</th>
                                <th>Salario</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($nominasActivas) === 0): ?>
                                <tr><td colspan="5" class="text-center">No hay registros</td></tr>
                            <?php else: ?>
                                <?php foreach ($nominasActivas as $nomina): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars((string) $nomina->nombre_completo, ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars((string) $nomina->nombre_banco, ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars((string) $nomina->num_cuenta, ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td>$<?php echo number_format((float) $nomina->salario, 2); ?></td>
                                        <td class="actions-cell">
                                            <?php if (has_module_permission(15, 'actualizar')): ?>
                                            <a class="btn btn-warning btn-sm" href="editar_nomina.php?id_nomina=<?php echo (int) $nomina->id_nomina; ?>">Editar</a>
                                            <?php endif; ?>
                                            <?php if (has_module_permission(15, 'eliminar')): ?>
                                            <form method="POST" action="eliminar_nomina.php" style="display:inline-block;">
                                                <?php echo csrf_input(); ?>
                                                <input type="hidden" name="id_nomina" value="<?php echo (int) $nomina->id_nomina; ?>">
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
                    <?php foreach ($nominasActivas as $nomina): ?>
                        <div class="col-12 mb-3">
                            <div class="mantenimiento-card card">
                                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0"><?php echo htmlspecialchars((string) $nomina->nombre_completo, ENT_QUOTES, 'UTF-8'); ?></h5>
                                    <div class="d-flex gap-2">
                                        <?php if (has_module_permission(15, 'actualizar')): ?>
                                            <a class="btn btn-warning btn-sm" href="editar_nomina.php?id_nomina=<?php echo (int) $nomina->id_nomina; ?>">Editar</a>
                                            <?php endif; ?>
                                        <?php if (has_module_permission(15, 'eliminar')): ?>
                                            <form method="POST" action="eliminar_nomina.php" style="display:inline-block;">
                                                <?php echo csrf_input(); ?>
                                                <input type="hidden" name="id_nomina" value="<?php echo (int) $nomina->id_nomina; ?>">
                                                <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                                            </form>
                                            <?php endif; ?>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <p class="card-text mb-1"><strong>Empleado:</strong> <?php echo htmlspecialchars((string) $nomina->nombre_completo, ENT_QUOTES, 'UTF-8'); ?></p>
                                    <p class="card-text mb-1"><strong>Banco:</strong> <?php echo htmlspecialchars((string) $nomina->nombre_banco, ENT_QUOTES, 'UTF-8'); ?></p>
                                    <p class="card-text mb-1"><strong>Cuenta:</strong> <?php echo htmlspecialchars((string) $nomina->num_cuenta, ENT_QUOTES, 'UTF-8'); ?></p>
                                    <p class="card-text mb-0"><strong>Salario:</strong> $<?php echo number_format((float) $nomina->salario, 2); ?></p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include_once 'pie_nomina.php'; ?>