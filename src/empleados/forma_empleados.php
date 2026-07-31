<?php

$moduleId = 4;
require_once __DIR__ . '/../../app/src_guard.php';
require_once __DIR__ . '/../../app/conexion.php';

$sentencia = $conexion->query("
    SELECT 
        id_usuario, 
        CONCAT_WS(' ', primer_nombre, NULLIF(segundo_nombre, ''), primer_apellido, NULLIF(segundo_apellido, '')) AS nombre_completo
    FROM t_usuarios
    WHERE fec_delete IS NULL
    ORDER BY nombre_completo, id_usuario
");
$usuariosRegistros = $sentencia->fetchAll(PDO::FETCH_OBJ);

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
$jefesRegistros = $sentencia->fetchAll(PDO::FETCH_OBJ);

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
?>

<?php include_once 'encab_empleados.php'; ?>
<?php require_once __DIR__ . '/../../app/alert_helper.php'; ?>
<?php render_alert_banner(); ?>


<main class="main-container">
    <section class="editor-shell">
        <div class="editor-card">
            <header class="editor-header">
                <h1 class="editor-title">Registrar Empleado</h1>
                <p class="editor-subtitle">Ingresa la información correspondiente al nuevo empleado.</p>
            </header>

            <div class="editor-body">
                <form action="insertar_empleados.php" method="POST" novalidate data-validate>
                    <?php echo csrf_input(); ?>

                    <div class="row g-3">
                        
                        <div class="col-md-6">
                            <div class="field-wrap">
                                <label for="id_usuario" class="form-label">ID Usuario</label>
                                <div class="input-group">
                                    <span class="input-group-text">Usuario</span>
                                    <select name="id_usuario" id="id_usuario" class="form-select" required>
                                        <option value="" disabled selected>Seleccione usuario</option>
                                        <?php foreach ($usuariosRegistros as $usuario): ?>
                                            <option value="<?php echo htmlspecialchars((string) $usuario->id_usuario, ENT_QUOTES, 'UTF-8'); ?>">
                                                <?php echo htmlspecialchars((string) $usuario->id_usuario, ENT_QUOTES, 'UTF-8') . ' - ' . htmlspecialchars((string) $usuario->nombre_completo, ENT_QUOTES, 'UTF-8'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <p class="field-tip">Selecciona el usuario base para registrarlo como empleado.</p>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="field-wrap">
                                <label for="id_jefe" class="form-label">ID Jefe Directo</label>
                                <div class="input-group">
                                    <span class="input-group-text">Jefe</span>
                                    <select name="id_jefe" id="id_jefe" class="form-select">
                                        <option value="" selected>Seleccione un jefe (Opcional)</option>
                                        <?php foreach ($jefesRegistros as $jefe): ?>
                                            <option value="<?php echo htmlspecialchars((string) $jefe->id_usuario, ENT_QUOTES, 'UTF-8'); ?>">
                                                <?php echo htmlspecialchars((string) $jefe->id_usuario, ENT_QUOTES, 'UTF-8') . ' - ' . htmlspecialchars((string) $jefe->nombre_completo, ENT_QUOTES, 'UTF-8'); ?>
                                            </option>
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
                                        <option value="" disabled selected>Seleccione departamento</option>
                                        <?php foreach ($departamentosRegistros as $departamento): ?>
                                            <option value="<?php echo htmlspecialchars((string) $departamento->id_departamento, ENT_QUOTES, 'UTF-8'); ?>">
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
                                    <select name="municipio" id="id_municipio" class="form-select" required disabled>
                                        <option value="" disabled selected>Seleccione departamento primero</option>
                                        <?php foreach ($municipiosRegistros as $municipio): ?>
                                            <option value="<?php echo htmlspecialchars((string) $municipio->id_municipio, ENT_QUOTES, 'UTF-8'); ?>" data-departamento-id="<?php echo htmlspecialchars((string) $municipio->departamento_id, ENT_QUOTES, 'UTF-8'); ?>">
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
                                    >
                                </div>
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
                                    >
                                </div>
                                <p class="field-tip">Opcional. Dejar en blanco si el empleado está activo.</p>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="field-wrap">
                                <label for="genero" class="form-label">Género</label>
                                <div class="input-group">
                                    <span class="input-group-text">Género</span>
                                    <select name="genero" id="genero" class="form-select" required>
                                        <option value="" disabled selected>Seleccione género</option>
                                        <option value="MASCULINO">MASCULINO</option>
                                        <option value="FEMENINO">FEMENINO</option>
                                        <option value="OTRO">OTRO</option>
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
                                        <option value="" disabled selected>Seleccione tipo de sangre</option>
                                        <option value="A+">A+</option>
                                        <option value="A-">A-</option>
                                        <option value="B+">B+</option>
                                        <option value="B-">B-</option>
                                        <option value="AB+">AB+</option>
                                        <option value="AB-">AB-</option>
                                        <option value="O+">O+</option>
                                        <option value="O-">O-</option>
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
                                        <option value="" disabled selected>Seleccione estado civil</option>
                                        <option value="SOLTERO(A)">SOLTERO(A)</option>
                                        <option value="CASADO(A)">CASADO(A)</option>
                                        <option value="DIVORCIADO(A)">DIVORCIADO(A)</option>
                                        <option value="VIUDO(A)">VIUDO(A)</option>
                                        <option value="UNION LIBRE">UNION LIBRE</option>
                                        <option value="SEPARADO(A)">SEPARADO(A)</option>
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
                                        placeholder="Ej: uploads/perfiles/emp001.jpg"
                                    >
                                </div>
                                <p class="field-tip">Opcional. Especifica la ruta de la imagen o URL.</p>
                            </div>
                        </div>

                    </div>

                    <div class="cta-row">
                        <a href="listar_empleados.php" class="btn btn-soft">Cancelar y volver</a>
                        <button type="submit" class="btn btn-save">Registrar empleado</button>
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