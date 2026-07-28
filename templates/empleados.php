<?php
require_once '../app/auth_guard.php';
require_once '../app/conexion.php';

require_module_access(22);

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
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Directorio de Empleados | DLGC</title>
    <link rel="stylesheet" href="/dlgc_rrhh/assets/css/firstpage.css">
    <link rel="stylesheet" href="/dlgc_rrhh/assets/css/empleados.css">
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
                    <span class="profile-name"><?php echo htmlspecialchars($_SESSION['nombre_completo'] ?? 'Usuario'); ?></span>
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
                    <h1>Nuestra Gente</h1>
                </div>
                
                <div class="top-bar-right">
                    <a href="/dlgc_rrhh/templates/login.php" class="icon-btn" aria-label="Cerrar sesión">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                    </a>
                    <button id="theme-toggle" class="icon-btn" aria-label="Cambiar modo oscuro/claro">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path></svg>
                    </button>
                </div>
            </header>

            <div class="dashboard-body fade-in-up">
                
                <div class="employees-layout">
                    <section class="employees-list-container" aria-labelledby="list-title">
                        <header class="section-header">
                            <h2 id="list-title">Compañeros de Equipo</h2>
                            <p class="section-desc">Selecciona un compañero para ver su disponibilidad laboral.</p>
                        </header>

                        <div class="search-bar">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                            <input type="text" id="employee-search" placeholder="Buscar por nombre o cargo...">
                        </div>

                        <div class="employee-grid" id="employee-grid">
                            <!-- Los 10 empleados se cargarán aquí vía JS -->
                        </div>
                    </section>

                    <section class="availability-panel" id="availability-panel">
                        <div class="empty-state" id="empty-state">
                            <div class="empty-icon">
                                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                            </div>
                            <h3>Disponibilidad Laboral</h3>
                            <p>Toca a un compañero para consultar su calendario de disponibilidad.</p>
                        </div>

                        <div class="calendar-container hidden" id="calendar-container">
                            <div class="calendar-header">
                                <div class="selected-employee-info">
                                    <div class="avatar-small"></div>
                                    <div>
                                        <h3 id="selected-name">Nombre</h3>
                                        <span id="selected-role">Cargo</span>
                                    </div>
                                </div>
                                <div class="calendar-nav">
                                    <button class="nav-btn" id="prev-month">&lt;</button>
                                    <span id="current-month">Julio 2026</span>
                                    <button class="nav-btn" id="next-month">&gt;</button>
                                </div>
                            </div>
                            <div class="calendar-grid-header">
                                <span>Do</span><span>Lu</span><span>Ma</span><span>Mi</span><span>Ju</span><span>Vi</span><span>Sa</span>
                            </div>
                            <div class="calendar-grid" id="calendar-days">
                                <!-- Días generados por JS -->
                            </div>
                            <div class="calendar-legend">
                                <div class="legend-item"><span class="dot available"></span> Disponible</div>
                                <div class="legend-item"><span class="dot occupied"></span> En turno</div>
                                <div class="legend-item"><span class="dot off"></span> Descanso</div>
                            </div>
                        </div>
                    </section>
                </div>

            </div>
        </main>
    </div>

    <script src="/dlgc_rrhh/assets/js/theme.js"></script>
    <script src="/dlgc_rrhh/assets/js/firstpage.js"></script>
    <script src="/dlgc_rrhh/assets/js/empleados.js"></script>
</body>
</html>
