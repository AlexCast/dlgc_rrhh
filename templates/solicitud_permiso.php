<?php
declare(strict_types=1);

require_once '../app/auth_guard.php';
require_once '../app/conexion.php';
require_module_access(3);

$puedeCrear = has_module_permission(3, 'crear');
$esJefe     = has_module_permission(3, 'actualizar');
$esRrhh     = has_module_access(27);

$tiposPermiso = [];
if ($conexion) {
    $sentenciaTipos = $conexion->query(
        'SELECT id_tipo_permiso, nombre_tipo, requiere_evidencia
         FROM t_tipos_permisos
         WHERE fec_delete IS NULL
         ORDER BY nombre_tipo'
    );
    $tiposPermiso = $sentenciaTipos->fetchAll(PDO::FETCH_ASSOC);
}

$csrfToken = htmlspecialchars(csrf_get_token(), ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitar Permiso o Vacaciones | DLGC</title>
    <link rel="stylesheet" href="/dlgc_rrhh/assets/css/firstpage.css">
    <link rel="stylesheet" href="/dlgc_rrhh/assets/css/sst.css">
    <link rel="stylesheet" href="/dlgc_rrhh/assets/css/solicitud_permiso.css">
    <link rel="icon" type="image/png" sizes="32x32" href="/dlgc_rrhh/assets/img/favicon.ico">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body
    data-puede-crear="<?php echo $puedeCrear ? '1' : '0'; ?>"
    data-es-jefe="<?php echo $esJefe ? '1' : '0'; ?>"
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

            <?php $activeItem = 'solicitudes'; include __DIR__ . '/partials/sidebar_nav.php'; ?>
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
                    <span class="profile-role">Área por definir</span> <!-- Puedes dejar 'Operaciones' si prefieres que se vea lleno por ahora -->
                </div>
            </a>
        </aside>

        <main class="main-content">
            
            <header class="top-bar">
                <div class="top-bar-left">
                    <button id="open-sidebar" class="icon-btn mobile-only" aria-label="Abrir menú de navegación">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
                    </button>
                    <h1>Solicitar Permiso o Vacaciones</h1>
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

            <div class="form-container fade-in-up">

                <div class="sst-tabs" role="tablist">
                    <button class="sst-tab active" data-tab="nueva" role="tab" aria-selected="true">Nueva Solicitud</button>
                    <button class="sst-tab" data-tab="mias" role="tab" aria-selected="false">Mis Solicitudes</button>
                    <?php if ($esJefe): ?>
                    <button class="sst-tab" data-tab="jefe" role="tab" aria-selected="false">Bandeja de Jefe</button>
                    <?php endif; ?>
                    <?php if ($esRrhh): ?>
                    <button class="sst-tab" data-tab="rrhh" role="tab" aria-selected="false">Bandeja de RRHH</button>
                    <?php endif; ?>
                </div>

                <!-- Pestaña: Nueva Solicitud -->
                <section id="tab-nueva" class="sst-tab-panel active">
                <section class="form-card">
                    <form id="permiso-form" class="permission-form" enctype="multipart/form-data">
                        <?php echo csrf_input(); ?>

                        <!-- Información del Empleado -->
                        <fieldset class="form-section">
                            <legend>Información del Empleado</legend>
                            
                            <div class="form-group">
                                <label for="fecha_solicitud">Fecha de Solicitud:</label>
                                <input type="date" id="fecha_solicitud" name="fecha_solicitud" value="<?php echo date('Y-m-d'); ?>" readonly class="auto-fill">
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="nombre_empleado">Nombre Completo:</label>
                                    <input type="text" id="nombre_empleado" name="nombre_empleado" value="<?php 
                                        // Concatenamos validando que si no hay segundo nombre/apellido no queden espacios dobles
                                        $nombres = trim(($_SESSION['primer_nombre'] ?? '') . ' ' . ($_SESSION['segundo_nombre'] ?? ''));
                                        $apellidos = trim(($_SESSION['primer_apellido'] ?? '') . ' ' . ($_SESSION['segundo_apellido'] ?? ''));
                                        echo htmlspecialchars(trim($nombres . ' ' . $apellidos)); 
                                    ?>" readonly class="auto-fill">
                                </div>
                                <div class="form-group">
                                    <label for="documento_empleado">Número de Identificación:</label>
                                    <input type="text" id="documento_empleado" name="documento_empleado" value="<?php echo htmlspecialchars($_SESSION['id_usuario'] ?? ''); ?>" readonly class="auto-fill">
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="cargo_empleado">Cargo:</label>
                                <input type="text" id="cargo_empleado" name="cargo_empleado" value="Pendiente por asignar" readonly class="auto-fill">
                            </div>

                        </fieldset>

                        <!-- Tipo de Solicitud -->
                        <fieldset class="form-section">
                            <legend>Tipo de Solicitud</legend>
                            
                            <div class="radio-group">
                                <div class="radio-item">
                                    <input type="radio" id="tipo_dias" name="tipo_solicitud" value="dias" required>
                                    <label for="tipo_dias">Permiso por Días</label>
                                </div>
                                <div class="radio-item">
                                    <input type="radio" id="tipo_horas" name="tipo_solicitud" value="horas" required>
                                    <label for="tipo_horas">Permiso por Horas</label>
                                </div>
                            </div>

                        </fieldset>

                        <!-- Selector de Fechas o Horas -->
                        <fieldset class="form-section" id="dates-section" style="display: none;">
                            <legend>Seleccionar Fechas</legend>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="fecha_inicio_display">Fecha Inicial:</label>
                                    <div class="date-picker" data-target="fecha_inicio">
                                        <input type="text" id="fecha_inicio_display" class="date-picker-input" placeholder="dd/mm/aaaa" readonly autocomplete="off">
                                        <svg class="date-picker-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                                        <input type="hidden" id="fecha_inicio" name="fecha_inicio">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="fecha_fin_display">Fecha Final:</label>
                                    <div class="date-picker" data-target="fecha_fin">
                                        <input type="text" id="fecha_fin_display" class="date-picker-input" placeholder="dd/mm/aaaa" readonly autocomplete="off">
                                        <svg class="date-picker-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                                        <input type="hidden" id="fecha_fin" name="fecha_fin">
                                    </div>
                                </div>
                            </div>

                            <div class="date-picker-legend">
                                <span class="date-picker-legend-item"><i class="legend-dot legend-dot--festivo"></i> Festivo nacional (Colombia)</span>
                                <span class="date-picker-legend-item"><i class="legend-dot legend-dot--festivo-empresa"></i> Festivo de empresa</span>
                                <span class="date-picker-legend-item"><i class="legend-dot legend-dot--domingo"></i> Domingo</span>
                            </div>

                            <div class="form-group">
                                <label for="total_dias">Total de Días Hábiles:</label>
                                <input type="number" id="total_dias" name="total_dias" readonly min="0">
                                <p class="form-hint">No cuenta domingos ni festivos en Colombia.</p>
                            </div>

                        </fieldset>

                        <fieldset class="form-section" id="hours-section" style="display: none;">
                            <legend>Seleccionar Horas</legend>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="hora_inicio">Hora de Inicio:</label>
                                    <input type="time" id="hora_inicio" name="hora_inicio">
                                </div>
                                <div class="form-group">
                                    <label for="hora_fin">Hora de Fin:</label>
                                    <input type="time" id="hora_fin" name="hora_fin">
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="total_horas">Total de Horas:</label>
                                <input type="number" id="total_horas" name="total_horas" readonly min="0" step="0.5">
                            </div>

                        </fieldset>

                        <!-- Motivo del Permiso (única opción, definida por RRHH en t_tipos_permisos) -->
                        <fieldset class="form-section">
                            <legend>Motivo del Permiso a Tramitar</legend>

                            <div class="form-group">
                                <label for="motivo">Selecciona un motivo:</label>
                                <select id="motivo" name="motivo" required>
                                    <option value="">Selecciona un motivo</option>
                                    <?php foreach ($tiposPermiso as $tipo): ?>
                                    <option value="<?php echo (int) $tipo['id_tipo_permiso']; ?>"
                                            data-requiere-evidencia="<?php echo $tipo['requiere_evidencia'] === 't' || $tipo['requiere_evidencia'] === true ? '1' : '0'; ?>">
                                        <?php echo htmlspecialchars($tipo['nombre_tipo']); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group" id="detalle-motivo-group" style="display: none;">
                                <label for="detalle_motivo">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: text-bottom; margin-right: 0.4rem;">
                                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                    </svg>
                                    Detalle (opcional)
                                </label>
                                <input type="text" id="detalle_motivo" name="detalle_motivo" placeholder="Describe con más detalle el motivo de tu solicitud">
                            </div>

                            <div class="form-group" id="evidencia-section" style="display: none;">
                                <label for="evidencias">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: text-bottom; margin-right: 0.4rem;">
                                        <path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"></path>
                                    </svg>
                                    Evidencia (JPG, PNG o PDF, máx. 10MB c/u)
                                </label>
                                <input type="file" id="evidencias" name="evidencias[]" accept=".jpg,.jpeg,.png,.pdf" multiple>
                                <p class="form-hint">El motivo seleccionado requiere evidencia.</p>
                            </div>

                        </fieldset>

                        <p id="permiso-form-feedback" class="sst-feedback"></p>

                        <!-- Firma y Botones -->
                        <div class="form-section form-actions">
                            <?php if ($puedeCrear): ?>
                            <button type="submit" class="btn btn-primary">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                Enviar Solicitud
                            </button>
                            <?php else: ?>
                            <button type="button" class="btn btn-primary" disabled title="No tienes permiso para crear solicitudes">
                                Enviar Solicitud
                            </button>
                            <?php endif; ?>
                            <a href="/dlgc_rrhh/templates/firstpage.php" class="btn btn-outline">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                                Cancelar
                            </a>
                        </div>

                    </form>
                </section>
                </section>

                <!-- Pestaña: Mis Solicitudes -->
                <section id="tab-mias" class="sst-tab-panel" hidden>
                    <div class="sst-section-header">
                        <h2>Mis Solicitudes</h2>
                        <p class="sst-section-desc">Historial de tus solicitudes con el estado de cada nivel de aprobación.</p>
                    </div>
                    <div id="lista-mias" class="permiso-lista"></div>
                </section>

                <?php if ($esJefe): ?>
                <!-- Pestaña: Bandeja de Jefe -->
                <section id="tab-jefe" class="sst-tab-panel" hidden>
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
                <!-- Pestaña: Bandeja de RRHH -->
                <section id="tab-rrhh" class="sst-tab-panel" hidden>
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
                <?php endif; ?>

                <?php if ($esRrhh): ?>
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
                                <div class="form-group">
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
                                <div class="form-group">
                                    <label for="reporte-anio">Año</label>
                                    <select id="reporte-anio">
                                        <?php for ($a = (int) date('Y') - 1; $a <= (int) date('Y') + 1; $a++): ?>
                                        <option value="<?php echo $a; ?>" <?php echo $a === (int) date('Y') ? 'selected' : ''; ?>><?php echo $a; ?></option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="reporte-grupo">
                                <p class="form-hint"><strong>Para Nómina</strong> — solo permisos y festivos que generan un descuento real.</p>
                                <div class="form-actions">
                                    <a class="btn btn-outline reporte-link" data-vista="nomina" data-formato="excel" href="#">Descargar Nómina (Excel)</a>
                                    <a class="btn btn-outline reporte-link" data-vista="nomina" data-formato="pdf" href="#">Descargar Nómina (PDF)</a>
                                </div>
                            </div>

                            <div class="reporte-grupo">
                                <p class="form-hint"><strong>Para RRHH</strong> — informe completo de todos los permisos aprobados, se descuenten o no.</p>
                                <div class="form-actions">
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
    <script src="/dlgc_rrhh/assets/js/solicitud_permiso.js"></script>
</body>
</html>
