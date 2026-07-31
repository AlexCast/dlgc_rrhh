<?php

$moduleId = 13;
require_once __DIR__ . '/../../app/src_guard.php';
require_once __DIR__ . '/../../app/conexion.php';

// Consultar empleados
$sentencia = $conexion->query("
    SELECT
        e.id_usuario,
        CONCAT_WS(' ', u.primer_nombre, NULLIF(u.segundo_nombre, ''), u.primer_apellido, NULLIF(u.segundo_apellido, '')) AS nombre_completo
    FROM t_empleados e
    INNER JOIN t_usuarios u ON u.id_usuario = e.id_usuario
    WHERE e.fec_delete IS NULL
      AND u.fec_delete IS NULL
    ORDER BY nombre_completo, e.id_usuario
");
$empleadosRegistros = $sentencia->fetchAll(PDO::FETCH_OBJ);

// Consultar áreas
$sentencia = $conexion->query("SELECT id_area, nombre_area FROM t_areas WHERE fec_delete IS NULL ORDER BY nombre_area");
$areasRegistros = $sentencia->fetchAll(PDO::FETCH_OBJ);
?>

<?php include_once 'encab_contratos_empleados.php'; ?>
<?php require_once __DIR__ . '/../../app/alert_helper.php'; ?>
<?php render_alert_banner(); ?>


<main class="main-container">
    <section class="editor-shell">
        <div class="editor-card">
            <header class="editor-header">
                <h1 class="editor-title">Registrar Contrato</h1>
                <p class="editor-subtitle">Ingresa la información para asignar un nuevo puesto/contrato al empleado.</p>
            </header>

            <div class="editor-body">
                <form action="insertar_contratos_empleados.php" method="POST" novalidate data-validate>
                    <?php echo csrf_input(); ?>

                    <div class="row g-3">
                        
                        <div class="col-md-6">
                            <div class="field-wrap">
                                <label for="id_usuario" class="form-label">ID Usuario (Empleado)</label>
                                <div class="input-group">
                                    <span class="input-group-text">Empleado</span>
                                    <select name="id_usuario" id="id_usuario" class="form-select" required>
                                        <option value="" disabled selected>Seleccione empleado</option>
                                        <?php foreach ($empleadosRegistros as $empleado): ?>
                                            <option value="<?php echo htmlspecialchars((string) $empleado->id_usuario, ENT_QUOTES, 'UTF-8'); ?>">
                                                <?php echo htmlspecialchars((string) $empleado->nombre_completo, ENT_QUOTES, 'UTF-8'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <p class="field-tip">Selecciona el empleado para asignarle el contrato.</p>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="field-wrap">
                                <label for="id_area" class="form-label">Área</label>
                                <div class="input-group">
                                    <span class="input-group-text">Área</span>
                                    <select name="id_area" id="id_area" class="form-select" required>
                                        <option value="" disabled selected>Seleccione área</option>
                                        <?php foreach ($areasRegistros as $area): ?>
                                            <option value="<?php echo (int) $area->id_area; ?>">
                                                <?php echo htmlspecialchars((string) $area->nombre_area, ENT_QUOTES, 'UTF-8'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <p class="field-tip">Área a la que pertenecerá el empleado.</p>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="field-wrap">
                                <label for="puesto" class="form-label">Puesto</label>
                                <div class="input-group">
                                    <span class="input-group-text">Puesto</span>
                                    <input
                                        type="text"
                                        name="puesto"
                                        id="puesto"
                                        class="form-control"
                                        required
                                        maxlength="50"
                                        placeholder="Ej: Desarrollador Junior"
                                    >
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="field-wrap">
                                <label for="tipo_contrato" class="form-label">Tipo de Contrato</label>
                                <div class="input-group">
                                    <span class="input-group-text">Tipo</span>
                                    <select name="tipo_contrato" id="tipo_contrato" class="form-select" required>
                                        <option value="" disabled selected>Seleccione tipo de contrato</option>
                                        <option value="FIJO">FIJO</option>
                                        <option value="INDEFINIDO">INDEFINIDO</option>
                                        <option value="PRACTICAS">PRACTICAS</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="field-wrap">
                                <label for="fecha_inicio_puesto" class="form-label">Fecha Inicio Puesto</label>
                                <div class="input-group">
                                    <span class="input-group-text">Inicio</span>
                                    <input
                                        type="date"
                                        name="fecha_inicio_puesto"
                                        id="fecha_inicio_puesto"
                                        class="form-control"
                                        required
                                    >
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="field-wrap">
                                <label for="fecha_fin_puesto" class="form-label">Fecha Fin Puesto</label>
                                <div class="input-group">
                                    <span class="input-group-text">Fin</span>
                                    <input
                                        type="date"
                                        name="fecha_fin_puesto"
                                        id="fecha_fin_puesto"
                                        class="form-control"
                                    >
                                </div>
                                <p class="field-tip">Dejar en blanco si este es su puesto actual.</p>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="field-wrap">
                                <label for="direccion_oficina" class="form-label">Dirección Oficina</label>
                                <div class="input-group">
                                    <span class="input-group-text">Dirección</span>
                                    <input
                                        type="text"
                                        name="direccion_oficina"
                                        id="direccion_oficina"
                                        class="form-control"
                                        required
                                        maxlength="100"
                                        placeholder="Ej: Edificio Central, Piso 4"
                                    >
                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="cta-row">
                        <a href="listar_contratos_empleados.php" class="btn btn-soft">Cancelar y volver</a>
                        <button type="submit" class="btn btn-save">Registrar contrato</button>
                    </div>
                </form>
            </div>
        </div>
    </section>
</main>

<?php include_once 'pie_contratos_empleados.php'; ?>