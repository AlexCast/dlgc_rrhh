<?php

declare(strict_types=1);

$moduleId = 26;
require_once __DIR__ . '/../../app/src_guard.php';
require_once __DIR__ . '/../../app/conexion.php';

date_default_timezone_set('America/Bogota');

$filtroEstado = isset($_GET['estado']) ? trim($_GET['estado']) : '';
$estadosValidos = ['PENDIENTE', 'EN PROCESO', 'RESUELTO', 'CANCELADO_ENCARGADO'];
if ($filtroEstado !== '' && !in_array($filtroEstado, $estadosValidos, true)) {
    $filtroEstado = '';
}

$sentencia = $conexion->prepare('SELECT * FROM fun_listar_sst_quejas_encargado(:estado);');
$sentencia->execute([':estado' => $filtroEstado]);
$quejas = $sentencia->fetchAll(PDO::FETCH_OBJ);

$csrfToken = htmlspecialchars(csrf_get_token(), ENT_QUOTES, 'UTF-8');

include_once 'encab_sst.php';
require_once __DIR__ . '/../../app/alert_helper.php';
render_alert_banner();
?>

<main class="main-container">
    <div class="comunicados-admin-header">
        <div class="comunicados-admin-title">
            <h1>Buzón de Quejas y Sugerencias</h1>
            <p class="comunicados-admin-subtitle">Gestiona las solicitudes recibidas. Recuerda: no puedes modificar el contenido de una queja.</p>
        </div>
    </div>

    <section class="data-card">
        <form method="GET" class="row g-3 mb-3">
            <div class="col-md-4">
                <label for="estado" class="form-label">Filtrar por estado</label>
                <select name="estado" id="estado" class="form-select" onchange="this.form.submit()">
                    <option value="">Todos</option>
                    <?php foreach ($estadosValidos as $estado): ?>
                        <option value="<?php echo htmlspecialchars($estado, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $filtroEstado === $estado ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($estado); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </form>

        <?php if (empty($quejas)): ?>
            <div class="sst-empty">No hay solicitudes para mostrar.</div>
        <?php else: ?>
            <div class="sst-quejas-list">
                <?php foreach ($quejas as $q): ?>
                    <article class="sst-queja-card">
                        <div class="sst-queja-header">
                            <div>
                                <span class="sst-queja-type"><?php echo htmlspecialchars($q->tipo_peticion); ?></span>
                                <span class="sst-queja-status status-<?php echo strtolower(str_replace([' ', '_'], '-', $q->estado)); ?>"><?php echo htmlspecialchars($q->estado); ?></span>
                            </div>
                            <span class="sst-queja-date"><?php echo date('d/m/Y H:i', strtotime($q->fec_insert)); ?></span>
                        </div>
                        <h3 class="sst-queja-subject"><?php echo htmlspecialchars($q->asunto); ?></h3>
                        <p class="sst-queja-desc"><?php echo nl2br(htmlspecialchars($q->descripcion)); ?></p>
                        <p class="sst-queja-meta">
                            <strong>De:</strong> <?php echo htmlspecialchars($q->nombre_usuario); ?> (<?php echo htmlspecialchars($q->id_usuario); ?>)
                            <?php if (!empty($q->correo_usuario)): ?> — <?php echo htmlspecialchars($q->correo_usuario); ?><?php endif; ?>
                        </p>

                        <?php if (!empty($q->respuesta)): ?>
                            <div class="sst-queja-response">
                                <strong>Respuesta:</strong>
                                <p><?php echo nl2br(htmlspecialchars($q->respuesta)); ?></p>
                            </div>
                        <?php endif; ?>

                        <?php if (!in_array($q->estado, ['RESUELTO', 'CANCELADO_ENCARGADO'], true)): ?>
                            <form action="cambiar_estado_queja.php" method="POST" class="sst-admin-form">
                                <?php echo csrf_input(); ?>
                                <input type="hidden" name="id_queja" value="<?php echo (int) $q->id_queja; ?>">
                                <div class="sst-admin-row">
                                    <div class="sst-admin-field">
                                        <label class="form-label">Nuevo estado</label>
                                        <select name="estado" class="form-select" required>
                                            <option value="EN PROCESO" <?php echo $q->estado === 'EN PROCESO' ? 'selected' : ''; ?>>EN PROCESO</option>
                                            <option value="RESUELTO">RESUELTO</option>
                                            <option value="CANCELADO_ENCARGADO">CANCELADO</option>
                                        </select>
                                    </div>
                                    <div class="sst-admin-field sst-admin-field-large">
                                        <label class="form-label">Respuesta / Observación</label>
                                        <textarea name="respuesta" class="form-control" rows="1" placeholder="Opcional"><?php echo htmlspecialchars($q->respuesta ?? ''); ?></textarea>
                                    </div>
                                    <div class="sst-admin-field sst-admin-field-actions">
                                        <button type="submit" class="btn btn-primary">Actualizar</button>
                                    </div>
                                </div>
                            </form>
                        <?php endif; ?>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</main>

<?php include_once 'pie_sst.php'; ?>
