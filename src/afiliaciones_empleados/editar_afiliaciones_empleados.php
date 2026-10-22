<?php

$moduleId = 7;
$requiredAction = 'actualizar';
require_once __DIR__ . '/../../app/src_guard.php';

if (!isset($_GET['id_usuario'])) {
    echo 'No existe el registro a editar';
    exit();
}

$id_usuario = trim((string) $_GET['id_usuario']);

if ($id_usuario === '') {
    echo 'ID invalido';
    exit();
}

require_once __DIR__ . '/../../app/conexion.php';

$sentencia = $conexion->prepare('
    SELECT id_usuario, id_eps, id_arl, id_caja, id_pension, id_cesantia
    FROM t_afiliaciones_empleados
    WHERE id_usuario = ?
      AND fec_delete IS NULL
');
$sentencia->execute([$id_usuario]);
$registro = $sentencia->fetch(PDO::FETCH_OBJ);

if (!$registro) {
    echo 'No se encontro la afiliacion activa';
    exit();
}

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
<?php require_once __DIR__ . '/../../app/alert_helper.php'; ?>
<?php render_alert_banner(); ?>


<main class="main-container">
    <section class="editor-shell">
        <div class="editor-card">
            <header class="editor-header">
                <h1 class="editor-title">Editar afiliación de empleado</h1>
                <p class="editor-subtitle">Actualiza las afiliaciones y guarda los cambios de forma segura.</p>
            </header>

            <div class="editor-body">
                <form action="update_afiliaciones_empleados.php" method="POST" novalidate data-validate>
                    <?php echo csrf_input(); ?>

                    <input type="hidden" name="id_usuario" value="<?php echo htmlspecialchars((string) $registro->id_usuario, ENT_QUOTES, 'UTF-8'); ?>">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="field-wrap">
                                <label for="id_eps" class="form-label">ID EPS</label>
                                <div class="input-group">
                                    <span class="input-group-text">EPS</span>
                                    <select name="id_eps" id="id_eps" class="form-select" required>
                                        <option value="" disabled>Seleccione EPS</option>
                                        <?php foreach ($epsRegistros as $eps): ?>
                                            <option value="<?php echo (int) $eps->id_eps; ?>" <?php echo ((int) $eps->id_eps === (int) $registro->id_eps) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars((string) $eps->nombre_eps, ENT_QUOTES, 'UTF-8'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="field-wrap">
                                <label for="id_arl" class="form-label">ID ARL</label>
                                <div class="input-group">
                                    <span class="input-group-text">ARL</span>
                                    <select name="id_arl" id="id_arl" class="form-select" required>
                                        <option value="" disabled>Seleccione ARL</option>
                                        <?php foreach ($arlRegistros as $arl): ?>
                                            <option value="<?php echo (int) $arl->id_arl; ?>" <?php echo ((int) $arl->id_arl === (int) $registro->id_arl) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars((string) $arl->nombre_arl, ENT_QUOTES, 'UTF-8'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="field-wrap">
                                <label for="id_caja" class="form-label">ID Caja de Compensación</label>
                                <div class="input-group">
                                    <span class="input-group-text">Caja</span>
                                    <select name="id_caja" id="id_caja" class="form-select" required>
                                        <option value="" disabled>Seleccione caja</option>
                                        <?php foreach ($cajaCompensacionRegistros as $caja): ?>
                                            <option value="<?php echo (int) $caja->id_caja; ?>" <?php echo ((int) $caja->id_caja === (int) $registro->id_caja) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars((string) $caja->nombre_caja, ENT_QUOTES, 'UTF-8'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="field-wrap">
                                <label for="id_pension" class="form-label">ID Pensión</label>
                                <div class="input-group">
                                    <span class="input-group-text">Pensión</span>
                                    <select name="id_pension" id="id_pension" class="form-select" required>
                                        <option value="" disabled>Seleccione pensión</option>
                                        <?php foreach ($pensionRegistros as $pension): ?>
                                            <option value="<?php echo (int) $pension->id_pension; ?>" <?php echo ((int) $pension->id_pension === (int) $registro->id_pension) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars((string) $pension->nombre_pension, ENT_QUOTES, 'UTF-8'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="field-wrap">
                                <label for="id_cesantia" class="form-label">ID Cesantías</label>
                                <div class="input-group">
                                    <span class="input-group-text">Cesantías</span>
                                    <select name="id_cesantia" id="id_cesantia" class="form-select" required>
                                        <option value="" disabled>Seleccione cesantías</option>
                                        <?php foreach ($cesantiaRegistros as $cesantia): ?>
                                            <option value="<?php echo (int) $cesantia->id_cesantia; ?>" <?php echo ((int) $cesantia->id_cesantia === (int) $registro->id_cesantia) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars((string) $cesantia->nombre_cesantia, ENT_QUOTES, 'UTF-8'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="cta-row">
                        <a href="listar_afiliaciones_empleados.php" class="btn btn-soft">Cancelar y volver</a>
                        <button type="submit" class="btn btn-save">Guardar cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </section>
</main>

<?php include_once 'pie_afiliaciones_empleados.php'; ?>