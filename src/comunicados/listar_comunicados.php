<?php

declare(strict_types=1);

$moduleId = 24;
require_once __DIR__ . '/../../app/src_guard.php';
require_once __DIR__ . '/../../app/conexion.php';
require_once __DIR__ . '/../../app/helpers/HtmlSanitizer.php';

// La base de datos almacena hora local de Colombia; forzamos esa zona en PHP
// para que los cálculos relativos ("hace X horas") sean correctos.
date_default_timezone_set('America/Bogota');

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

function tiempoRelativo(?string $fecha): string
{
    if (empty($fecha)) {
        return 'Fecha desconocida';
    }

    $dt = date_create($fecha);
    if ($dt === false) {
        return htmlspecialchars($fecha, ENT_QUOTES, 'UTF-8');
    }

    $now = new DateTime();
    $diff = $now->diff($dt);

    if ($diff->y > 0) {
        return 'Hace ' . $diff->y . ' año' . ($diff->y > 1 ? 's' : '');
    }
    if ($diff->m > 0) {
        return 'Hace ' . $diff->m . ' mes' . ($diff->m > 1 ? 'es' : '');
    }
    if ($diff->d > 0) {
        return 'Hace ' . $diff->d . ' día' . ($diff->d > 1 ? 's' : '');
    }
    if ($diff->h > 0) {
        return 'Hace ' . $diff->h . ' hora' . ($diff->h > 1 ? 's' : '');
    }
    if ($diff->i > 0) {
        return 'Hace ' . $diff->i . ' minuto' . ($diff->i > 1 ? 's' : '');
    }
    return 'Hace un momento';
}

function truncarTexto(?string $texto, int $limite = 140): string
{
    $textoPlano = strip_tags(trim((string) $texto));
    if (mb_strlen($textoPlano) <= $limite) {
        return $textoPlano;
    }
    return mb_substr($textoPlano, 0, $limite) . '...';
}

function limpiarContenidoHtml(?string $html): string
{
    return HtmlSanitizer::clean($html);
}

function formatearFechaHora(?string $fecha): string
{
    if (empty($fecha)) {
        return 'N/A';
    }

    $dt = date_create($fecha);
    if ($dt === false) {
        return htmlspecialchars($fecha, ENT_QUOTES, 'UTF-8');
    }

    return $dt->format('d/m/Y H:i');
}

$categorias = [
    'GENERAL' => 'tag-general',
    'URGENTE' => 'tag-urgent',
    'EVENTO' => 'tag-event',
    'INFORMACION' => 'tag-info',
    'INSTITUCIONAL' => 'tag-institutional',
];

$csrfToken = htmlspecialchars(csrf_get_token(), ENT_QUOTES, 'UTF-8');

include_once 'encab_comunicados.php';
?>
<?php require_once __DIR__ . '/../../app/alert_helper.php'; ?>
<?php render_alert_banner(); ?>

<main class="main-container">
    <div class="comunicados-admin-header">
        <div class="comunicados-admin-title">
            <h1>Gestión de Comunicados</h1>
            <p class="text-muted">Administra los comunicados oficiales y consulta quién los ha leído.</p>
        </div>

        <div class="comunicados-admin-stats">
            <span class="admin-stat stat-active">
                <i class="fas fa-check-circle" aria-hidden="true"></i>
                Activos: <?php echo count($activos); ?>
            </span>
            <span class="admin-stat stat-deleted" id="btnEliminados" data-deleted-modal-target="#modalEliminados" role="button" tabindex="0">
                <i class="fas fa-trash-alt" aria-hidden="true"></i>
                Eliminados: <?php echo count($eliminados); ?>
            </span>
        </div>
    </div>

    <!-- Modal de comunicados eliminados -->
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
                                            <td><?php echo formatearFechaHora($c->fec_delete); ?></td>
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

    <!-- Modal: Visto por -->
    <div class="modal" id="modalVistoPor" aria-labelledby="modalVistoPorLabel" aria-hidden="true">
        <div class="modal-dialog modal-visto-por">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <div>
                        <h5 class="modal-title" id="modalVistoPorLabel">Visto por</h5>
                        <span id="vistoPorComunicadoInfo" class="modal-subtitle"></span>
                    </div>
                    <button type="button" class="btn-close" data-modal-close aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <div id="vistoPorLoader" class="visto-por-loader">
                        <i class="fas fa-circle-notch fa-spin" aria-hidden="true"></i> Cargando lectores...
                    </div>
                    <div id="vistoPorError" class="alert alert-info" style="display:none;"></div>
                    <ul id="vistoPorLista" class="lectores-list"></ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Grid de comunicados -->
    <div class="comunicados-admin-grid" data-csrf-token="<?php echo $csrfToken; ?>">
        <?php if (count($activos) === 0): ?>
            <div class="comunicado-card comunicado-empty">
                <p>No hay comunicados registrados.</p>
            </div>
        <?php else: ?>
            <?php foreach ($activos as $c): ?>
                <?php
                $tagClass = $categorias[$c->categoria] ?? 'tag-general';
                $contenidoCompleto = limpiarContenidoHtml((string) $c->contenido);
                $contenidoCorto = truncarTexto($contenidoCompleto, 140);
                $requiereExpansion = mb_strlen(strip_tags($contenidoCompleto)) > 140;
                $totalVistos = (int) $c->total_vistos;
                ?>
                <article class="comunicado-card" data-id-comunicado="<?php echo (int) $c->id_comunicado; ?>">
                    <div class="comunicado-card-inner">
                        <div class="comunicado-meta">
                            <span class="comunicado-tag <?php echo $tagClass; ?>">
                                <?php echo mostrarValor($c->categoria); ?>
                            </span>
                            <span class="comunicado-date">
                                <?php echo tiempoRelativo($c->fec_insert); ?>
                            </span>
                        </div>

                        <h3 class="comunicado-title"><?php echo mostrarValor($c->titulo); ?></h3>

                        <div class="comunicado-content-wrapper">
                            <div class="comunicado-content <?php echo $requiereExpansion ? 'comunicado-truncado' : ''; ?>" data-full-text="<?php echo htmlspecialchars($contenidoCompleto, ENT_QUOTES, 'UTF-8'); ?>">
                                <?php echo nl2br(htmlspecialchars($contenidoCorto, ENT_QUOTES, 'UTF-8'), false); ?>
                            </div>
                            <?php if ($requiereExpansion): ?>
                                <button type="button" class="btn-ver-mas" data-accion="expandir">Ver más</button>
                            <?php endif; ?>
                        </div>

                        <div class="comunicado-author admin-author">
                            <div class="author-avatar">
                                <i class="fas fa-user" aria-hidden="true"></i>
                            </div>
                            <div class="author-info">
                                <span class="author-name"><?php echo mostrarValor($c->usr_insert); ?></span>
                                <span class="comunicado-date"><?php echo formatearFechaHora($c->fec_insert); ?></span>
                            </div>
                        </div>

                        <div class="comunicado-footer admin-footer">
                            <button
                                type="button"
                                class="vistos-count btn-visto-por"
                                data-id-comunicado="<?php echo (int) $c->id_comunicado; ?>"
                                data-titulo-comunicado="<?php echo htmlspecialchars((string) $c->titulo, ENT_QUOTES, 'UTF-8'); ?>"
                            >
                                <i class="fas fa-eye" aria-hidden="true"></i>
                                <span class="vistos-text"><?php echo $totalVistos; ?> visto<?php echo $totalVistos !== 1 ? 's' : ''; ?></span>
                            </button>

                            <div class="admin-actions">
                                <?php if (has_module_permission($moduleId, 'actualizar')): ?>
                                    <a class="btn btn-sm btn-warning" href="editar_comunicados.php?id_comunicado=<?php echo urlencode((string) $c->id_comunicado); ?>">
                                        <i class="fas fa-edit" aria-hidden="true"></i> Editar
                                    </a>
                                <?php endif; ?>
                                <?php if (has_module_permission($moduleId, 'eliminar')): ?>
                                    <form method="POST" action="eliminar_comunicados.php" class="form-inline">
                                        <?php echo csrf_input(); ?>
                                        <input type="hidden" name="id_comunicado" value="<?php echo htmlspecialchars((string) $c->id_comunicado, ENT_QUOTES, 'UTF-8'); ?>">
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar este comunicado?');">
                                            <i class="fas fa-trash-alt" aria-hidden="true"></i> Eliminar
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</main>

<?php include_once 'pie_comunicados.php'; ?>
