<?php

$moduleId = 13;
$requiredAction = 'actualizar';
require_once __DIR__ . '/../../app/src_guard.php';
if (!isset($_GET['id_contrato']) || empty(trim($_GET['id_contrato']))) {
    echo 'No existe el registro a editar';
    exit();
}

$id_contrato = (int) $_GET['id_contrato'];

if ($id_contrato <= 0) {
    echo 'ID inválido';
    exit();
}

require_once __DIR__ . '/../../app/conexion.php';

$sentencia = $conexion->prepare('
    SELECT id_contrato, id_usuario, id_area, puesto, fecha_inicio_puesto, fecha_fin_puesto, tipo_contrato, direccion_oficina
    FROM t_contratos_empleados
    WHERE id_contrato = ?
');
$sentencia->execute([$id_contrato]);
$registro = $sentencia->fetch(PDO::FETCH_OBJ);

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

if (!$registro) {
    echo 'No se encontró el contrato para este registro';
    exit();
}
?>

<?php include_once 'encab_contratos_empleados.php'; ?>

<main class="main-container">
    <section class="editor-shell">
        <div class="editor-card">
            <header class="editor-header">
                <h1 class="editor-title">Editar Contrato</h1>
                <p class="editor-subtitle">Actualiza la información del contrato del empleado.</p>
            </header>

            <div class="editor-body">
                <form action="update_contratos_empleados.php" method="POST" novalidate>
                    <?php echo csrf_input(); ?>

                    <input type="hidden" name="id_contrato" value="<?php echo (int) $registro->id_contrato; ?>">
                    <div class="row g-3">
                        
                        <div class="col-md-6">
                            <div class="field-wrap">
                                <label for="id_usuario" class="form-label">ID Usuario (Empleado)</label>
                                <div class="input-group">
                                    <span class="input-group-text">Empleado</span>
                                    <select name="id_usuario" id="id_usuario" class="form-select" required>
                                        <option value="" disabled>Seleccione empleado</option>
                                        <?php foreach ($empleadosRegistros as $empleado): ?>
                                            <option value="<?php echo htmlspecialchars((string) $empleado->id_usuario, ENT_QUOTES, 'UTF-8'); ?>" <?php echo (string) $empleado->id_usuario === (string) $registro->id_usuario ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars((string) $empleado->nombre_completo, ENT_QUOTES, 'UTF-8'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <p class="field-tip">Selecciona el empleado asociado a este contrato.</p>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="field-wrap">
                                <label for="id_area" class="form-label">Área</label>
                                <div class="input-group">
                                    <span class="input-group-text">Área</span>
                                    <select name="id_area" id="id_area" class="form-select" required>
                                        <option value="" disabled>Seleccione área</option>
                                        <?php foreach ($areasRegistros as $area): ?>
                                            <option value="<?php echo (int) $area->id_area; ?>" <?php echo (int) $area->id_area === (int) $registro->id_area ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars((string) $area->nombre_area, ENT_QUOTES, 'UTF-8'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <p class="field-tip">Área a la que pertenece el empleado en este puesto.</p>
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
                                        value="<?php echo htmlspecialchars((string) $registro->puesto, ENT_QUOTES, 'UTF-8'); ?>"
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
                                        <option value="" disabled>Seleccione tipo de contrato</option>
                                        <option value="FIJO" <?php echo $registro->tipo_contrato === 'FIJO' ? 'selected' : ''; ?>>FIJO</option>
                                        <option value="INDEFINIDO" <?php echo $registro->tipo_contrato === 'INDEFINIDO' ? 'selected' : ''; ?>>INDEFINIDO</option>
                                        <option value="PRACTICAS" <?php echo $registro->tipo_contrato === 'PRACTICAS' ? 'selected' : ''; ?>>PRACTICAS</option>
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
                                        value="<?php echo htmlspecialchars((string) $registro->fecha_inicio_puesto, ENT_QUOTES, 'UTF-8'); ?>"
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
                                        value="<?php echo htmlspecialchars((string) $registro->fecha_fin_puesto, ENT_QUOTES, 'UTF-8'); ?>"
                                    >
                                </div>
                                <p class="field-tip">Dejar en blanco si es su puesto actual.</p>
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
                                        value="<?php echo htmlspecialchars((string) $registro->direccion_oficina, ENT_QUOTES, 'UTF-8'); ?>"
                                        placeholder="Ej: Edificio Central, Piso 4"
                                    >
                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="cta-row">
                        <a href="listar_contratos_empleados.php" class="btn btn-soft">Cancelar y volver</a>
                        <button type="submit" class="btn btn-save">Guardar cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </section>
</main>

<?php include_once 'pie_contratos_empleados.php'; ?>