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
         WHERE id_jefe = :id_jefe
           AND fec_delete IS NULL'
    );
    $sentenciaSubordinados->execute([':id_jefe' => $_SESSION['id_usuario'] ?? null]);
    $tieneSubordinados = ((int) ($sentenciaSubordinados->fetch(PDO::FETCH_ASSOC)['total'] ?? 0)) > 0;
}
$mostrarJefe = $esJefe && $tieneSubordinados;

$esRrhh = has_module_access(27);

// Este módulo solo existe para quien es jefe de al menos un subordinado o tiene el permiso de RRHH.
if (!$mostrarJefe && !$esRrhh) {
    header('Location: /dlgc_rrhh/templates/access_denied.php', true, 302);
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
    <link rel="stylesheet" href="/dlgc_rrhh/assets/css/firstpage.css">
    <link rel="stylesheet" href="/dlgc_rrhh/assets/css/sst.css">
    <link rel="stylesheet" href="/dlgc_rrhh/assets/css/solicitud_permiso.css">
    <link rel="icon" type="image/png" sizes="32x32" href="/dlgc_rrhh/assets/img/favicon.ico">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body
    data-es-jefe="<?php echo $mostrarJefe ? '1' : '0'; ?>"
    data-es-rrhh="<?php echo $esRrhh ? '1' : '0'; ?>"
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

            <?php $activeItem = 'bandeja_permisos'; include __DIR__ . '/partials/sidebar_nav.php'; ?>
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
                    <a href="../app/logout.php" class="icon-btn" aria-label="Cerrar sesión">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                    </a>
                    <button id="theme-toggle" class="icon-btn" aria-label="Cambiar modo oscuro/claro">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path></svg>
                    </button>
                </div>
            </header>

            <div class="perm-page fade-in-up">

                <?php if ($mostrarJefe && $esRrhh): ?>
                <div class="sst-tabs" role="tablist">
                    <button class="sst-tab active" data-tab="jefe" role="tab" aria-selected="true">Bandeja de Jefe</button>
                    <button class="sst-tab" data-tab="rrhh" role="tab" aria-selected="false">Bandeja de RRHH</button>
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
                                <p class="perm-field-hint"><strong>Para Nómina</strong> — solo permisos y festivos que generan un descuento real.</p>
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

    <script src="/dlgc_rrhh/assets/js/theme.js"></script>
    <script src="/dlgc_rrhh/assets/js/firstpage.js"></script>
    <script src="/dlgc_rrhh/assets/js/bandeja_permisos.js"></script>
</body>
</html>
