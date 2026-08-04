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
                                <label for="fecha" class="form-label">Fecha</label>
                                <input type="date" name="fecha" id="fecha" class="form-control" required>
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
