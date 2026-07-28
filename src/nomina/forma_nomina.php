<?php

$moduleId = 15;
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

$sentencia = $conexion->query("SELECT id_banco, nombre_banco FROM t_bancos WHERE fec_delete IS NULL ORDER BY nombre_banco");
$bancosRegistros = $sentencia->fetchAll(PDO::FETCH_OBJ);
?>

<?php include_once 'encab_nomina.php'; ?>

<main class="main-container">
    <section class="editor-shell">
        <div class="editor-card">
            <header class="editor-header">
                <h1 class="editor-title">Registrar Nómina</h1>
                <p class="editor-subtitle">Ingresa la información de nómina correspondiente al empleado.</p>
            </header>

            <div class="editor-body">
                <form action="insertar_nomina.php" method="POST" novalidate>
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
                                <p class="field-tip">Selecciona el empleado para asignarle la nómina.</p>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="field-wrap">
                                <label for="id_banco" class="form-label">ID Banco</label>
                                <div class="input-group">
                                    <span class="input-group-text">Banco</span>
                                    <select name="id_banco" id="id_banco" class="form-select" required>
                                        <option value="" disabled selected>Seleccione banco</option>
                                        <?php foreach ($bancosRegistros as $banco): ?>
                                            <option value="<?php echo (int) $banco->id_banco; ?>">
                                                <?php echo htmlspecialchars((string) $banco->nombre_banco, ENT_QUOTES, 'UTF-8'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <p class="field-tip">Selecciona el banco registrado.</p>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="field-wrap">
                                <label for="num_cuenta" class="form-label">Número de cuenta</label>
                                <div class="input-group">
                                    <span class="input-group-text">Cuenta</span>
                                    <input
                                        type="text"
                                        name="num_cuenta"
                                        id="num_cuenta"
                                        class="form-control"
                                        required
                                        maxlength="20"
                                        placeholder="Ej: 1234567890"
                                    >
                                </div>
                                <p class="field-tip">Número de cuenta destino para el depósito.</p>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="field-wrap">
                                <label for="salario" class="form-label">Salario</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input
                                        type="number"
                                        step="0.01"
                                        name="salario"
                                        id="salario"
                                        class="form-control"
                                        required
                                        placeholder="Ej: 1500000.00"
                                    >
                                </div>
                                <p class="field-tip">Monto total del salario asignado.</p>
                            </div>
                        </div>

                    </div>

                    <div class="cta-row">
                        <a href="listar_nomina.php" class="btn btn-soft">Cancelar y volver</a>
                        <button type="submit" class="btn btn-save">Registrar nómina</button>
                    </div>
                </form>
            </div>
        </div>
    </section>
</main>

<?php include_once 'pie_nomina.php'; ?>