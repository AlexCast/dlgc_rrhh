<?php

$moduleId = 28;
$requiredAction = 'crear';
require_once __DIR__ . '/../../app/src_guard.php';
require_once __DIR__ . '/../../app/conexion.php';
?>

<?php include_once 'encab_dias_festivos.php'; ?>
<?php require_once __DIR__ . '/../../app/alert_helper.php'; ?>
<?php render_alert_banner(); ?>


<main class="main-container">
    <section class="editor-shell">
        <div class="editor-card">
            <header class="editor-header">
                <h1 class="editor-title">Registrar Festivo de Empresa</h1>
                <p class="editor-subtitle">Los festivos nacionales de Colombia se calculan automáticamente y no se editan aquí. Usa este formulario solo para días adicionales declarados por la empresa (ej. aniversario, puente administrativo).</p>
            </header>

            <div class="editor-body">
                <form action="insertar_dias_festivos.php" method="POST" novalidate data-validate>
                    <?php echo csrf_input(); ?>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="field-wrap">
                                <label for="fecha_display" class="form-label">Fecha</label>
                                <div class="date-picker" data-target="fecha">
                                    <input type="text" id="fecha_display" class="date-picker-input form-control" placeholder="dd/mm/aaaa" required autocomplete="off">
                                    <svg class="date-picker-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                                    <input type="hidden" id="fecha" name="fecha">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="field-wrap">
                                <label for="descripcion" class="form-label">Descripción</label>
                                <input type="text" name="descripcion" id="descripcion" class="form-control" required minlength="3" maxlength="100" placeholder="Ej: Aniversario de la empresa">
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="field-wrap form-check">
                                <input type="checkbox" name="descuenta_salario" id="descuenta_salario" class="form-check-input" value="1" checked>
                                <label for="descuenta_salario" class="form-check-label">Este día se descuenta del sueldo (a diferencia de los festivos nacionales, que siempre son remunerados)</label>
                            </div>
                        </div>
                    </div>

                    <div class="cta-row">
                        <a href="listar_dias_festivos.php" class="btn btn-soft">Cancelar y volver</a>
                        <button type="submit" class="btn btn-save">Registrar Festivo</button>
                    </div>
                </form>
            </div>
        </div>
    </section>
</main>

<?php include_once 'pie_dias_festivos.php'; ?>
