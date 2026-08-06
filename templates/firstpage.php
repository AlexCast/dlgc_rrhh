<?php
require_once '../app/auth_guard.php';
require_once '../app/conexion.php';

require_module_access(1);

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

$saldoVacaciones = null;
if (is_string($idUsuarioSesion) && trim($idUsuarioSesion) !== '') {
    $sentenciaSaldo = $conexion->prepare('SELECT * FROM fun_calcular_saldo_vacaciones(:id_usuario)');
    $sentenciaSaldo->execute([':id_usuario' => $idUsuarioSesion]);
    $filaSaldo = $sentenciaSaldo->fetch(PDO::FETCH_ASSOC);
    if ($filaSaldo !== false) {
        $saldoVacaciones = $filaSaldo;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Empleado | DLGC</title>
    <link rel="stylesheet" href="/dlgc_rrhh/assets/css/firstpage.css">
    <link rel="icon" type="image/png" sizes="32x32" href="/dlgc_rrhh/assets/img/favicon.ico">
    <link rel="icon" type="image/png" sizes="32x32" href="/dlgc_rrhh/assets/img/favicon.ico">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="dashboard-container">
        
        <aside class="sidebar" id="sidebar" aria-label="Menú principal">
            <div class="sidebar-top">
                <a href="#" class="logo" aria-label="Inicio">
                    <img src="/dlgc_rrhh/assets/img/logo1.png" alt="Distribuciones La Gran Cacharrería">
                </a>
                <button id="close-sidebar" class="icon-btn mobile-only" aria-label="Cerrar menú">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                </button>
            </div>

            <?php $activeItem = 'inicio'; include __DIR__ . '/partials/sidebar_nav.php'; ?>
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
                    <h1>Dashboard</h1>
                </div>
                
                <div class="top-bar-right">
                        <a href="../app/logout.php" class="icon-btn" aria-label="Cerrar sesión">                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                    </a>
                    <button id="theme-toggle" class="icon-btn" aria-label="Cambiar modo oscuro/claro">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path></svg>
                    </button>
                </div>
            </header>

            <div class="dashboard-body fade-in-up">
                
                <section class="welcome-card" aria-labelledby="welcome-title">
                    <div class="welcome-text">
                        <h2 id="welcome-title">¡Hola de nuevo, <?php echo htmlspecialchars($_SESSION['nombre_completo'] ?? 'Usuario'); ?>!</h2>                        <p>Revisa el estado de tus novedades laborales, radica certificados médicos y mantente al día con la empresa.</p>
                    </div>
                    <div class="welcome-decor" aria-hidden="true"></div>
                </section>

                <section class="metrics-grid" aria-label="Resumen de saldos y solicitudes">
                    <div class="metric-card">
                        <span class="metric-title">Vacaciones Disponibles</span>
                        <?php if ($saldoVacaciones === null || (int) $saldoVacaciones['anios_servicio'] < 1): ?>
                        <span class="metric-value">0 <small>Días</small></span>
                        <small class="metric-hint">Aún no cumples tu primer año de servicio.</small>
                        <?php else: ?>
                        <span class="metric-value"><?php echo (int) $saldoVacaciones['saldo_disponible']; ?> <small>Días</small></span>
                        <?php if ($saldoVacaciones['alerta_vencimiento_proximo']): ?>
                        <small class="metric-hint metric-hint--warning">⚠ Se pierden en <?php echo (int) $saldoVacaciones['dias_para_vencer']; ?> día(s) (<?php echo htmlspecialchars($saldoVacaciones['periodo_fin']); ?>)</small>
                        <?php else: ?>
                        <small class="metric-hint">Ciclo vigente hasta <?php echo htmlspecialchars($saldoVacaciones['periodo_fin']); ?></small>
                        <?php endif; ?>
                        <?php endif; ?>
                    </div>
                    <div class="metric-card">
                        <span class="metric-title">Incapacidades este año</span>
                        <span class="metric-value">3 <small>Días</small></span>
                    </div>
                    <div class="metric-card">
                        <span class="metric-title">Solicitudes en Proceso</span>
                        <span class="metric-value">1 <small>Activa</small></span>
                    </div>
                </section>

                <div class="content-split-grid">
                    
                    <section class="data-card" aria-labelledby="table-title">
                        <h3 id="table-title" class="card-title">Últimos Trámites e Incapacidades</h3>
                        <div class="table-responsive">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Tipo de Solicitud</th>
                                        <th>Fecha de Inicio</th>
                                        <th>Duración</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Incapacidad Médica (General)</td>
                                        <td>15 Jun, 2026</td>
                                        <td>2 días</td>
                                        <td><span class="badge status-approved">Aprobada</span></td>
                                    </tr>
                                    <tr>
                                        <td>Permiso por Calamidad Doméstica</td>
                                        <td>02 May, 2026</td>
                                        <td>1 día</td>
                                        <td><span class="badge status-approved">Aprobada</span></td>
                                    </tr>
                                    <tr>
                                        <td>Solicitud de Vacaciones</td>
                                        <td>10 Jul, 2026</td>
                                        <td>5 días</td>
                                        <td><span class="badge status-pending">En revisión</span></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </section>

                    <section class="data-card actions-sidebar" aria-labelledby="actions-title">
                        <h3 id="actions-title" class="card-title">Acciones Rápidas</h3>
                        <div class="actions-vertical-group">
                            <button class="btn btn-primary full-width-btn">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>
                                Reportar Incapacidad Médica
                            </button>
                            <a href="/dlgc_rrhh/templates/solicitud_permiso.php" class="btn btn-outline full-width-btn">
                                Solicitación de Vacaciones o Permiso
                            </a>
                        </div>
                        <div class="info-alert-box" role="note">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                            <p>Recuerda que tienes hasta 48 horas hábiles después de emitida tu incapacidad para radicar el certificado médico en el sistema.</p>
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