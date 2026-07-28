<?php

$moduleId = 4;
$requiredAction = 'actualizar';
require_once __DIR__ . '/../../app/src_guard.php';
if (!isset($_GET['id_usuario']) || empty(trim($_GET['id_usuario']))) {
    echo 'No existe el registro a editar';
    exit();
}

$id_usuario = trim($_GET['id_usuario']);

require_once __DIR__ . '/../../app/conexion.php';

$sentencia = $conexion->prepare('
    SELECT e.id_usuario, e.id_jefe, e.id_municipio, m.departamento_id AS id_departamento, e.fecha_ingreso, e.fecha_egreso, e.genero, e.fecha_nacimiento, e.tipo_sangre, e.estado_civil, e.direccion_casa, e.numero_celular, e.foto_perfil
    FROM t_empleados e
    INNER JOIN t_municipios m ON m.id_municipio = e.id_municipio
    WHERE id_usuario = ?
');
$sentencia->execute([$id_usuario]);
$registro = $sentencia->fetch(PDO::FETCH_OBJ);

// Consultar los empleados activos para asignación de jefe
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

$sentencia = $conexion->query("
    SELECT
        id_departamento,
        departamento
    FROM t_departamentos
    ORDER BY departamento, id_departamento
");
$departamentosRegistros = $sentencia->fetchAll(PDO::FETCH_OBJ);

$sentencia = $conexion->query("
    SELECT
        m.id_municipio,
        m.municipio,
        m.departamento_id,
        d.departamento
    FROM t_municipios m
    INNER JOIN t_departamentos d ON d.id_departamento = m.departamento_id
    WHERE m.estado = 1
    ORDER BY municipio, id_municipio
");
$municipiosRegistros = $sentencia->fetchAll(PDO::FETCH_OBJ);

if (!$registro) {
    echo 'No se encontró el empleado para este registro';
    exit();
}
?>

<?php include_once 'encab_empleados.php'; ?>

<main class="main-container">
    <section class="editor-shell">
        <div class="editor-card">
            <header class="editor-header">
                <h1 class="editor-title">Editar Empleado</h1>
                <p class="editor-subtitle">Actualiza la información del empleado y guarda los cambios de forma segura.</p>
            </header>

            <div class="editor-body">
                <form action="update_empleados.php" method="POST" novalidate>
                    <?php echo csrf_input(); ?>

                    <input type="hidden" name="id_usuario" value="<?php echo htmlspecialchars((string) $registro->id_usuario, ENT_QUOTES, 'UTF-8'); ?>">
                    <div class="row g-3">
                        
                        <div class="col-md-6">
                            <div class="field-wrap">
                                <label for="id_usuario_view" class="form-label">ID Usuario (Empleado)</label>
                                <div class="input-group">
                                    <span class="input-group-text">Usuario</span>
                                    <input
                                        type="text"
                                        id="id_usuario_view"
                                        class="form-control"
                                        disabled
                                        value="<?php echo htmlspecialchars((string) $registro->id_usuario, ENT_QUOTES, 'UTF-8'); ?>"
                                    >
                                </div>
                                <p class="field-tip">Identificador del empleado (Clave principal).</p>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="field-wrap">
                                <label for="id_jefe" class="form-label">ID Jefe Directo</label>
                                <div class="input-group">
                                    <span class="input-group-text">Jefe</span>
                                    <select name="id_jefe" id="id_jefe" class="form-select">
                                        <option value="">Seleccione un jefe (Opcional)</option>
                                        <?php foreach ($empleadosRegistros as $empleado): ?>
                                            <?php if ($empleado->id_usuario !== $registro->id_usuario): ?>
                                                <option value="<?php echo htmlspecialchars((string) $empleado->id_usuario, ENT_QUOTES, 'UTF-8'); ?>" <?php echo (string) $empleado->id_usuario === (string) $registro->id_jefe ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars((string) $empleado->id_usuario, ENT_QUOTES, 'UTF-8') . ' - ' . htmlspecialchars((string) $empleado->nombre_completo, ENT_QUOTES, 'UTF-8'); ?>
                                                </option>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <p class="field-tip">Selecciona el jefe directo del empleado.</p>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="field-wrap">
                                <label for="id_departamento" class="form-label">Departamento</label>
                                <div class="input-group">
                                    <span class="input-group-text">Dpto</span>
                                    <select name="departamento" id="id_departamento" class="form-select" required>
                                        <option value="">Seleccione departamento</option>
                                        <?php foreach ($departamentosRegistros as $departamento): ?>
                                            <option value="<?php echo htmlspecialchars((string) $departamento->id_departamento, ENT_QUOTES, 'UTF-8'); ?>" <?php echo (string) $departamento->id_departamento === (string) $registro->id_departamento ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars((string) $departamento->departamento, ENT_QUOTES, 'UTF-8'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="field-wrap">
                                <label for="id_municipio" class="form-label">Municipio</label>
                                <div class="input-group">
                                    <span class="input-group-text">Mpio</span>
                                    <select name="municipio" id="id_municipio" class="form-select" required>
                                        <option value="">Seleccione departamento primero</option>
                                        <?php foreach ($municipiosRegistros as $municipio): ?>
                                            <option value="<?php echo htmlspecialchars((string) $municipio->id_municipio, ENT_QUOTES, 'UTF-8'); ?>" data-departamento-id="<?php echo htmlspecialchars((string) $municipio->departamento_id, ENT_QUOTES, 'UTF-8'); ?>" <?php echo (string) $municipio->id_municipio === (string) $registro->id_municipio ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars((string) $municipio->municipio, ENT_QUOTES, 'UTF-8'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="field-wrap">
                                <label for="fecha_ingreso" class="form-label">Fecha de Ingreso</label>
                                <div class="input-group">
                                    <span class="input-group-text">Ingreso</span>
                                    <input
                                        type="date"
                                        name="fecha_ingreso"
                                        id="fecha_ingreso"
                                        class="form-control"
                                        required
                                        value="<?php echo htmlspecialchars((string) $registro->fecha_ingreso, ENT_QUOTES, 'UTF-8'); ?>"
                                    >
                                </div>
                                <p class="field-tip">Día en el que el empleado ingresó a la empresa.</p>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="field-wrap">
                                <label for="fecha_egreso" class="form-label">Fecha de Egreso</label>
                                <div class="input-group">
                                    <span class="input-group-text">Egreso</span>
                                    <input
                                        type="date"
                                        name="fecha_egreso"
                                        id="fecha_egreso"
                                        class="form-control"
                                        value="<?php echo htmlspecialchars((string) $registro->fecha_egreso, ENT_QUOTES, 'UTF-8'); ?>"
                                    >
                                </div>
                                <p class="field-tip">Fecha de salida (Dejar en blanco si sigue activo).</p>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="field-wrap">
                                <label for="genero" class="form-label">Género</label>
                                <div class="input-group">
                                    <span class="input-group-text">Género</span>
                                    <select name="genero" id="genero" class="form-select" required>
                                        <option value="" disabled>Seleccione género</option>
                                        <option value="MASCULINO" <?php echo $registro->genero === 'MASCULINO' ? 'selected' : ''; ?>>MASCULINO</option>
                                        <option value="FEMENINO" <?php echo $registro->genero === 'FEMENINO' ? 'selected' : ''; ?>>FEMENINO</option>
                                        <option value="OTRO" <?php echo $registro->genero === 'OTRO' ? 'selected' : ''; ?>>OTRO</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="field-wrap">
                                <label for="fecha_nacimiento" class="form-label">Fecha de Nacimiento</label>
                                <div class="input-group">
                                    <span class="input-group-text">Nacimiento</span>
                                    <input
                                        type="date"
                                        name="fecha_nacimiento"
                                        id="fecha_nacimiento"
                                        class="form-control"
                                        required
                                        value="<?php echo htmlspecialchars((string) $registro->fecha_nacimiento, ENT_QUOTES, 'UTF-8'); ?>"
                                    >
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="field-wrap">
                                <label for="tipo_sangre" class="form-label">Tipo de Sangre</label>
                                <div class="input-group">
                                    <span class="input-group-text">Sangre</span>
                                    <select name="tipo_sangre" id="tipo_sangre" class="form-select" required>
                                        <option value="" disabled>Seleccione tipo de sangre</option>
                                        <?php 
                                        $tiposSangre = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
                                        foreach ($tiposSangre as $ts): 
                                        ?>
                                            <option value="<?php echo $ts; ?>" <?php echo $registro->tipo_sangre === $ts ? 'selected' : ''; ?>>
                                                <?php echo $ts; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="field-wrap">
                                <label for="estado_civil" class="form-label">Estado Civil</label>
                                <div class="input-group">
                                    <span class="input-group-text">Civil</span>
                                    <select name="estado_civil" id="estado_civil" class="form-select" required>
                                        <option value="" disabled>Seleccione estado civil</option>
                                        <?php 
                                        $estadosCiviles = ['SOLTERO(A)', 'CASADO(A)', 'DIVORCIADO(A)', 'VIUDO(A)', 'UNION LIBRE', 'SEPARADO(A)'];
                                        foreach ($estadosCiviles as $estado): 
                                        ?>
                                            <option value="<?php echo $estado; ?>" <?php echo $registro->estado_civil === $estado ? 'selected' : ''; ?>>
                                                <?php echo $estado; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="field-wrap">
                                <label for="direccion_casa" class="form-label">Dirección Casa</label>
                                <div class="input-group">
                                    <span class="input-group-text">Dirección</span>
                                    <input
                                        type="text"
                                        name="direccion_casa"
                                        id="direccion_casa"
                                        class="form-control"
                                        required
                                        maxlength="100"
                                        value="<?php echo htmlspecialchars((string) $registro->direccion_casa, ENT_QUOTES, 'UTF-8'); ?>"
                                        placeholder="Ej: Calle 123 # 45-67"
                                    >
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="field-wrap">
                                <label for="numero_celular" class="form-label">Número Celular</label>
                                <div class="input-group">
                                    <span class="input-group-text">Celular</span>
                                    <input
                                        type="text"
                                        name="numero_celular"
                                        id="numero_celular"
                                        class="form-control"
                                        required
                                        maxlength="10"
                                        value="<?php echo htmlspecialchars((string) $registro->numero_celular, ENT_QUOTES, 'UTF-8'); ?>"
                                        placeholder="Ej: 3001234567"
                                    >
                                </div>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="field-wrap">
                                <label for="foto_perfil" class="form-label">Foto de Perfil (Ruta/URL)</label>
                                <div class="input-group">
                                    <span class="input-group-text">Foto</span>
                                    <input
                                        type="text"
                                        name="foto_perfil"
                                        id="foto_perfil"
                                        class="form-control"
                                        maxlength="255"
                                        value="<?php echo htmlspecialchars((string) $registro->foto_perfil, ENT_QUOTES, 'UTF-8'); ?>"
                                        placeholder="Ej: uploads/perfil_123.png"
                                    >
                                </div>
                                <p class="field-tip">Opcional. Especifica la ruta de la imagen o URL.</p>
                            </div>
                        </div>

                    </div>

                    <div class="cta-row">
                        <a href="listar_empleados.php" class="btn btn-soft">Cancelar y volver</a>
                        <button type="submit" class="btn btn-save">Guardar cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </section>
</main>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var departamento = document.getElementById('id_departamento');
    var municipio = document.getElementById('id_municipio');
    var municipioInicial = municipio.value;
    var municipiosBase = [];

    function normalizarId(valor) {
        return String(valor || '').trim();
    }

    for (var i = 0; i < municipio.options.length; i++) {
        var opt = municipio.options[i];
        if (opt.dataset && opt.dataset.departamentoId) {
            municipiosBase.push({
                value: opt.value,
                text: opt.text,
                departamentoId: normalizarId(opt.dataset.departamentoId)
            });
        }
    }

    function renderMunicipios() {
        var departamentoId = normalizarId(departamento.value);
        var placeholder = 'Seleccione departamento primero';

        municipio.innerHTML = '';

        var optionPlaceholder = document.createElement('option');
        optionPlaceholder.value = '';
        optionPlaceholder.disabled = true;
        optionPlaceholder.selected = true;

        if (departamentoId) {
            placeholder = 'Seleccione municipio';
            municipio.disabled = false;
        } else {
            municipio.disabled = true;
        }

        optionPlaceholder.textContent = placeholder;
        municipio.appendChild(optionPlaceholder);

        if (!departamentoId) {
            return;
        }

        for (var i = 0; i < municipiosBase.length; i++) {
            if (normalizarId(municipiosBase[i].departamentoId) === departamentoId) {
                var opt = document.createElement('option');
                opt.value = municipiosBase[i].value;
                opt.textContent = municipiosBase[i].text;
                if (normalizarId(municipiosBase[i].value) === normalizarId(municipioInicial)) {
                    opt.selected = true;
                }
                municipio.appendChild(opt);
            }
        }

        municipioInicial = municipio.value;
    }

    departamento.addEventListener('change', function () {
        municipioInicial = '';
        renderMunicipios();
    });

    renderMunicipios();
});
</script>

<?php include_once 'pie_empleados.php'; ?>