<?php
$exportarModulo = $exportarModulo ?? '';
$exportarTitulos = [
    'empleados' => 'Exportar empleados',
    'contratos' => 'Exportar contratos',
    'afiliaciones' => 'Exportar afiliaciones',
    'nomina' => 'Exportar nómina',
    'festivos' => 'Exportar días festivos',
    'comunicados' => 'Exportar comunicados',
    'quejas' => 'Exportar quejas y sugerencias',
    'comite' => 'Exportar comité SST',
    'usuarios' => 'Exportar usuarios',
    'permisos-usuarios' => 'Exportar permisos por usuario',
    'roles-operaciones' => 'Exportar roles y operaciones',
    'area' => 'Exportar áreas',
    'arl' => 'Exportar ARL',
    'banco' => 'Exportar bancos',
    'cajacompensacion' => 'Exportar cajas de compensación',
    'cesantias' => 'Exportar cesantías',
    'eps' => 'Exportar EPS',
    'modulos' => 'Exportar módulos',
    'operaciones' => 'Exportar operaciones',
    'pension' => 'Exportar pensiones',
    'roles' => 'Exportar roles',
];
$exportarSinFechas = ['afiliaciones', 'comite', 'area', 'arl', 'banco', 'cajacompensacion', 'cesantias', 'eps', 'modulos', 'operaciones', 'pension', 'roles'];
if (!isset($exportarTitulos[$exportarModulo])) {
    return;
}
$exportarPermitido = has_module_permission($moduleId ?? 0, 'actualizar');
if (!$exportarPermitido) {
    return;
}
?>
<link rel="stylesheet" href="/assets/css/src_export.css?v=<?php echo filemtime(__DIR__ . '/../../assets/css/src_export.css'); ?>">
<button type="button" class="src-export-trigger" data-src-export-open aria-label="<?php echo htmlspecialchars($exportarTitulos[$exportarModulo], ENT_QUOTES, 'UTF-8'); ?>" title="<?php echo htmlspecialchars($exportarTitulos[$exportarModulo], ENT_QUOTES, 'UTF-8'); ?>">
    <i class="fas fa-download" aria-hidden="true"></i>
</button>
<div class="src-export-modal" data-src-export-modal hidden>
    <div class="src-export-dialog" role="dialog" aria-modal="true" aria-labelledby="src-export-title">
        <div class="src-export-header">
            <h2 id="src-export-title"><?php echo htmlspecialchars($exportarTitulos[$exportarModulo], ENT_QUOTES, 'UTF-8'); ?></h2>
            <button type="button" class="src-export-close" data-src-export-close aria-label="Cerrar">&times;</button>
        </div>
        <form class="src-export-form" data-src-export-form data-modulo="<?php echo htmlspecialchars($exportarModulo, ENT_QUOTES, 'UTF-8'); ?>">
            <label>Alcance
                <select name="alcance">
                    <option value="activos">Solo activos</option>
                    <option value="todos">Todos los registros</option>
                </select>
            </label>
            <?php if ($exportarModulo === 'festivos'): ?>
                <p class="src-export-hint">El rango se aplica a la fecha del festivo.</p>
            <?php elseif ($exportarModulo === 'comunicados'): ?>
                <p class="src-export-hint">El rango se aplica a la fecha de publicación.</p>
            <?php elseif ($exportarModulo === 'contratos'): ?>
                <p class="src-export-hint">El rango se aplica al inicio del contrato.</p>
            <?php endif; ?>
            <?php if (in_array($exportarModulo, ['contratos', 'festivos', 'comite'], true)): ?>
                <label><?php echo $exportarModulo === 'comite' ? 'Comité' : 'Tipo'; ?>
                    <select name="tipo">
                        <option value="">Todos</option>
                        <?php if ($exportarModulo === 'contratos'): ?>
                            <option value="FIJO">Fijo</option><option value="INDEFINIDO">Indefinido</option><option value="PRACTICAS">Prácticas</option>
                        <?php elseif ($exportarModulo === 'festivos'): ?>
                            <option value="NACIONAL">Nacional</option><option value="EMPRESA">Empresa</option>
                        <?php else: ?>
                            <option value="COPASST">COPASST</option><option value="COMITE_CONVIVENCIA">Comité de convivencia</option>
                        <?php endif; ?>
                    </select>
                </label>
            <?php endif; ?>
            <?php if ($exportarModulo === 'festivos'): ?>
                <label>¿Descuenta salario?
                    <select name="estado"><option value="">Todos</option><option value="SI">Sí</option><option value="NO">No</option></select>
                </label>
            <?php elseif ($exportarModulo === 'comunicados'): ?>
                <label>Categoría
                    <select name="categoria"><option value="">Todas</option><option>GENERAL</option><option>URGENTE</option><option>EVENTO</option><option>INFORMACION</option><option>INSTITUCIONAL</option></select>
                </label>
            <?php elseif ($exportarModulo === 'quejas'): ?>
                <label>Estado
                    <select name="estado"><option value="">Todos</option><option>PENDIENTE</option><option>EN PROCESO</option><option>RESUELTO</option><option>CANCELADO_ENCARGADO</option></select>
                </label>
                <label>Tipo
                    <select name="tipo"><option value="">Todos</option><option>QUEJA</option><option>SUGERENCIA</option><option>RECLAMO</option><option>DENUNCIA</option></select>
                </label>
            <?php elseif ($exportarModulo === 'nomina'): ?>
                <div class="src-export-dates">
                    <label>Salario mínimo <input type="number" min="0" step="0.01" name="minimo"></label>
                    <label>Salario máximo <input type="number" min="0" step="0.01" name="maximo"></label>
                </div>
            <?php endif; ?>
            <?php if (!in_array($exportarModulo, $exportarSinFechas, true)): ?>
            <div class="src-export-dates">
                <label>Desde <input type="date" name="desde"></label>
                <label>Hasta <input type="date" name="hasta"></label>
            </div>
            <?php endif; ?>
            <div class="src-export-actions">
                <button type="submit" name="formato" value="excel" class="src-export-option"><i class="fas fa-file-excel" aria-hidden="true"></i> Excel</button>
                <button type="submit" name="formato" value="pdf" class="src-export-option"><i class="fas fa-file-pdf" aria-hidden="true"></i> PDF</button>
            </div>
        </form>
    </div>
</div>
<script src="/assets/js/src_export.js?v=<?php echo filemtime(__DIR__ . '/../../assets/js/src_export.js'); ?>" defer></script>
