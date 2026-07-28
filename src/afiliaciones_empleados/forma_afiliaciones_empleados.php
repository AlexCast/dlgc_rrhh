<?php

$moduleId = 7;
require_once __DIR__ . '/../../app/src_guard.php';
require_once __DIR__ . '/../../app/conexion.php';

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
$usuarios = $sentencia->fetchAll(PDO::FETCH_OBJ);

$sentencia = $conexion->query("SELECT id_eps, nombre_eps FROM t_eps WHERE fec_delete IS NULL ORDER BY nombre_eps");
$epsRegistros = $sentencia->fetchAll(PDO::FETCH_OBJ);

$sentencia = $conexion->query("SELECT id_arl, nombre_arl FROM t_arl WHERE fec_delete IS NULL ORDER BY nombre_arl");
$arlRegistros = $sentencia->fetchAll(PDO::FETCH_OBJ);

try {
    $sentencia = $conexion->query("SELECT id_caja, nombre_caja FROM t_caja_compensacion WHERE fec_delete IS NULL ORDER BY nombre_caja");
} catch (PDOException $exception) {
    $sentencia = $conexion->query("SELECT id_caja AS id_caja, nombre_caja AS nombre_caja FROM t_caja_compensacion WHERE fec_delete IS NULL ORDER BY nombre_caja");
}
$cajaCompensacionRegistros = $sentencia->fetchAll(PDO::FETCH_OBJ);

$sentencia = $conexion->query("SELECT id_pension, nombre_pension FROM t_pension WHERE fec_delete IS NULL ORDER BY nombre_pension");
$pensionRegistros = $sentencia->fetchAll(PDO::FETCH_OBJ);

$sentencia = $conexion->query("SELECT id_cesantia, nombre_cesantia FROM t_cesantias WHERE fec_delete IS NULL ORDER BY nombre_cesantia");
$cesantiaRegistros = $sentencia->fetchAll(PDO::FETCH_OBJ);
?>

<?php include_once 'encab_afiliaciones_empleados.php'; ?>

<main class="main-container">
    <section class="editor-shell">
        <div class="editor-card">
            <header class="editor-header">
                <h1 class="editor-title">Registrar Afiliaciones de Empleados</h1>
                <p class="editor-subtitle">Ingresa las afiliaciones correspondientes al empleado.</p>
            </header>

            <div class="editor-body">
                <form action="insertar_afiliaciones_empleados.php" method="POST" novalidate>
                    <?php echo csrf_input(); ?>

                    <div class="row g-3">
                        
                        <div class="col-md-6">
                            <div class="field-wrap">
                                <label for="id_usuario" class="form-label">ID Usuario (Empleado)</label>
                                <div class="input-group">
                                    <span class="input-group-text">Usuario</span>
                                    <select name="id_usuario" id="id_usuario" class="form-select" required>
                                        <option value="" disabled selected>Seleccione empleado</option>
                                        <?php foreach ($usuarios as $usuario): ?>
                                            <option value="<?php echo htmlspecialchars((string) $usuario->id_usuario, ENT_QUOTES, 'UTF-8'); ?>">
                                                <?php echo htmlspecialchars((string) $usuario->id_usuario, ENT_QUOTES, 'UTF-8') . ' - ' . htmlspecialchars((string) $usuario->nombre_completo, ENT_QUOTES, 'UTF-8'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <p class="field-tip">Selecciona el empleado registrado.</p>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="field-wrap">
                                <label for="id_eps" class="form-label">ID EPS</label>
                                <div class="input-group">
                                    <span class="input-group-text">EPS</span>
                                    <select name="id_eps" id="id_eps" class="form-select" required>
                                        <option value="" disabled selected>Seleccione EPS</option>
                                        <?php foreach ($epsRegistros as $eps): ?>
                                            <option value="<?php echo (int) $eps->id_eps; ?>">
                                                <?php echo htmlspecialchars((string) $eps->nombre_eps, ENT_QUOTES, 'UTF-8'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <p class="field-tip">Selecciona la EPS registrada.</p>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="field-wrap">
                                <label for="id_arl" class="form-label">ID ARL</label>
                                <div class="input-group">
                                    <span class="input-group-text">ARL</span>
                                    <select name="id_arl" id="id_arl" class="form-select" required>
                                        <option value="" disabled selected>Seleccione ARL</option>
                                        <?php foreach ($arlRegistros as $arl): ?>
                                            <option value="<?php echo (int) $arl->id_arl; ?>">
                                                <?php echo htmlspecialchars((string) $arl->nombre_arl, ENT_QUOTES, 'UTF-8'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <p class="field-tip">Selecciona la ARL registrada.</p>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="field-wrap">
                                <label for="id_caja" class="form-label">ID Caja de Compensación</label>
                                <div class="input-group">
                                    <span class="input-group-text">Caja</span>
                                    <select name="id_caja" id="id_caja" class="form-select" required>
                                        <option value="" disabled selected>Seleccione caja</option>
                                        <?php foreach ($cajaCompensacionRegistros as $caja): ?>
                                            <option value="<?php echo (int) $caja->id_caja; ?>">
                                                <?php echo htmlspecialchars((string) $caja->nombre_caja, ENT_QUOTES, 'UTF-8'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <p class="field-tip">Selecciona la caja de compensación registrada.</p>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="field-wrap">
                                <label for="id_pension" class="form-label">ID Pensión</label>
                                <div class="input-group">
                                    <span class="input-group-text">Pensión</span>
                                    <select name="id_pension" id="id_pension" class="form-select" required>
                                        <option value="" disabled selected>Seleccione pensión</option>
                                        <?php foreach ($pensionRegistros as $pension): ?>
                                            <option value="<?php echo (int) $pension->id_pension; ?>">
                                                <?php echo htmlspecialchars((string) $pension->nombre_pension, ENT_QUOTES, 'UTF-8'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <p class="field-tip">Selecciona el fondo de pensión registrado.</p>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="field-wrap">
                                <label for="id_cesantia" class="form-label">ID Cesantías</label>
                                <div class="input-group">
                                    <span class="input-group-text">Cesantías</span>
                                    <select name="id_cesantia" id="id_cesantia" class="form-select" required>
                                        <option value="" disabled selected>Seleccione cesantías</option>
                                        <?php foreach ($cesantiaRegistros as $cesantia): ?>
                                            <option value="<?php echo (int) $cesantia->id_cesantia; ?>">
                                                <?php echo htmlspecialchars((string) $cesantia->nombre_cesantia, ENT_QUOTES, 'UTF-8'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <p class="field-tip">Selecciona el fondo de cesantías registrado.</p>
                            </div>
                        </div>

                    </div>

                    <div class="cta-row">
                        <a href="listar_afiliaciones_empleados.php" class="btn btn-soft">Cancelar y volver</a>
                        <button type="submit" class="btn btn-save">Registrar afiliaciones</button>
                    </div>
                </form>
            </div>
        </div>
    </section>
</main>

<?php include_once 'pie_afiliaciones_empleados.php'; ?>