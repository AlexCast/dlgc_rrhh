<?php
require_once '../app/auth_guard.php';
require_once '../app/conexion.php';

require_module_access(5);

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
    <title>Comunicados | DLGC</title>
    <link rel="stylesheet" href="/dlgc_rrhh/assets/css/firstpage.css">
    <link rel="stylesheet" href="/dlgc_rrhh/assets/css/comunicados.css">
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

            <?php $activeItem = 'comunicados'; include __DIR__ . '/partials/sidebar_nav.php'; ?>
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
                    <h1>Comunicados</h1>
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
                
                <section class="comunicados-header">
                    <div>
                        <h2>Últimas Noticias</h2>
                        <p class="text-muted">Mantente al día con la información oficial de la empresa.</p>
                    </div>
                </section>

                <div class="comunicados-list">
                    <!-- Ejemplo de Comunicado 1 -->
                    <article class="comunicado-card">
                        <span class="comunicado-tag">Institucional</span>
                        <div class="comunicado-author">
                            <div class="author-avatar">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                            </div>
                            <div class="author-info">
                                <span class="author-name">Recursos Humanos</span>
                                <span class="comunicado-date">Hace 2 horas</span>
                            </div>
                        </div>
                        <h3 class="comunicado-title">Nueva Política de Teletrabajo 2024</h3>
                        <p class="comunicado-content">
                            Les informamos que a partir del próximo mes entra en vigencia la actualización de nuestra política de trabajo híbrido. Los detalles han sido enviados a sus correos institucionales.
                        </p>
                    </article>

                    <!-- Ejemplo de Comunicado 2 -->
                    <article class="comunicado-card">
                        <span class="comunicado-tag">Eventos</span>
                        <div class="comunicado-author">
                            <div class="author-avatar">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                            </div>
                            <div class="author-info">
                                <span class="author-name">Bienestar Laboral</span>
                                <span class="comunicado-date">Ayer</span>
                            </div>
                        </div>
                        <h3 class="comunicado-title">Celebración del Día de la Familia</h3>
                        <p class="comunicado-content">
                            Están cordialmente invitados a nuestra jornada de integración este viernes 15 de julio. Habrá actividades para niños, almuerzo campestre y rifas. ¡No faltes!
                        </p>
                    </article>

                    <!-- Ejemplo de Comunicado 3 -->
                    <article class="comunicado-card">
                        <span class="comunicado-tag">Operaciones</span>
                        <div class="comunicado-author">
                            <div class="author-avatar">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                            </div>
                            <div class="author-info">
                                <span class="author-name">Soporte Técnico</span>
                                <span class="comunicado-date">2 de Julio, 2026</span>
                            </div>
                        </div>
                        <h3 class="comunicado-title">Mantenimiento de Servidores</h3>
                        <p class="comunicado-content">
                            Se realizará un mantenimiento programado en los servidores de base de datos este domingo desde las 10:00 PM hasta las 2:00 AM. Los servicios de consulta estarán fuera de línea.
                        </p>
                    </article>
                </div>
            </div>
        </main>
    </div>

    <!-- Scripts -->
    <script src="/dlgc_rrhh/assets/js/theme.js"></script>
    <script src="/dlgc_rrhh/assets/js/comunicados.js"></script>
</body>
</html>
