<?php
require_once '../app/auth_guard.php';
require_once '../app/conexion.php';

require_module_access(1);

// secondpage.php es el dashboard exclusivo del administrador (id_rol = 1).
if (($_SESSION['id_rol'] ?? null) !== 1) {
    header('Location: /dlgc_rrhh/templates/access_denied.php', true, 302);
    exit;
}

$rolPerfil = 'Sin cargo';
$idUsuarioSesion = $_SESSION['id_usuario'] ?? null;

if (is_string($idUsuarioSesion) && trim($idUsuarioSesion) !== '') {
    $sentenciaRol = $conexion->prepare(
        "SELECT c.puesto
         FROM t_contratos_empleados c
         WHERE c.id_usuario = :id_usuario
           AND c.fec_delete IS NULL
         ORDER BY c.fecha_inicio_puesto DESC NULLS LAST, c.id_contrato DESC
         LIMIT 1"
    );

    $sentenciaRol->execute([':id_usuario' => $idUsuarioSesion]);
    $puestoContrato = $sentenciaRol->fetchColumn();

    if ($puestoContrato !== false && trim((string) $puestoContrato) !== '') {
        $rolPerfil = (string) $puestoContrato;
    }
}

$totalEmpleadosActivos = 0;
$afiliacionesPendientes = 0;
$novedadesSemana = 0;
$ultimosTramitesAdmin = [];

function formatearFechaCorta(string $fecha): string {
    $meses = ['ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic'];
    $ts = strtotime($fecha);
    if ($ts === false) {
        return htmlspecialchars($fecha);
    }
    return date('j', $ts) . ' ' . ucfirst($meses[(int) date('n', $ts) - 1]) . ', ' . date('Y', $ts);
}

function claseBadgeEstado(string $estado): string {
    return in_array($estado, ['APROBADO', 'Activo', 'Registrada'], true) ? 'status-approved' : 'status-pending';
}

function textoBadgeEstado(string $estado): string {
    return match ($estado) {
        'APROBADO' => 'Aprobada',
        'PENDIENTE' => 'Pendiente',
        'EN_REVISION' => 'En revisión',
        'RECHAZADO' => 'Rechazada',
        'CANCELADO' => 'Cancelada',
        default => $estado,
    };
}

$stmt = $conexion->query("SELECT COUNT(*) FROM t_empleados WHERE fecha_egreso IS NULL OR fecha_egreso > CURRENT_DATE");
$totalEmpleadosActivos = (int) $stmt->fetchColumn();

$stmt = $conexion->query("SELECT COUNT(*)
    FROM t_empleados e
    WHERE (e.fecha_egreso IS NULL OR e.fecha_egreso > CURRENT_DATE)
      AND NOT EXISTS (
          SELECT 1 FROM t_afiliaciones_empleados a
          WHERE a.id_usuario = e.id_usuario AND a.fec_delete IS NULL
      )");
$afiliacionesPendientes = (int) $stmt->fetchColumn();

$stmt = $conexion->query("SELECT COUNT(*) FROM t_solicitudes_permisos WHERE fec_insert >= CURRENT_DATE - INTERVAL '7 days' AND fec_delete IS NULL");
$novedadesSemana = (int) $stmt->fetchColumn();

$stmtUlt = $conexion->query("WITH ultimos AS (
    SELECT
        'Permiso' AS tipo_tramite,
        STRING_AGG(DISTINCT tp.nombre_tipo, ', ' ORDER BY tp.nombre_tipo) AS detalle,
        sp.fec_insert::date AS fecha_registro,
        sp.estado
    FROM t_solicitudes_permisos sp
    JOIN t_usuarios u ON sp.id_empleado = u.id_usuario
    LEFT JOIN t_solicitudes_permisos_motivos spm ON sp.id_permiso = spm.id_permiso
    LEFT JOIN t_tipos_permisos tp ON spm.id_tipo_permiso = tp.id_tipo_permiso
    WHERE sp.fec_delete IS NULL
    GROUP BY sp.id_permiso, sp.fec_insert, sp.estado

    UNION ALL

    SELECT
        'Afiliación' AS tipo_tramite,
        ('EPS: ' || e.nombre_eps || ' | ARL: ' || a.nombre_arl) AS detalle,
        ae.fec_insert::date AS fecha_registro,
        'Activo' AS estado
    FROM t_afiliaciones_empleados ae
    JOIN t_usuarios u ON ae.id_usuario = u.id_usuario
    JOIN t_eps e ON ae.id_eps = e.id_eps
    JOIN t_arl a ON ae.id_arl = a.id_arl
    WHERE ae.fec_delete IS NULL
)
SELECT tipo_tramite, detalle, fecha_registro, estado
FROM ultimos
ORDER BY fecha_registro DESC
LIMIT 5");
$ultimosTramitesAdmin = $stmtUlt ? $stmtUlt->fetchAll(PDO::FETCH_ASSOC) : [];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Recursos Humanos | DLGC</title>
    <link rel="stylesheet" href="/dlgc_rrhh/assets/css/firstpage.css">
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

            <?php $activeItem = 'inicio'; include __DIR__ . '/partials/sidebar_admin.php'; ?>
            <a href="/dlgc_rrhh/templates/perfil_rrhh.php" class="sidebar-profile" aria-label="Ver mi perfil">
                <div class="profile-avatar" aria-hidden="true">
                    <?php if (!empty($_SESSION['foto_perfil'])): ?>
                        <img src="/dlgc_rrhh/uploads/perfiles/<?php echo htmlspecialchars($_SESSION['foto_perfil']); ?>" 
                             alt="Foto de perfil" 
                             style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                    <?php else: ?>
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                    <?php endif; ?>
                </div>
                <div class="profile-info">
                    <span class="profile-name"><?php echo htmlspecialchars($_SESSION['nombre_completo'] ?? 'Administrador'); ?></span>
                    <span class="profile-role"><?php echo htmlspecialchars($rolPerfil, ENT_QUOTES, 'UTF-8'); ?></span>
                </div>
            </a>
        </aside>

        <main class="main-content">
            
            <header class="top-bar">
                <div class="top-bar-left">
                    <button id="open-sidebar" class="icon-btn mobile-only" aria-label="Abrir menú de navegación">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
                    </button>
                    <h1>Dashboard RRHH</h1>
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
                
                <section class="welcome-card" aria-labelledby="welcome-title">
                    <div class="welcome-text">
                        <h2 id="welcome-title">¡Hola, <?php echo htmlspecialchars($_SESSION['nombre_completo'] ?? 'Administrador'); ?>!</h2>
                        <p>Supervisa el estado de las afiliaciones, gestiona las entidades de seguridad social y mantén al día la información de los empleados.</p>
                    </div>
                    <div class="welcome-decor" aria-hidden="true"></div>
                </section>

                <section class="metrics-grid" aria-label="Resumen de gestión">
                    <div class="metric-card">
                        <span class="metric-title">Empleados Activos</span>
                        <span class="metric-value"><?php echo (int) $totalEmpleadosActivos; ?> <small>Registrados</small></span>
                    </div>
                    <div class="metric-card">
                        <span class="metric-title">Afiliaciones Pendientes</span>
                        <span class="metric-value"><?php echo (int) $afiliacionesPendientes; ?> <small>Por procesar</small></span>
                    </div>
                    <div class="metric-card">
                        <span class="metric-title">Novedades Reportadas</span>
                        <span class="metric-value"><?php echo (int) $novedadesSemana; ?> <small>Esta semana</small></span>
                    </div>
                </section>

                <div class="content-split-grid">
                    
                    <section class="data-card" aria-labelledby="table-title">
                        <h3 id="table-title" class="card-title">Últimos Trámites y Afiliaciones</h3>
                        <div class="table-responsive">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Tipo de Trámite</th>
                                        <th>Entidad / Detalle</th>
                                        <th>Fecha Registro</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($ultimosTramitesAdmin)): ?>
                                    <tr>
                                        <td colspan="4" class="text-center">No hay trámites recientes</td>
                                    </tr>
                                    <?php else: ?>
                                    <?php foreach ($ultimosTramitesAdmin as $tramite): ?>
                                    <tr>
                                        <td>
                                            <?php if ($tramite['tipo_tramite'] === 'Afiliación'): ?>
                                            Afiliación de Empleado
                                            <?php else: ?>
                                            Solicitud de Permiso
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($tramite['detalle'] ?? ''); ?></td>
                                        <td><?php echo formatearFechaCorta($tramite['fecha_registro']); ?></td>
                                        <td><span class="badge <?php echo claseBadgeEstado($tramite['estado']); ?>"><?php echo textoBadgeEstado($tramite['estado']); ?></span></td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </section>

                    <section class="data-card actions-sidebar" aria-labelledby="actions-title">
                        <h3 id="actions-title" class="card-title">Acciones Rápidas</h3>
                        <div class="actions-vertical-group">
                            <?php if (has_module_permission(7, 'crear')): ?>
                            <a href="/dlgc_rrhh/src/afiliaciones_empleados/forma_afiliaciones_empleados.php" class="btn btn-primary full-width-btn">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>
                                Nueva Afiliación de Empleado
                            </a>
                            <?php endif; ?>
                            <?php if (has_module_access(14) || has_module_access(9)): ?>
                            <a href="/dlgc_rrhh/src/eps/listar_eps.php" class="btn btn-outline full-width-btn">
                                Gestionar Entidades (EPS/ARL)
                            </a>
                            <?php endif; ?>
                        </div>
                        <div class="info-alert-box" role="note">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                            <p>Recuerda validar las certificaciones de la EPS y el fondo de pensiones antes de dar el visto bueno a una nueva contratación.</p>
                        </div>
                    </section>

                </div>

            </div>
        </main>
    </div>

    <script src="/dlgc_rrhh/assets/js/theme.js"></script>
    <script src="/dlgc_rrhh/assets/js/firstpage.js"></script>
</body>
</html>