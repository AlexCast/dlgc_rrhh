<?php
require_once '../app/auth_guard.php';
require_once '../app/conexion.php';

require_module_access(22);

$idUsuarioSesion = $_SESSION['id_usuario'] ?? null;

if (!is_string($idUsuarioSesion) || trim($idUsuarioSesion) === '') {
    header('Location: /dlgc_rrhh/templates/login.php', true, 302);
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
        e.fecha_ingreso,
        m.municipio,
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
    LEFT JOIN LATERAL (
        SELECT c1.id_area, c1.puesto, c1.tipo_contrato, c1.direccion_oficina
        FROM t_contratos_empleados c1
        WHERE c1.id_usuario = u.id_usuario
          AND c1.fec_delete IS NULL
        ORDER BY c1.fecha_inicio_puesto DESC NULLS LAST, c1.id_contrato DESC
        LIMIT 1
    ) c ON true
    LEFT JOIN t_areas a
        ON a.id_area = c.id_area
        AND a.fec_delete IS NULL
    LEFT JOIN LATERAL (
        SELECT n1.id_banco, n1.num_cuenta, n1.salario
        FROM t_nomina n1
        WHERE n1.id_usuario = u.id_usuario
          AND n1.fec_delete IS NULL
        ORDER BY n1.fec_insert DESC, n1.id_nomina DESC
        LIMIT 1
    ) n ON true
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
    WHERE u.id_usuario = :id_usuario
      AND u.fec_delete IS NULL
    LIMIT 1"
);

$consultaFicha->execute([':id_usuario' => $idUsuarioSesion]);
$ficha = $consultaFicha->fetch(PDO::FETCH_ASSOC) ?: [];

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

$rolPerfil = $ficha['nombre_area'] ?? ($_SESSION['id_rol'] ?? '');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ficha Técnica del Empleado | DLGC</title>
    <link rel="stylesheet" href="/dlgc_rrhh/assets/css/firstpage.css">
    <link rel="stylesheet" href="/dlgc_rrhh/assets/css/ficha_tecnica.css">
    <link rel="icon" type="image/png" sizes="32x32" href="/dlgc_rrhh/assets/img/favicon.ico">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>

    <div class="dashboard-container">
        
        <aside class="sidebar" id="sidebar" aria-label="Menú principal">
            <div class="sidebar-top">
                <a href="/dlgc_rrhh/templates/index.html" class="logo" aria-label="Inicio">
                    <img src="/dlgc_rrhh/assets/img/logo1.png" alt="Distribuciones La Gran Cacharrería">
                </a>
                <button id="close-sidebar" class="icon-btn mobile-only" aria-label="Cerrar menú">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                </button>
            </div>

            <?php $activeItem = 'empleados'; include __DIR__ . '/partials/sidebar_nav.php'; ?>

            <a href="/dlgc_rrhh/templates/ficha_tecnica.php" class="sidebar-profile" aria-label="Ver mi perfil">
                <div class="profile-avatar" aria-hidden="true">
    <?php if (!empty($_SESSION['foto_perfil'])): ?>
        <img src="/dlgc_rrhh/uploads/perfiles/<?php echo htmlspecialchars($_SESSION['foto_perfil']); ?>" 
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
                        <a href="../app/logout.php" class="icon-btn" aria-label="Cerrar sesión">                        
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
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
                        </div>
                        <div class="info-grid">
                            <div class="info-item">
                                <span class="info-label">Nombre Completo</span>
                                <span class="info-value"><?php echo $escape($nombreCompleto); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Identificación</span>
                                <span class="info-value"><?php echo $escape($documento); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Fecha de Nacimiento</span>
                                <span class="info-value"><?php echo $formatearFecha($ficha['fecha_nacimiento'] ?? null); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Género</span>
                                <span class="info-value"><?php echo $escape($ficha['genero'] ?? null); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Tipo de Sangre</span>
                                <span class="info-value"><?php echo $escape($ficha['tipo_sangre'] ?? null); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Estado Civil</span>
                                <span class="info-value"><?php echo $escape($ficha['estado_civil'] ?? null); ?></span>
                            </div>
                            <div class="info-item full-width">
                                <span class="info-label">Dirección de Residencia</span>
                                <span class="info-value"><?php echo $escape($ficha['direccion_casa'] ?? null); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Número Celular</span>
                                <span class="info-value"><?php echo $escape($ficha['numero_celular'] ?? null); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Correo</span>
                                <span class="info-value"><?php echo $escape($ficha['correo'] ?? null); ?></span>
                            </div>
                        </div>
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

    <script src="/dlgc_rrhh/assets/js/theme.js"></script>
    <script src="/dlgc_rrhh/assets/js/firstpage.js"></script>
    <script src="/dlgc_rrhh/assets/js/ficha_tecnica.js"></script>
</body>
</html>
