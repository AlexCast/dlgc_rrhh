<?php
require_once '../app/auth_guard.php';
require_once '../app/conexion.php';
require_once '../app/helpers/TurnstileHelper.php';

require_module_access(22);

$idUsuarioSesion = $_SESSION['id_usuario'] ?? null;

$turnstileSiteKey = TurnstileHelper::siteKey();

if (!is_string($idUsuarioSesion) || trim($idUsuarioSesion) === '') {
    header('Location: /templates/login.php', true, 302);
    exit;
}

$consultaFicha = $conexion->prepare(
    "SELECT
        u.id_usuario,
        u.tipo_documento,
        u.correo,
        u.primer_nombre,
        u.segundo_nombre,
        u.primer_apellido,
        u.segundo_apellido,
        e.fecha_nacimiento,
        e.genero,
        e.tipo_sangre,
        e.estado_civil,
        e.direccion_casa,
        e.numero_celular,
        e.id_municipio,
        e.fecha_ingreso,
        m.municipio,
        m.departamento_id AS id_departamento,
        d.departamento,
        c.puesto,
        c.tipo_contrato,
        c.direccion_oficina,
        a.nombre_area,
        n.salario,
        n.num_cuenta,
        b.nombre_banco,
        eps.nombre_eps,
        arl.nombre_arl,
        caja.nombre_caja,
        pen.nombre_pension,
        ces.nombre_cesantia
    FROM t_usuarios u
    LEFT JOIN t_empleados e
        ON e.id_usuario = u.id_usuario
        AND e.fec_delete IS NULL
    LEFT JOIN t_municipios m
        ON m.id_municipio = e.id_municipio
    LEFT JOIN t_departamentos d
        ON d.id_departamento = m.departamento_id
    LEFT JOIN t_contratos_empleados c
        ON c.id_usuario = u.id_usuario
       AND c.fec_delete IS NULL
       AND c.id_contrato = (
           SELECT c1.id_contrato
           FROM t_contratos_empleados c1
           WHERE c1.id_usuario = u.id_usuario
             AND c1.fec_delete IS NULL
           ORDER BY c1.fecha_inicio_puesto DESC, c1.id_contrato DESC
           LIMIT 1
       )
    LEFT JOIN t_areas a
        ON a.id_area = c.id_area
        AND a.fec_delete IS NULL
    LEFT JOIN t_nomina n
        ON n.id_usuario = u.id_usuario
       AND n.fec_delete IS NULL
       AND n.id_nomina = (
           SELECT n1.id_nomina
           FROM t_nomina n1
           WHERE n1.id_usuario = u.id_usuario
             AND n1.fec_delete IS NULL
           ORDER BY n1.fec_insert DESC, n1.id_nomina DESC
           LIMIT 1
       )
    LEFT JOIN t_bancos b
        ON b.id_banco = n.id_banco
        AND b.fec_delete IS NULL
    LEFT JOIN t_afiliaciones_empleados af
        ON af.id_usuario = u.id_usuario
        AND af.fec_delete IS NULL
    LEFT JOIN t_eps eps
        ON eps.id_eps = af.id_eps
        AND eps.fec_delete IS NULL
    LEFT JOIN t_arl arl
        ON arl.id_arl = af.id_arl
        AND arl.fec_delete IS NULL
    LEFT JOIN t_caja_compensacion caja
        ON caja.id_caja = af.id_caja
        AND caja.fec_delete IS NULL
    LEFT JOIN t_pension pen
        ON pen.id_pension = af.id_pension
        AND pen.fec_delete IS NULL
    LEFT JOIN t_cesantias ces
        ON ces.id_cesantia = af.id_cesantia
        AND ces.fec_delete IS NULL
    WHERE u.id_usuario = ?
      AND u.fec_delete IS NULL
    LIMIT 1"
);

$consultaFicha->execute([$idUsuarioSesion]);
$ficha = $consultaFicha->fetch(PDO::FETCH_ASSOC) ?: [];

// Catálogos para el formulario de edición (departamento → municipios).
$departamentos = $conexion->query(
    'SELECT id_departamento, departamento FROM t_departamentos ORDER BY departamento, id_departamento'
)->fetchAll(PDO::FETCH_ASSOC);

$municipios = $conexion->query(
    'SELECT m.id_municipio, m.municipio, m.departamento_id
     FROM t_municipios m
     WHERE m.estado = 1
     ORDER BY m.municipio, m.id_municipio'
)->fetchAll(PDO::FETCH_ASSOC);

$escape = static function ($valor): string {
    $texto = trim((string) ($valor ?? ''));
    return $texto !== '' ? htmlspecialchars($texto, ENT_QUOTES, 'UTF-8') : 'N/A';
};

$formatearFecha = static function ($valor): string {
    if (empty($valor)) {
        return 'N/A';
    }

    $timestamp = strtotime((string) $valor);
    if ($timestamp === false) {
        return 'N/A';
    }

    return date('d/m/Y', $timestamp);
};

$formatearSalario = static function ($valor): string {
    if ($valor === null || $valor === '') {
        return 'N/A';
    }

    return '$' . number_format((float) $valor, 0, ',', '.');
};

$nombreCompleto = trim(implode(' ', array_filter([
    $ficha['primer_nombre'] ?? '',
    $ficha['segundo_nombre'] ?? '',
    $ficha['primer_apellido'] ?? '',
    $ficha['segundo_apellido'] ?? '',
])));

$documento = trim(implode(' ', array_filter([
    $ficha['tipo_documento'] ?? '',
    $ficha['id_usuario'] ?? '',
])));

// Escape para atributos value de inputs (no aplica el reemplazo por 'N/A').
$valor = static function ($campo): string {
    return htmlspecialchars((string) ($campo ?? ''), ENT_QUOTES, 'UTF-8');
};

// Municipio / departamento para la vista de datos personales.
$ubicacion = trim(implode(' / ', array_filter([
    $ficha['municipio'] ?? '',
    $ficha['departamento'] ?? '',
])));

$rolPerfil = $ficha['nombre_area'] ?? ($_SESSION['id_rol'] ?? '');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ficha Técnica del Empleado | DLGC</title>
    <link rel="stylesheet" href="/assets/css/firstpage.css?v=<?php echo filemtime(__DIR__ . '/../assets/css/firstpage.css'); ?>">
    <link rel="stylesheet" href="/assets/css/ficha_tecnica.css?v=<?php echo filemtime(__DIR__ . '/../assets/css/ficha_tecnica.css'); ?>">
    <link rel="icon" type="image/png" sizes="32x32" href="/assets/img/favicon.ico">
</head>
<body>

    <div class="dashboard-container">
        
        <aside class="sidebar" id="sidebar" aria-label="Menú principal">
            <div class="sidebar-top">
                <a href="/templates/index.html" class="logo" aria-label="Inicio">
                    <img src="/assets/img/logo1.png" alt="Distribuciones La Gran Cacharrería">
                </a>
                <button id="close-sidebar" class="icon-btn mobile-only" aria-label="Cerrar menú">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                </button>
            </div>

            <?php
            // Mismo criterio que firstpage.php: el sidebar se mantiene según el rol.
            $activeItem = 'empleados';
            if (($_SESSION['id_rol'] ?? null) === 1) {
                include __DIR__ . '/partials/sidebar_admin.php';
            } else {
                include __DIR__ . '/partials/sidebar_nav.php';
            }
            ?>

            <a href="/templates/ficha_tecnica.php" class="sidebar-profile" aria-label="Ver mi perfil">
                <div class="profile-avatar" aria-hidden="true">
    <?php if (!empty($_SESSION['foto_perfil'])): ?>
        <img src="<?php echo htmlspecialchars($_SESSION['foto_perfil']); ?>" 
             alt="Foto de perfil" 
             style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
    <?php else: ?>
        <!-- Tu SVG original como respaldo -->
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
            <circle cx="12" cy="7" r="4"></circle>
        </svg>
    <?php endif; ?>
</div>
                <div class="profile-info">
                    <span class="profile-name"><?php echo $escape($nombreCompleto); ?></span>
                    <span class="profile-role"><?php echo $escape($rolPerfil); ?></span>
                </div>
            </a>
        </aside>

        <main class="main-content">
            
            <header class="top-bar">
                <div class="top-bar-left">
                    <button id="open-sidebar" class="icon-btn mobile-only" aria-label="Abrir menú de navegación">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
                    </button>
                    <h1>Mi Ficha Técnica</h1>
                </div>
                
                <div class="top-bar-right">
                    <a href="/templates/firstpage.php" class="icon-btn" aria-label="Volver al inicio">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
                    </a>
                    <button id="theme-toggle" class="icon-btn" aria-label="Cambiar modo oscuro/claro">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path></svg>
                    </button>
                </div>
            </header>
            <div class="dashboard-body fade-in-up">
                
                <div class="ficha-grid">
                    <!-- Datos Personales -->
                    <section class="data-card" aria-labelledby="personal-title">
                        <div class="card-header">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="card-icon"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                            <h2 id="personal-title" class="card-title">Datos Personales</h2>
                            <button type="button" id="btn-editar-personales" class="card-edit-btn" aria-label="Editar mis datos personales" aria-expanded="false" aria-controls="personal-form">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                                <span>Editar</span>
                            </button>
                        </div>
                        <div class="info-grid" id="personal-view">
                            <div class="info-item">
                                <span class="info-label">Nombre Completo</span>
                                <span class="info-value" id="v-nombre-completo"><?php echo $escape($nombreCompleto); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Identificación</span>
                                <span class="info-value"><?php echo $escape($documento); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Fecha de Nacimiento</span>
                                <span class="info-value" id="v-fecha-nacimiento"><?php echo $formatearFecha($ficha['fecha_nacimiento'] ?? null); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Género</span>
                                <span class="info-value" id="v-genero"><?php echo $escape($ficha['genero'] ?? null); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Tipo de Sangre</span>
                                <span class="info-value" id="v-tipo-sangre"><?php echo $escape($ficha['tipo_sangre'] ?? null); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Estado Civil</span>
                                <span class="info-value" id="v-estado-civil"><?php echo $escape($ficha['estado_civil'] ?? null); ?></span>
                            </div>
                            <div class="info-item full-width">
                                <span class="info-label">Dirección de Residencia</span>
                                <span class="info-value" id="v-direccion"><?php echo $escape($ficha['direccion_casa'] ?? null); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Municipio / Departamento</span>
                                <span class="info-value" id="v-municipio"><?php echo $escape($ubicacion); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Número Celular</span>
                                <span class="info-value" id="v-celular"><?php echo $escape($ficha['numero_celular'] ?? null); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Correo</span>
                                <span class="info-value"><?php echo $escape($ficha['correo'] ?? null); ?></span>
                            </div>
                        </div>

                        <form id="personal-form" class="edit-form" hidden>
                            <?php echo csrf_input(); ?>
                            <div class="edit-form-grid">
                                <div class="edit-field">
                                    <label for="pf-primer-nombre">Primer Nombre <span class="required-mark" aria-hidden="true">*</span></label>
                                    <input type="text" id="pf-primer-nombre" name="primer_nombre" required maxlength="30" autocomplete="given-name" value="<?php echo $valor($ficha['primer_nombre'] ?? null); ?>">
                                </div>
                                <div class="edit-field">
                                    <label for="pf-segundo-nombre">Segundo Nombre</label>
                                    <input type="text" id="pf-segundo-nombre" name="segundo_nombre" maxlength="30" autocomplete="additional-name" value="<?php echo $valor($ficha['segundo_nombre'] ?? null); ?>">
                                </div>
                                <div class="edit-field">
                                    <label for="pf-primer-apellido">Primer Apellido <span class="required-mark" aria-hidden="true">*</span></label>
                                    <input type="text" id="pf-primer-apellido" name="primer_apellido" required maxlength="30" autocomplete="family-name" value="<?php echo $valor($ficha['primer_apellido'] ?? null); ?>">
                                </div>
                                <div class="edit-field">
                                    <label for="pf-segundo-apellido">Segundo Apellido</label>
                                    <input type="text" id="pf-segundo-apellido" name="segundo_apellido" maxlength="30" autocomplete="additional-name" value="<?php echo $valor($ficha['segundo_apellido'] ?? null); ?>">
                                </div>
                                <div class="edit-field">
                                    <label for="pf-fecha-nacimiento">Fecha de Nacimiento <span class="required-mark" aria-hidden="true">*</span></label>
                                    <input type="date" id="pf-fecha-nacimiento" name="fecha_nacimiento" required max="<?php echo date('Y-m-d'); ?>" value="<?php echo $valor($ficha['fecha_nacimiento'] ?? null); ?>">
                                </div>
                                <div class="edit-field">
                                    <label for="pf-genero">Género <span class="required-mark" aria-hidden="true">*</span></label>
                                    <select id="pf-genero" name="genero" required>
                                        <?php foreach (['MASCULINO', 'FEMENINO', 'OTRO'] as $opcionGenero): ?>
                                            <option value="<?php echo $opcionGenero; ?>" <?php echo ($ficha['genero'] ?? '') === $opcionGenero ? 'selected' : ''; ?>><?php echo $opcionGenero; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="edit-field">
                                    <label for="pf-tipo-sangre">Tipo de Sangre <span class="required-mark" aria-hidden="true">*</span></label>
                                    <select id="pf-tipo-sangre" name="tipo_sangre" required>
                                        <?php foreach (['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'] as $opcionSangre): ?>
                                            <option value="<?php echo $opcionSangre; ?>" <?php echo ($ficha['tipo_sangre'] ?? '') === $opcionSangre ? 'selected' : ''; ?>><?php echo $opcionSangre; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="edit-field">
                                    <label for="pf-estado-civil">Estado Civil <span class="required-mark" aria-hidden="true">*</span></label>
                                    <select id="pf-estado-civil" name="estado_civil" required>
                                        <?php foreach (['SOLTERO(A)', 'CASADO(A)', 'DIVORCIADO(A)', 'VIUDO(A)', 'UNION LIBRE', 'SEPARADO(A)'] as $opcionCivil): ?>
                                            <option value="<?php echo $opcionCivil; ?>" <?php echo ($ficha['estado_civil'] ?? '') === $opcionCivil ? 'selected' : ''; ?>><?php echo $opcionCivil; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="edit-field full-width">
                                    <label for="pf-direccion-casa">Dirección de Residencia <span class="required-mark" aria-hidden="true">*</span></label>
                                    <input type="text" id="pf-direccion-casa" name="direccion_casa" required maxlength="100" autocomplete="street-address" value="<?php echo $valor($ficha['direccion_casa'] ?? null); ?>">
                                </div>
                                <div class="edit-field">
                                    <label for="pf-departamento">Departamento <span class="required-mark" aria-hidden="true">*</span></label>
                                    <select id="pf-departamento" name="departamento" required>
                                        <option value="">Seleccione departamento</option>
                                        <?php foreach ($departamentos as $departamento): ?>
                                            <option value="<?php echo (int) $departamento['id_departamento']; ?>" <?php echo (int) ($ficha['id_departamento'] ?? 0) === (int) $departamento['id_departamento'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars((string) $departamento['departamento'], ENT_QUOTES, 'UTF-8'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="edit-field">
                                    <label for="pf-municipio">Municipio <span class="required-mark" aria-hidden="true">*</span></label>
                                    <select id="pf-municipio" name="id_municipio" required>
                                        <option value="">Seleccione departamento primero</option>
                                        <?php foreach ($municipios as $municipio): ?>
                                            <option value="<?php echo (int) $municipio['id_municipio']; ?>"
                                                data-departamento-id="<?php echo (int) $municipio['departamento_id']; ?>"
                                                <?php echo (int) ($ficha['id_municipio'] ?? 0) === (int) $municipio['id_municipio'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars((string) $municipio['municipio'], ENT_QUOTES, 'UTF-8'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="edit-field">
                                    <label for="pf-numero-celular">Número Celular <span class="required-mark" aria-hidden="true">*</span></label>
                                    <input type="tel" id="pf-numero-celular" name="numero_celular" required inputmode="numeric" pattern="[0-9]{10}" maxlength="10" autocomplete="tel" value="<?php echo $valor($ficha['numero_celular'] ?? null); ?>">
                                </div>
                                <div class="edit-field">
                                    <label for="pf-correo">Correo (solo lectura)</label>
                                    <input type="email" id="pf-correo" disabled value="<?php echo $valor($ficha['correo'] ?? null); ?>">
                                    <p class="field-tip">El documento y el correo no se cambian desde aquí; contacta a RRHH si necesitas actualizarlos.</p>
                                </div>
                                <div class="edit-field full-width">
                                    <label>Foto de Perfil</label>
                                    <div class="edit-photo-row">
                                        <div class="edit-photo-preview" id="edit-photo-preview">
                                            <?php if (!empty($_SESSION['foto_perfil'])): ?>
                                                <img src="<?php echo htmlspecialchars((string) $_SESSION['foto_perfil'], ENT_QUOTES, 'UTF-8'); ?>" alt="Foto de perfil actual">
                                            <?php else: ?>
                                                <span class="edit-photo-placeholder" aria-hidden="true"><svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg></span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="edit-photo-actions">
                                            <input type="file" id="pf-foto" name="foto_perfil" accept="image/jpeg,image/png,image/webp">
                                            <?php if (!empty($_SESSION['foto_perfil'])): ?>
                                                <label class="edit-photo-quitar">
                                                    <input type="checkbox" name="quitar_foto" id="pf-quitar-foto" value="1">
                                                    Quitar foto actual
                                                </label>
                                            <?php endif; ?>
                                            <p class="field-tip">Opcional. JPG, PNG o WebP de máximo 2 MB. La imagen se comprime y solo es visible para usuarios con sesión.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php if ($turnstileSiteKey !== ''): ?>
                                <div id="turnstile-personales" data-sitekey="<?php echo htmlspecialchars($turnstileSiteKey, ENT_QUOTES, 'UTF-8'); ?>" data-action="empleado_foto"></div>
                            <?php endif; ?>
                            <div class="edit-form-actions">
                                <button type="submit" class="edit-btn edit-btn-primary">Guardar cambios</button>
                                <button type="button" class="edit-btn edit-btn-secondary" id="btn-cancelar-personales">Cancelar</button>
                            </div>
                            <p id="personal-form-message" class="form-message" role="alert" hidden></p>
                        </form>
                    </section>

                    <!-- Datos Laborales -->
                    <section class="data-card" aria-labelledby="laboral-title">
                        <div class="card-header">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="card-icon"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path></svg>
                            <h2 id="laboral-title" class="card-title">Datos Laborales</h2>
                        </div>
                        <div class="info-grid">
                            <div class="info-item">
                                <span class="info-label">Área</span>
                                <span class="info-value"><?php echo $escape($ficha['nombre_area'] ?? null); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Cargo</span>
                                <span class="info-value"><?php echo $escape($ficha['puesto'] ?? null); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Tipo de Contrato</span>
                                <span class="info-value"><?php echo $escape($ficha['tipo_contrato'] ?? null); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Fecha de Ingreso</span>
                                <span class="info-value"><?php echo $formatearFecha($ficha['fecha_ingreso'] ?? null); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Sueldo Básico</span>
                                <span class="info-value"><?php echo $formatearSalario($ficha['salario'] ?? null); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Sede / Oficina</span>
                                <span class="info-value"><?php echo $escape($ficha['direccion_oficina'] ?? null); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Banco de Pago</span>
                                <span class="info-value"><?php echo $escape($ficha['nombre_banco'] ?? null); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Número de Cuenta</span>
                                <span class="info-value"><?php echo $escape($ficha['num_cuenta'] ?? null); ?></span>
                            </div>
                        </div>
                    </section>

                    <!-- Seguridad Social -->
                    <section class="data-card" aria-labelledby="seguridad-title">
                        <div class="card-header">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="card-icon"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                            <h2 id="seguridad-title" class="card-title">Seguridad Social</h2>
                        </div>
                        <div class="info-grid">
                            <div class="info-item">
                                <span class="info-label">EPS</span>
                                <span class="info-value"><?php echo $escape($ficha['nombre_eps'] ?? null); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">ARL</span>
                                <span class="info-value"><?php echo $escape($ficha['nombre_arl'] ?? null); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Caja de Compensación</span>
                                <span class="info-value"><?php echo $escape($ficha['nombre_caja'] ?? null); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Fondo de Pensiones</span>
                                <span class="info-value"><?php echo $escape($ficha['nombre_pension'] ?? null); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Fondo de Cesantías</span>
                                <span class="info-value"><?php echo $escape($ficha['nombre_cesantia'] ?? null); ?></span>
                            </div>
                        </div>
                    </section>
                </div>

            </div>
        </main>
    </div>

    <?php if ($turnstileSiteKey !== ''): ?>
    <script id="turnstile-script" src="https://challenges.cloudflare.com/turnstile/v0/api.js" defer></script>
    <?php endif; ?>
    <script src="/assets/js/theme.js?v=<?php echo filemtime(__DIR__ . '/../assets/js/theme.js'); ?>"></script>
    <script src="/assets/js/firstpage.js?v=<?php echo filemtime(__DIR__ . '/../assets/js/firstpage.js'); ?>"></script>
    <script src="/assets/js/ficha_tecnica.js?v=<?php echo filemtime(__DIR__ . '/../assets/js/ficha_tecnica.js'); ?>"></script>
</body>
</html>
