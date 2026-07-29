<?php

declare(strict_types=1);

$moduleId = 26;
require_once __DIR__ . '/../../app/src_guard.php';
require_once __DIR__ . '/../../app/conexion.php';

date_default_timezone_set('America/Bogota');

$sentencia = $conexion->query('SELECT * FROM fun_listar_sst_comite_miembros(NULL);');
$miembros = $sentencia->fetchAll(PDO::FETCH_OBJ);

$activos = array_filter($miembros, fn($m) => empty($m->fec_delete));
$eliminados = array_filter($miembros, fn($m) => !empty($m->fec_delete));

$csrfToken = htmlspecialchars(csrf_get_token(), ENT_QUOTES, 'UTF-8');

include_once 'encab_sst.php';
?>

<main class="main-container">
    <div class="comunicados-admin-header">
        <div class="comunicados-admin-title">
            <h1>Directorio de Comités SST</h1>
            <p class="comunicados-admin-subtitle">Gestiona los miembros del COPASST y del Comité de Convivencia Laboral.</p>
        </div>
        <?php if (has_module_permission(26, 'crear')): ?>
        <a href="forma_comite.php" class="btn btn-primary">
            <i class="fas fa-plus"></i> Agregar miembro
        </a>
        <?php endif; ?>
    </div>

    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($_GET['error'], ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>
    <?php if (isset($_GET['restaurado'])): ?>
        <div class="alert alert-success">Miembro restaurado correctamente.</div>
    <?php endif; ?>

    <section class="data-card">
        <h2 class="card-title">Miembros activos</h2>
        <?php if (empty($activos)): ?>
            <div class="sst-empty">No hay miembros activos.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Cargo</th>
                            <th>Comité</th>
                            <th>Correo</th>
                            <th>Teléfono</th>
                            <th>Orden</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($activos as $m): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($m->nombre_completo); ?></td>
                                <td><?php echo htmlspecialchars($m->cargo); ?></td>
                                <td><?php echo htmlspecialchars($m->tipo_comite); ?></td>
                                <td><?php echo htmlspecialchars($m->correo ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($m->telefono ?? 'N/A'); ?></td>
                                <td><?php echo (int) $m->orden_visualizacion; ?></td>
                                <td>
                                    <?php if (has_module_permission(26, 'actualizar')): ?>
                                        <a href="editar_comite.php?id_miembro=<?php echo (int) $m->id_miembro; ?>" class="btn btn-sm btn-warning">Editar</a>
                                    <?php endif; ?>
                                    <?php if (has_module_permission(26, 'eliminar')): ?>
                                        <form action="eliminar_comite.php" method="POST" style="display:inline;" onsubmit="return confirm('¿Eliminar este miembro?');">
                                            <?php echo csrf_input(); ?>
                                            <input type="hidden" name="id_miembro" value="<?php echo (int) $m->id_miembro; ?>">
                                            <button type="submit" class="btn btn-sm btn-danger">Eliminar</button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>

    <?php if (!empty($eliminados) && has_module_permission(26, 'restaurar')): ?>
    <section class="data-card" style="margin-top: 2rem;">
        <h2 class="card-title">Miembros eliminados</h2>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Cargo</th>
                        <th>Comité</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($eliminados as $m): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($m->nombre_completo); ?></td>
                            <td><?php echo htmlspecialchars($m->cargo); ?></td>
                            <td><?php echo htmlspecialchars($m->tipo_comite); ?></td>
                            <td>
                                <form action="restore_comite.php" method="POST" style="display:inline;">
                                    <?php echo csrf_input(); ?>
                                    <input type="hidden" name="id_miembro" value="<?php echo (int) $m->id_miembro; ?>">
                                    <button type="submit" class="btn btn-sm btn-success">Restaurar</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
    <?php endif; ?>
</main>

<?php include_once 'pie_sst.php'; ?>
