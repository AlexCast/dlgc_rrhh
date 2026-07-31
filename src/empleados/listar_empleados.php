<?php

$moduleId = 4;
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
        e.id_usuario,
        e.id_jefe,
        e.id_municipio,
        e.fecha_ingreso,
        e.fecha_egreso,
        e.genero,
        e.fecha_nacimiento,
        e.tipo_sangre,
        e.estado_civil,
        e.direccion_casa,
        e.numero_celular,
        e.foto_perfil,
        e.fec_delete,
        u.correo,
        u.primer_nombre,
        u.segundo_nombre,
        u.primer_apellido,
        u.segundo_apellido,
        CONCAT_WS(' ', u.primer_nombre, NULLIF(u.segundo_nombre, ''), u.primer_apellido, NULLIF(u.segundo_apellido, '')) AS nombre_completo,
        CONCAT_WS(' ', u.primer_nombre, NULLIF(u.segundo_nombre, '')) AS nombres,
        CONCAT_WS(' ', u.primer_apellido, NULLIF(u.segundo_apellido, '')) AS apellidos,
        m.municipio,
        d.departamento,
        CONCAT_WS(' ', uj.primer_nombre, NULLIF(uj.segundo_nombre, ''), uj.primer_apellido, NULLIF(uj.segundo_apellido, '')) AS nombre_jefe
    FROM t_empleados e
    INNER JOIN t_usuarios u ON e.id_usuario = u.id_usuario
    LEFT JOIN t_municipios m ON e.id_municipio = m.id_municipio
    LEFT JOIN t_departamentos d ON m.departamento_id = d.id_departamento
    LEFT JOIN t_empleados ej ON e.id_jefe = ej.id_usuario
    LEFT JOIN t_usuarios uj ON ej.id_usuario = uj.id_usuario
    ORDER BY nombre_completo, e.id_usuario
");
$empleadosRegistros = $sentencia->fetchAll(PDO::FETCH_OBJ);

$empleadosActivos = array_filter($empleadosRegistros, function ($empleado) {
    return empty($empleado->fec_delete);
});

$empleadosEliminados = array_filter($empleadosRegistros, function ($empleado) {
    return !empty($empleado->fec_delete);
});
?>

<?php include_once 'encab_empleados.php'; ?>
<?php require_once __DIR__ . '/../../app/alert_helper.php'; ?>
<?php render_alert_banner(); ?>


<main class="main-container">
    <div class="row">
        <div class="col-12">
            <h1>Empleados Registrados</h1>

            <div class="d-flex gap-3 mb-4">
                <span class="badge bg-primary p-2">Activos: <?php echo count($empleadosActivos); ?></span>
                <span class="badge bg-danger p-2" id="btnEliminados" data-deleted-modal-target="#modalEliminados" style="cursor:pointer;">Eliminados: <?php echo count($empleadosEliminados); ?></span>
            </div>

            <div class="modal" id="modalEliminados" data-deleted-modal aria-labelledby="modalEliminadosLabel" aria-hidden="true">
                <div class="modal-dialog modal-xl">
                    <div class="modal-content">
                        <div class="modal-header bg-danger text-white">
                            <h5 class="modal-title" id="modalEliminadosLabel">Empleados Eliminados</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                        </div>
                        <div class="modal-body">
                            <?php if (count($empleadosEliminados) === 0): ?>
                                <div class="alert alert-info">No hay empleados eliminados.</div>
                            <?php else: ?>
                                <div class="table-responsive empleados-table-scroll">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>ID Usuario</th>
                                                <th>Correo</th>
                                                <th>Nombres</th>
                                                <th>Apellidos</th>
                                                <th>Jefe Directo</th>
                                                <th>Municipio</th>
                                                <th>Departamento</th>
                                                <th>Fecha Ingreso</th>
                                                <th>Fecha Egreso</th>
                                                <th>Género</th>
                                                <th>Fecha Nacimiento</th>
                                                <th>Tipo Sangre</th>
                                                <th>Estado Civil</th>
                                                <th>Dirección Casa</th>
                                                <th>Celular</th>
                                                <th>Foto Perfil</th>
                                                <th class="acciones-fijas">Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($empleadosEliminados as $empleado): ?>
                                                <tr>
                                                    <td><?php echo mostrarValor((string) $empleado->id_usuario); ?></td>
                                                    <td><?php echo mostrarValor($empleado->correo ?? null); ?></td>
                                                    <td><?php echo mostrarValor($empleado->nombres ?? null); ?></td>
                                                    <td><?php echo mostrarValor($empleado->apellidos ?? null); ?></td>
                                                    <td><?php echo mostrarValor($empleado->nombre_jefe ?? null); ?></td>
                                                    <td><?php echo mostrarValor($empleado->municipio ?? null); ?></td>
                                                    <td><?php echo mostrarValor($empleado->departamento ?? null); ?></td>
                                                    <td><?php echo mostrarValor($empleado->fecha_ingreso ?? null); ?></td>
                                                    <td><?php echo mostrarValor($empleado->fecha_egreso ?? null); ?></td>
                                                    <td><?php echo mostrarValor($empleado->genero ?? null); ?></td>
                                                    <td><?php echo mostrarValor($empleado->fecha_nacimiento ?? null); ?></td>
                                                    <td><?php echo mostrarValor($empleado->tipo_sangre ?? null); ?></td>
                                                    <td><?php echo mostrarValor($empleado->estado_civil ?? null); ?></td>
                                                    <td><?php echo mostrarValor($empleado->direccion_casa ?? null); ?></td>
                                                    <td><?php echo mostrarValor($empleado->numero_celular ?? null); ?></td>
                                                    <td><?php echo mostrarValor($empleado->foto_perfil ?? null); ?></td>
                                                    <td class="acciones-fijas">
                                                        <?php if (has_module_permission($moduleId, 'restaurar')): ?>
                                                        <form method="POST" action="restore_empleados.php" style="display:inline-block;">
                                                            <?php echo csrf_input(); ?>
                                                            <input type="hidden" name="id_usuario" value="<?php echo htmlspecialchars((string) $empleado->id_usuario, ENT_QUOTES, 'UTF-8'); ?>">
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
                <div class="table-responsive empleados-table-scroll">
                    <table class="table table-hover">
                        <thead class="table-primary">
                            <tr>
                                <th>ID Usuario</th>
                                <th>Correo</th>
                                <th>Nombres</th>
                                <th>Apellidos</th>
                                <th>Jefe Directo</th>
                                <th>Municipio</th>
                                <th>Departamento</th>
                                <th>Fecha Ingreso</th>
                                <th>Fecha Egreso</th>
                                <th>Género</th>
                                <th>Fecha Nacimiento</th>
                                <th>Tipo Sangre</th>
                                <th>Estado Civil</th>
                                <th>Dirección Casa</th>
                                <th>Celular</th>
                                <th>Foto Perfil</th>
                                <th class="acciones-fijas">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($empleadosActivos) === 0): ?>
                                <tr><td colspan="17" class="text-center">No hay registros</td></tr>
                            <?php else: ?>
                                <?php foreach ($empleadosActivos as $empleado): ?>
                                    <tr>
                                        <td><?php echo mostrarValor((string) $empleado->id_usuario); ?></td>
                                        <td><?php echo mostrarValor($empleado->correo ?? null); ?></td>
                                        <td><?php echo mostrarValor($empleado->nombres ?? null); ?></td>
                                        <td><?php echo mostrarValor($empleado->apellidos ?? null); ?></td>
                                        <td><?php echo mostrarValor($empleado->nombre_jefe ?? null); ?></td>
                                        <td><?php echo mostrarValor($empleado->municipio ?? null); ?></td>
                                        <td><?php echo mostrarValor($empleado->departamento ?? null); ?></td>
                                        <td><?php echo mostrarValor($empleado->fecha_ingreso ?? null); ?></td>
                                        <td><?php echo mostrarValor($empleado->fecha_egreso ?? null); ?></td>
                                        <td><?php echo mostrarValor($empleado->genero ?? null); ?></td>
                                        <td><?php echo mostrarValor($empleado->fecha_nacimiento ?? null); ?></td>
                                        <td><?php echo mostrarValor($empleado->tipo_sangre ?? null); ?></td>
                                        <td><?php echo mostrarValor($empleado->estado_civil ?? null); ?></td>
                                        <td><?php echo mostrarValor($empleado->direccion_casa ?? null); ?></td>
                                        <td><?php echo mostrarValor($empleado->numero_celular ?? null); ?></td>
                                        <td><?php echo mostrarValor($empleado->foto_perfil ?? null); ?></td>
                                        <td class="actions-cell acciones-fijas">
                                            <?php if (has_module_permission(4, 'actualizar')): ?>
                                            <a class="btn btn-warning btn-sm" href="editar_empleados.php?id_usuario=<?php echo urlencode((string) $empleado->id_usuario); ?>">Editar</a>
                                            <?php endif; ?>
                                            <?php if (has_module_permission(4, 'eliminar')): ?>
                                            <form method="POST" action="eliminar_empleados.php" style="display:inline-block;">
                                                <?php echo csrf_input(); ?>
                                                <input type="hidden" name="id_usuario" value="<?php echo htmlspecialchars((string) $empleado->id_usuario, ENT_QUOTES, 'UTF-8'); ?>">
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
                    <?php foreach ($empleadosActivos as $empleado): ?>
                        <div class="col-12 mb-3">
                            <div class="mantenimiento-card card">
                                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0"><?php echo htmlspecialchars((string) $empleado->nombre_completo, ENT_QUOTES, 'UTF-8'); ?></h5>
                                    <div class="d-flex gap-2">
                                        <?php if (has_module_permission(4, 'actualizar')): ?>
                                            <a class="btn btn-warning btn-sm" href="editar_empleados.php?id_usuario=<?php echo urlencode((string) $empleado->id_usuario); ?>">Editar</a>
                                            <?php endif; ?>
                                        <?php if (has_module_permission(4, 'eliminar')): ?>
                                            <form method="POST" action="eliminar_empleados.php" style="display:inline-block;">
                                                <?php echo csrf_input(); ?>
                                                <input type="hidden" name="id_usuario" value="<?php echo htmlspecialchars((string) $empleado->id_usuario, ENT_QUOTES, 'UTF-8'); ?>">
                                                <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                                            </form>
                                            <?php endif; ?>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <p class="card-text mb-1"><strong>ID:</strong> <?php echo htmlspecialchars((string) $empleado->id_usuario, ENT_QUOTES, 'UTF-8'); ?></p>
                                    <p class="card-text mb-1"><strong>Correo:</strong> <?php echo mostrarValor($empleado->correo ?? null); ?></p>
                                    <p class="card-text mb-1"><strong>Nombres:</strong> <?php echo mostrarValor($empleado->nombres ?? null); ?></p>
                                    <p class="card-text mb-1"><strong>Apellidos:</strong> <?php echo mostrarValor($empleado->apellidos ?? null); ?></p>
                                    <p class="card-text mb-1"><strong>Jefe:</strong> <?php echo $empleado->nombre_jefe ? htmlspecialchars((string) $empleado->nombre_jefe, ENT_QUOTES, 'UTF-8') : 'N/A'; ?></p>
                                    <p class="card-text mb-1"><strong>Municipio:</strong> <?php echo mostrarValor($empleado->municipio ?? null); ?></p>
                                    <p class="card-text mb-1"><strong>Departamento:</strong> <?php echo mostrarValor($empleado->departamento ?? null); ?></p>
                                    <p class="card-text mb-1"><strong>Ingreso:</strong> <?php echo htmlspecialchars((string) $empleado->fecha_ingreso, ENT_QUOTES, 'UTF-8'); ?></p>
                                    <p class="card-text mb-1"><strong>Egreso:</strong> <?php echo mostrarValor($empleado->fecha_egreso ?? null); ?></p>
                                    <p class="card-text mb-1"><strong>Género:</strong> <?php echo mostrarValor($empleado->genero ?? null); ?></p>
                                    <p class="card-text mb-1"><strong>Fecha Nacimiento:</strong> <?php echo mostrarValor($empleado->fecha_nacimiento ?? null); ?></p>
                                    <p class="card-text mb-1"><strong>Tipo Sangre:</strong> <?php echo mostrarValor($empleado->tipo_sangre ?? null); ?></p>
                                    <p class="card-text mb-1"><strong>Estado Civil:</strong> <?php echo mostrarValor($empleado->estado_civil ?? null); ?></p>
                                    <p class="card-text mb-1"><strong>Dirección:</strong> <?php echo mostrarValor($empleado->direccion_casa ?? null); ?></p>
                                    <p class="card-text mb-1"><strong>Celular:</strong> <?php echo mostrarValor($empleado->numero_celular ?? null); ?></p>
                                    <p class="card-text mb-0"><strong>Foto Perfil:</strong> <?php echo mostrarValor($empleado->foto_perfil ?? null); ?></p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include_once 'pie_empleados.php'; ?>