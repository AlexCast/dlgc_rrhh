<?php

$moduleId = 13;
require_once __DIR__ . '/../../app/src_guard.php';
require_once __DIR__ . '/../../app/conexion.php';

function mostrarValor(?string $valor): string
{
    $valorLimpio = trim((string) $valor);

    return $valorLimpio !== ''
        ? htmlspecialchars($valorLimpio, ENT_QUOTES, 'UTF-8')
        : 'N/A';
}

$sentencia = $conexion->query("
    SELECT 
        c.id_contrato,
        c.id_usuario,
        c.id_area,
        c.puesto,
        c.fecha_inicio_puesto,
        c.fecha_fin_puesto,
        c.tipo_contrato,
        c.direccion_oficina,
        c.fec_delete,
        a.nombre_area,
        CONCAT_WS(' ', u.primer_nombre, NULLIF(u.segundo_nombre, ''), u.primer_apellido, NULLIF(u.segundo_apellido, '')) AS nombre_completo
    FROM t_contratos_empleados c
    INNER JOIN t_empleados e ON c.id_usuario = e.id_usuario
    INNER JOIN t_usuarios u ON e.id_usuario = u.id_usuario
    INNER JOIN t_areas a ON c.id_area = a.id_area
    ORDER BY nombre_completo, c.id_contrato
");
$contratosRegistros = $sentencia->fetchAll(PDO::FETCH_OBJ);

$contratosActivos = array_filter($contratosRegistros, function ($contrato) {
    return empty($contrato->fec_delete);
});

$contratosEliminados = array_filter($contratosRegistros, function ($contrato) {
    return !empty($contrato->fec_delete);
});
?>

<?php include_once 'encab_contratos_empleados.php'; ?>
<?php require_once __DIR__ . '/../../app/alert_helper.php'; ?>
<?php render_alert_banner(); ?>


<main class="main-container">
    <div class="row">
        <div class="col-12">
            <h1>Contratos Registrados</h1>

            <div class="d-flex gap-3 mb-4">
                <span class="badge bg-primary p-2">Activos: <?php echo count($contratosActivos); ?></span>
                <span class="badge bg-danger p-2" id="btnEliminados" data-deleted-modal-target="#modalEliminados" style="cursor:pointer;">Eliminados: <?php echo count($contratosEliminados); ?></span>
            </div>

            <div class="modal" id="modalEliminados" data-deleted-modal aria-labelledby="modalEliminadosLabel" aria-hidden="true">
                <div class="modal-dialog modal-xl">
                    <div class="modal-content">
                        <div class="modal-header bg-danger text-white">
                            <h5 class="modal-title" id="modalEliminadosLabel">Contratos Eliminados</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                        </div>
                        <div class="modal-body">
                            <?php if (count($contratosEliminados) === 0): ?>
                                <div class="alert alert-info">No hay contratos eliminados.</div>
                            <?php else: ?>
                                <div class="table-responsive contratos-table-scroll">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Empleado</th>
                                                <th>Área</th>
                                                <th>Puesto</th>
                                                <th>Fecha Inicio</th>
                                                <th>Fecha Fin</th>
                                                <th>Tipo Contrato</th>
                                                <th>Dirección Oficina</th>
                                                <th class="acciones-fijas">Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($contratosEliminados as $contrato): ?>
                                                <tr>
                                                    <td><?php echo mostrarValor($contrato->nombre_completo ?? null); ?></td>
                                                    <td><?php echo mostrarValor($contrato->nombre_area ?? null); ?></td>
                                                    <td><?php echo mostrarValor($contrato->puesto ?? null); ?></td>
                                                    <td><?php echo mostrarValor($contrato->fecha_inicio_puesto ?? null); ?></td>
                                                    <td><?php echo mostrarValor($contrato->fecha_fin_puesto ?? null); ?></td>
                                                    <td><?php echo mostrarValor($contrato->tipo_contrato ?? null); ?></td>
                                                    <td><?php echo mostrarValor($contrato->direccion_oficina ?? null); ?></td>
                                                    <td class="acciones-fijas">
                                                        <?php if (has_module_permission($moduleId, 'restaurar')): ?>
                                                        <form method="POST" action="restore_contratos_empleados.php" style="display:inline-block;">
                                                            <?php echo csrf_input(); ?>
                                                            <input type="hidden" name="id_contrato" value="<?php echo (int) $contrato->id_contrato; ?>">
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
                <div class="table-responsive contratos-table-scroll">
                    <table class="table table-hover">
                        <thead class="table-primary">
                            <tr>
                                <th>Empleado</th>
                                <th>Área</th>
                                <th>Puesto</th>
                                <th>Fecha Inicio</th>
                                <th>Fecha Fin</th>
                                <th>Tipo Contrato</th>
                                <th>Dirección Oficina</th>
                                <th class="acciones-fijas">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($contratosActivos) === 0): ?>
                                <tr><td colspan="8" class="text-center">No hay registros</td></tr>
                            <?php else: ?>
                                <?php foreach ($contratosActivos as $contrato): ?>
                                    <tr>
                                        <td><?php echo mostrarValor($contrato->nombre_completo ?? null); ?></td>
                                        <td><?php echo mostrarValor($contrato->nombre_area ?? null); ?></td>
                                        <td><?php echo mostrarValor($contrato->puesto ?? null); ?></td>
                                        <td><?php echo mostrarValor($contrato->fecha_inicio_puesto ?? null); ?></td>
                                        <td><?php echo mostrarValor($contrato->fecha_fin_puesto ?? null); ?></td>
                                        <td><?php echo mostrarValor($contrato->tipo_contrato ?? null); ?></td>
                                        <td><?php echo mostrarValor($contrato->direccion_oficina ?? null); ?></td>
                                        <td class="actions-cell acciones-fijas">
                                            <?php if (has_module_permission(13, 'actualizar')): ?>
                                            <a class="btn btn-warning btn-sm" href="editar_contratos_empleados.php?id_contrato=<?php echo (int) $contrato->id_contrato; ?>">Editar</a>
                                            <?php endif; ?>
                                            <?php if (has_module_permission(13, 'eliminar')): ?>
                                            <form method="POST" action="eliminar_contratos_empleados.php" style="display:inline-block;">
                                                <?php echo csrf_input(); ?>
                                                <input type="hidden" name="id_contrato" value="<?php echo (int) $contrato->id_contrato; ?>">
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
                    <?php foreach ($contratosActivos as $contrato): ?>
                        <div class="col-12 mb-3">
                            <div class="mantenimiento-card card">
                                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0"><?php echo htmlspecialchars((string) $contrato->nombre_completo, ENT_QUOTES, 'UTF-8'); ?></h5>
                                    <div class="d-flex gap-2">
                                        <?php if (has_module_permission(13, 'actualizar')): ?>
                                            <a class="btn btn-warning btn-sm" href="editar_contratos_empleados.php?id_contrato=<?php echo (int) $contrato->id_contrato; ?>">Editar</a>
                                            <?php endif; ?>
                                        <?php if (has_module_permission(13, 'eliminar')): ?>
                                            <form method="POST" action="eliminar_contratos_empleados.php" style="display:inline-block;">
                                                <?php echo csrf_input(); ?>
                                                <input type="hidden" name="id_contrato" value="<?php echo (int) $contrato->id_contrato; ?>">
                                                <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                                            </form>
                                            <?php endif; ?>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <p class="card-text mb-1"><strong>Área:</strong> <?php echo mostrarValor($contrato->nombre_area ?? null); ?></p>
                                    <p class="card-text mb-1"><strong>Puesto:</strong> <?php echo mostrarValor($contrato->puesto ?? null); ?></p>
                                    <p class="card-text mb-1"><strong>Inicio:</strong> <?php echo mostrarValor($contrato->fecha_inicio_puesto ?? null); ?></p>
                                    <p class="card-text mb-1"><strong>Fin:</strong> <?php echo mostrarValor($contrato->fecha_fin_puesto ?? null); ?></p>
                                    <p class="card-text mb-1"><strong>Tipo Contrato:</strong> <?php echo mostrarValor($contrato->tipo_contrato ?? null); ?></p>
                                    <p class="card-text mb-0"><strong>Dirección:</strong> <?php echo mostrarValor($contrato->direccion_oficina ?? null); ?></p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include_once 'pie_contratos_empleados.php'; ?>