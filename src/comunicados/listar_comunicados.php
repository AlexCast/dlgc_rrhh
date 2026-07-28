<?php

declare(strict_types=1);

$moduleId = 24;
require_once __DIR__ . '/../../app/src_guard.php';
require_once __DIR__ . '/../../app/conexion.php';

$sentencia = $conexion->query('
    SELECT
        c.id_comunicado,
        c.titulo,
        c.contenido,
        c.categoria,
        c.usr_insert,
        c.fec_insert,
        c.usr_update,
        c.fec_update,
        c.usr_delete,
        c.fec_delete,
        COALESCE(v.total, 0) AS total_vistos
    FROM t_comunicados c
    LEFT JOIN (
        SELECT id_comunicado, COUNT(*) AS total
        FROM t_comunicados_vistos
        GROUP BY id_comunicado
    ) v ON v.id_comunicado = c.id_comunicado
    ORDER BY c.fec_insert DESC
');
$comunicados = $sentencia->fetchAll(PDO::FETCH_OBJ);

$activos = array_filter($comunicados, function ($c) {
    return empty($c->fec_delete);
});

$eliminados = array_filter($comunicados, function ($c) {
    return !empty($c->fec_delete);
});

function mostrarValor(?string $valor): string
{
    $limpio = trim((string) $valor);
    return $limpio !== '' ? htmlspecialchars($limpio, ENT_QUOTES, 'UTF-8') : 'N/A';
}

$categorias = [
    'GENERAL' => 'badge-general',
    'URGENTE' => 'badge-urgent',
    'EVENTO' => 'badge-event',
    'INFORMACION' => 'badge-info',
    'INSTITUCIONAL' => 'badge-institutional',
];
?>

<?php include_once 'encab_comunicados.php'; ?>

<main class="main-container">
    <div class="row">
        <div class="col-12">
            <h1>Comunicados Registrados</h1>

            <div class="d-flex gap-3 mb-4">
                <span class="badge bg-primary p-2">Activos: <?php echo count($activos); ?></span>
                <span class="badge bg-danger p-2" id="btnEliminados" data-deleted-modal-target="#modalEliminados" style="cursor:pointer;">Eliminados: <?php echo count($eliminados); ?></span>
            </div>

            <div class="modal" id="modalEliminados" data-deleted-modal aria-labelledby="modalEliminadosLabel" aria-hidden="true">
                <div class="modal-dialog modal-xl">
                    <div class="modal-content">
                        <div class="modal-header bg-danger text-white">
                            <h5 class="modal-title" id="modalEliminadosLabel">Comunicados Eliminados</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                        </div>
                        <div class="modal-body">
                            <?php if (count($eliminados) === 0): ?>
                                <div class="alert alert-info">No hay comunicados eliminados.</div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Título</th>
                                                <th>Categoría</th>
                                                <th>Insertado por</th>
                                                <th>Eliminado por</th>
                                                <th>Fecha eliminación</th>
                                                <th class="acciones-fijas">Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($eliminados as $c): ?>
                                                <tr>
                                                    <td><?php echo mostrarValor($c->titulo); ?></td>
                                                    <td><?php echo mostrarValor($c->categoria); ?></td>
                                                    <td><?php echo mostrarValor($c->usr_insert); ?></td>
                                                    <td><?php echo mostrarValor($c->usr_delete); ?></td>
                                                    <td><?php echo mostrarValor($c->fec_delete); ?></td>
                                                    <td class="acciones-fijas">
                                                        <?php if (has_module_permission($moduleId, 'restaurar')): ?>
                                                        <form method="POST" action="restore_comunicados.php" style="display:inline-block;">
                                                            <?php echo csrf_input(); ?>
                                                            <input type="hidden" name="id_comunicado" value="<?php echo htmlspecialchars((string) $c->id_comunicado, ENT_QUOTES, 'UTF-8'); ?>">
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
                                <th>Título</th>
                                <th>Categoría</th>
                                <th>Contenido</th>
                                <th>Vistos</th>
                                <th>Insertado por</th>
                                <th class="acciones-fijas">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($activos) === 0): ?>
                                <tr><td colspan="6" class="text-center">No hay comunicados registrados.</td></tr>
                            <?php else: ?>
                                <?php foreach ($activos as $c): ?>
                                    <tr>
                                        <td><?php echo mostrarValor($c->titulo); ?></td>
                                        <td>
                                            <span class="badge <?php echo $categorias[$c->categoria] ?? 'badge-general'; ?>">
                                                <?php echo mostrarValor($c->categoria); ?>
                                            </span>
                                        </td>
                                        <td class="text-truncate" style="max-width: 300px;"><?php echo mostrarValor($c->contenido); ?></td>
                                        <td><?php echo (int) $c->total_vistos; ?></td>
                                        <td><?php echo mostrarValor($c->usr_insert); ?></td>
                                        <td class="actions-cell">
                                            <?php if (has_module_permission($moduleId, 'actualizar')): ?>
                                            <a class="btn btn-warning btn-sm" href="editar_comunicados.php?id_comunicado=<?php echo urlencode((string) $c->id_comunicado); ?>">Editar</a>
                                            <?php endif; ?>
                                            <?php if (has_module_permission($moduleId, 'eliminar')): ?>
                                            <form method="POST" action="eliminar_comunicados.php" style="display:inline-block;">
                                                <?php echo csrf_input(); ?>
                                                <input type="hidden" name="id_comunicado" value="<?php echo htmlspecialchars((string) $c->id_comunicado, ENT_QUOTES, 'UTF-8'); ?>">
                                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('¿Eliminar este comunicado?');">Eliminar</button>
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
                    <?php foreach ($activos as $c): ?>
                        <div class="col-12 mb-3">
                            <div class="mantenimiento-card card">
                                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                                    <span class="badge <?php echo $categorias[$c->categoria] ?? 'badge-general'; ?>"><?php echo mostrarValor($c->categoria); ?></span>
                                </div>
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo mostrarValor($c->titulo); ?></h5>
                                    <p class="card-text text-truncate"><?php echo mostrarValor($c->contenido); ?></p>
                                    <p class="card-text"><small>Vistos: <?php echo (int) $c->total_vistos; ?></small></p>
                                    <div class="d-flex gap-2 flex-wrap">
                                        <?php if (has_module_permission($moduleId, 'actualizar')): ?>
                                        <a class="btn btn-warning btn-sm" href="editar_comunicados.php?id_comunicado=<?php echo urlencode((string) $c->id_comunicado); ?>">Editar</a>
                                        <?php endif; ?>
                                        <?php if (has_module_permission($moduleId, 'eliminar')): ?>
                                        <form method="POST" action="eliminar_comunicados.php" style="display:inline-block;">
                                            <?php echo csrf_input(); ?>
                                            <input type="hidden" name="id_comunicado" value="<?php echo htmlspecialchars((string) $c->id_comunicado, ENT_QUOTES, 'UTF-8'); ?>">
                                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('¿Eliminar este comunicado?');">Eliminar</button>
                                        </form>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include_once 'pie_comunicados.php'; ?>
