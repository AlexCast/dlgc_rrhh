<?php
declare(strict_types=1);

require_once '../app/auth_guard.php';
require_once '../app/conexion.php';
require_module_access(29);

$puedeCrear = has_module_permission(29, 'crear');
$puedeEliminar = has_module_permission(29, 'eliminar');
$puedeRestaurar = has_module_permission(29, 'restaurar');

$csrfToken = htmlspecialchars(csrf_get_token(), ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Vacaciones | DLGC</title>
    <link rel="stylesheet" href="/dlgc_rrhh/assets/css/firstpage.css">
    <link rel="stylesheet" href="/dlgc_rrhh/assets/css/vacaciones.css">
    <link rel="icon" type="image/png" sizes="32x32" href="/dlgc_rrhh/assets/img/favicon.ico">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body
    data-puede-crear="<?php echo $puedeCrear ? '1' : '0'; ?>"
    data-puede-eliminar="<?php echo $puedeEliminar ? '1' : '0'; ?>"
    data-puede-restaurar="<?php echo $puedeRestaurar ? '1' : '0'; ?>"
    data-csrf="<?php echo $csrfToken; ?>"
>

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

            <?php $activeItem = 'vacaciones'; include __DIR__ . '/partials/sidebar_nav.php'; ?>
            <a href="/dlgc_rrhh/templates/ficha_tecnica.php" class="sidebar-profile" aria-label="Ver mi perfil">
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
                    <span class="profile-name"><?php echo htmlspecialchars($_SESSION['nombre_completo'] ?? 'Usuario'); ?></span>
                    <span class="profile-role">RRHH</span>
                </div>
            </a>
        </aside>

        <main class="main-content">

            <header class="top-bar">
                <div class="top-bar-left">
                    <button id="open-sidebar" class="icon-btn mobile-only" aria-label="Abrir menú de navegación">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
                    </button>
                    <h1>Gestión de Vacaciones</h1>
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

            <div class="vac-page fade-in-up">

                <section class="vac-hero">
                    <div class="vac-hero-text">
                        <h2>Saldo de Vacaciones</h2>
                        <p>Consulta el saldo del ciclo aniversario vigente de un empleado y registra ajustes manuales (saldo inicial de migración, correcciones puntuales). <strong>No acumulable:</strong> lo no disfrutado se pierde en cada aniversario.</p>
                    </div>
                    <div class="vac-toolbar">
                        <a class="vac-btn vac-btn-outline" href="/dlgc_rrhh/app/vacaciones_reporte.php?formato=excel">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                            Todos (Excel)
                        </a>
                        <a class="vac-btn vac-btn-outline" href="/dlgc_rrhh/app/vacaciones_reporte.php?formato=pdf">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                            Todos (PDF)
                        </a>
                    </div>
                </section>

                <section class="vac-card">
                    <label class="vac-label" for="vacaciones-empleado">Empleado</label>
                    <div class="vac-empleado-row">
                        <select id="vacaciones-empleado" class="vac-select"><option value="">Selecciona un empleado</option></select>
                        <div class="vac-toolbar">
                            <a id="vacaciones-exportar-empleado-excel" class="vac-btn vac-btn-outline" href="#" hidden>Este empleado (Excel)</a>
                            <a id="vacaciones-exportar-empleado-pdf" class="vac-btn vac-btn-outline" href="#" hidden>Este empleado (PDF)</a>
                        </div>
                    </div>

                    <div id="vacaciones-saldo-resumen" class="vac-resumen"></div>
                </section>

                <div class="vac-grid">
                    <?php if ($puedeCrear): ?>
                    <section class="vac-card">
                        <h3 class="vac-card-title">Registrar ajuste manual</h3>
                        <form id="vacaciones-ajuste-form" class="vac-form">
                            <?php echo csrf_input(); ?>
                            <div class="vac-form-row">
                                <div class="vac-field">
                                    <label for="vacaciones-dias-ajuste">Días a restar</label>
                                    <input type="number" id="vacaciones-dias-ajuste" name="dias_ajuste" step="0.5" min="0.5" required>
                                    <small class="vac-field-hint">Indica cuántos días ya disfrutados quieres restar del saldo (por ley el ciclo causa exactamente 15 días).</small>
                                </div>
                                <div class="vac-field">
                                    <label for="vacaciones-fecha-ajuste">Fecha del ajuste</label>
                                    <input type="date" id="vacaciones-fecha-ajuste" name="fecha_ajuste" value="<?php echo date('Y-m-d'); ?>" disabled>
                                    <small id="vacaciones-fecha-ajuste-ayuda" class="vac-field-hint">Selecciona un empleado para habilitar la fecha.</small>
                                </div>
                            </div>
                            <div class="vac-field">
                                <label for="vacaciones-motivo">Motivo (obligatorio)</label>
                                <input type="text" id="vacaciones-motivo" name="motivo" minlength="5" required placeholder="Ej: Saldo inicial migración, tomó 5 días antes de este sistema">
                            </div>
                            <p id="vacaciones-ajuste-feedback" class="vac-feedback"></p>
                            <button type="submit" class="vac-btn vac-btn-primary">Guardar ajuste</button>
                        </form>
                    </section>
                    <?php endif; ?>

                    <section class="vac-card">
                        <h3 class="vac-card-title">Historial de ajustes</h3>
                        <div id="vacaciones-ajustes-lista" class="vac-lista"></div>
                    </section>
                </div>
            </div>
        </main>
    </div>

    <script src="/dlgc_rrhh/assets/js/theme.js"></script>
    <script src="/dlgc_rrhh/assets/js/firstpage.js"></script>
    <script src="/dlgc_rrhh/assets/js/vacaciones_saldo.js"></script>
</body>
</html>

