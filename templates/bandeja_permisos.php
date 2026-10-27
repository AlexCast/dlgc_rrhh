<?php
declare(strict_types=1);

require_once '../app/auth_guard.php';
require_once '../app/conexion.php';

$esJefe = has_module_permission(3, 'actualizar');
$tieneSubordinados = false;
if ($conexion && $esJefe) {
    $sentenciaSubordinados = $conexion->prepare(
        'SELECT COUNT(*) AS total
         FROM t_empleados
         WHERE id_jefe = ?
           AND fec_delete IS NULL'
    );
    $sentenciaSubordinados->execute([$_SESSION['id_usuario'] ?? null]);
    $tieneSubordinados = ((int) ($sentenciaSubordinados->fetch(PDO::FETCH_ASSOC)['total'] ?? 0)) > 0;
}
$mostrarJefe = $esJefe && $tieneSubordinados;

$esRrhh = has_module_access(27);

// Este módulo solo existe para quien es jefe de al menos un subordinado o tiene el permiso de RRHH.
if (!$mostrarJefe && !$esRrhh) {
    header('Location: /templates/access_denied.php', true, 302);
    exit;
}

$primeraVista = $mostrarJefe ? 'jefe' : 'rrhh';
$csrfToken = htmlspecialchars(csrf_get_token(), ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bandeja de Aprobaciones | DLGC</title>
    <link rel="stylesheet" href="/assets/css/firstpage.css?v=<?php echo filemtime(__DIR__ . '/../assets/css/firstpage.css'); ?>">
    <link rel="stylesheet" href="/assets/css/sst.css?v=<?php echo filemtime(__DIR__ . '/../assets/css/sst.css'); ?>">
    <link rel="stylesheet" href="/assets/css/solicitud_permiso.css?v=<?php echo filemtime(__DIR__ . '/../assets/css/solicitud_permiso.css'); ?>">
    <link rel="icon" type="image/png" sizes="32x32" href="/assets/img/favicon.ico">
</head>
<body
    data-es-jefe="<?php echo $mostrarJefe ? '1' : '0'; ?>"
    data-es-rrhh="<?php echo $esRrhh ? '1' : '0'; ?>"
    data-csrf="<?php echo $csrfToken; ?>"
>

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

            <?php $activeItem = 'bandeja_permisos'; include __DIR__ . '/partials/sidebar_nav.php'; ?>
            <a href="/templates/ficha_tecnica.php" class="sidebar-profile" aria-label="Ver mi perfil">
                <div class="profile-avatar" aria-hidden="true">
    <?php if (!empty($_SESSION['foto_perfil'])): ?>
        <img src="/uploads/perfiles/<?php echo htmlspecialchars($_SESSION['foto_perfil']); ?>"
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
                    <span class="profile-role">Área por definir</span>
                </div>
            </a>
        </aside>

        <main class="main-content">

            <header class="top-bar">
                <div class="top-bar-left">
                    <button id="open-sidebar" class="icon-btn mobile-only" aria-label="Abrir menú de navegación">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
                    </button>
                    <h1>Bandeja de Aprobaciones</h1>
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

            <div class="perm-page fade-in-up">

                <?php if ($mostrarJefe || $esRrhh): ?>
                <div class="tab-switch" role="tablist" aria-label="Bandejas de aprobación">
                    <?php if ($mostrarJefe): ?>
                    <button type="button" class="tab-switch-btn<?php echo $primeraVista === 'jefe' ? ' active' : ''; ?>" data-tab="jefe" role="tab" aria-selected="<?php echo $primeraVista === 'jefe' ? 'true' : 'false'; ?>">Bandeja de Jefe</button>
                    <?php endif; ?>
                    <?php if ($esRrhh): ?>
                    <button type="button" class="tab-switch-btn<?php echo $primeraVista === 'rrhh' ? ' active' : ''; ?>" data-tab="rrhh" role="tab" aria-selected="<?php echo $primeraVista === 'rrhh' ? 'true' : 'false'; ?>">Bandeja de RRHH</button>
                    <?php endif; ?>
                    <span class="tab-switch-indicator" aria-hidden="true"></span>
                </div>
                <?php endif; ?>

                <?php if ($mostrarJefe): ?>
                <!-- Bandeja de Jefe Directo -->
                <section id="tab-jefe" class="sst-tab-panel<?php echo $primeraVista === 'jefe' ? ' active' : ''; ?>" <?php echo $primeraVista === 'jefe' ? '' : 'hidden'; ?>>
                    <div class="sst-section-header">
                        <h2>Bandeja de Jefe Directo</h2>
                        <p class="sst-section-desc">Solicitudes de tus subordinados directos pendientes de tu aprobación.</p>
                        <div class="form-actions">
                            <button type="button" class="btn-accion-secundaria btn-toggle-historial" data-vista="jefe">Ver historial completo</button>
                        </div>
                    </div>
                    <div id="lista-jefe" class="permiso-lista"></div>
                </section>
                <?php endif; ?>

                <?php if ($esRrhh): ?>
                <!-- Bandeja de RRHH -->
                <section id="tab-rrhh" class="sst-tab-panel<?php echo $primeraVista === 'rrhh' ? ' active' : ''; ?>" <?php echo $primeraVista === 'rrhh' ? '' : 'hidden'; ?>>
                    <div class="sst-section-header">
                        <h2>Bandeja de RRHH</h2>
                        <p class="sst-section-desc">Todas las solicitudes del sistema, sin excepción.</p>
                        <div class="form-actions">
                            <button type="button" id="btn-exportar-informe" class="btn-accion-secundaria">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                                Exportar Informe
                            </button>
                            <button type="button" class="btn-accion-secundaria btn-toggle-historial" data-vista="rrhh">Ver historial completo</button>
                        </div>
                    </div>
                    <div id="lista-rrhh" class="permiso-lista"></div>
                </section>

                <!-- Modal: Exportar Informe (Nómina / RRHH) -->
                <div class="modal-overlay" id="modal-exportar-informe" hidden>
                    <div class="modal-box">
                        <div class="modal-box-header">
                            <h3>Exportar Informe</h3>
                            <button type="button" id="cerrar-modal-exportar" class="icon-btn" aria-label="Cerrar">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                            </button>
                        </div>

                        <div class="modal-box-body">
                            <div class="reporte-filtros">
                                <div class="perm-field">
                                    <label for="reporte-mes">Mes</label>
                                    <select id="reporte-mes">
                                        <?php
                                        $nombresMes = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
                                        foreach ($nombresMes as $indice => $nombreMes):
                                            $numeroMes = $indice + 1;
                                        ?>
                                        <option value="<?php echo $numeroMes; ?>" <?php echo $numeroMes === (int) date('n') ? 'selected' : ''; ?>><?php echo $nombreMes; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="perm-field">
                                    <label for="reporte-anio">Año</label>
                                    <select id="reporte-anio">
                                        <?php for ($a = (int) date('Y') - 1; $a <= (int) date('Y') + 1; $a++): ?>
                                        <option value="<?php echo $a; ?>" <?php echo $a === (int) date('Y') ? 'selected' : ''; ?>><?php echo $a; ?></option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="reporte-grupo">
                                <p class="perm-field-hint"><strong>Para Nómina</strong> — permisos no remunerados (descuento de sueldo) y días cargados a vacaciones.</p>
                                <div class="perm-actions">
                                    <a class="btn btn-outline reporte-link" data-vista="nomina" data-formato="excel" href="#">Descargar Nómina (Excel)</a>
                                    <a class="btn btn-outline reporte-link" data-vista="nomina" data-formato="pdf" href="#">Descargar Nómina (PDF)</a>
                                </div>
                            </div>

                            <div class="reporte-grupo">
                                <p class="perm-field-hint"><strong>Para RRHH</strong> — informe completo de todos los permisos aprobados, se descuenten o no.</p>
                                <div class="perm-actions">
                                    <a class="btn btn-outline reporte-link" data-vista="rrhh" data-formato="excel" href="#">Descargar Informe RRHH (Excel)</a>
                                    <a class="btn btn-outline reporte-link" data-vista="rrhh" data-formato="pdf" href="#">Descargar Informe RRHH (PDF)</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

            </div>
        </main>
    </div>

    <script src="/assets/js/theme.js?v=<?php echo filemtime(__DIR__ . '/../assets/js/theme.js'); ?>"></script>
    <script src="/assets/js/firstpage.js?v=<?php echo filemtime(__DIR__ . '/../assets/js/firstpage.js'); ?>"></script>
    <script src="/assets/js/tab_switch.js?v=<?php echo filemtime(__DIR__ . '/../assets/js/tab_switch.js'); ?>"></script>
    <script src="/assets/js/bandeja_permisos.js?v=<?php echo filemtime(__DIR__ . '/../assets/js/bandeja_permisos.js'); ?>"></script>
</body>
</html>
