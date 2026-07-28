<?php require_once '../app/auth_guard.php';
require_module_access(3);?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitar Permiso o Vacaciones | DLGC</title>
    <link rel="stylesheet" href="/dlgc_rrhh/assets/css/firstpage.css">
    <link rel="stylesheet" href="/dlgc_rrhh/assets/css/solicitud_permiso.css">
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
                
                <section class="form-card">
                    <form id="permiso-form" class="permission-form">
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
                                    <label for="fecha_inicio">Fecha Inicial:</label>
                                    <input type="date" id="fecha_inicio" name="fecha_inicio">
                                </div>
                                <div class="form-group">
                                    <label for="fecha_fin">Fecha Final:</label>
                                    <input type="date" id="fecha_fin" name="fecha_fin">
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="total_dias">Total de Días:</label>
                                <input type="number" id="total_dias" name="total_dias" readonly min="0">
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

                        <!-- Motivo del Permiso -->
                        <fieldset class="form-section">
                            <legend>Motivo del Permiso a Tramitar</legend>
                            
                            <div class="checkbox-group">
                                <div class="checkbox-item">
                                    <input type="radio" id="motivo_estudio" name="motivo" value="ESTUDIO" required>
                                    <label for="motivo_estudio">Estudio</label>
                                </div>
                                <div class="checkbox-item">
                                    <input type="radio" id="motivo_calamidad" name="motivo" value="CALAMIDAD" required>
                                    <label for="motivo_calamidad">Calamidad Doméstica</label>
                                </div>
                                <div class="checkbox-item">
                                    <input type="radio" id="motivo_otro" name="motivo" value="OTRO" required>
                                    <label for="motivo_otro">Otro Motivo</label>
                                </div>
                                <div class="checkbox-item">
                                    <input type="radio" id="motivo_consulta" name="motivo" value="CONSULTA_MEDICA" required>
                                    <label for="motivo_consulta">Consulta Médica</label>
                                </div>
                                <div class="checkbox-item">
                                    <input type="radio" id="motivo_fuerza_mayor" name="motivo" value="FUERZA_MAYOR" required>
                                    <label for="motivo_fuerza_mayor">Fuerza Mayor</label>
                                </div>
                            </div>

                            <div class="form-group" id="detalle-motivo-section" style="display: none;">
                                <label for="detalle_motivo">Especificar Motivo:</label>
                                <textarea id="detalle_motivo" name="detalle_motivo" rows="3" placeholder="Describe brevemente el motivo de tu solicitud..."></textarea>
                            </div>

                        </fieldset>

                        <!-- Permiso Remunerado -->
                        <fieldset class="form-section">
                            <legend>Permiso Remunerado</legend>
                            <p class="form-hint">Selecciona una opción</p>
                            
                            <div class="radio-group">
                                <div class="radio-item">
                                    <input type="radio" id="permiso_dinero" name="permiso_remunerado" value="DINERO" required>
                                    <label for="permiso_dinero">Dinero</label>
                                </div>
                                <div class="radio-item">
                                    <input type="radio" id="permiso_vacaciones" name="permiso_remunerado" value="VACACIONES" required>
                                    <label for="permiso_vacaciones">Vacaciones</label>
                                </div>
                                <div class="radio-item">
                                    <input type="radio" id="permiso_no" name="permiso_remunerado" value="NO" required>
                                    <label for="permiso_no">No</label>
                                </div>
                            </div>

                        </fieldset>

                        <!-- Firma y Botones -->
                        <div class="form-section form-actions">
                            <?php if (has_module_permission(3, 'crear')): ?>
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

            </div>
        </main>
    </div>

    <script src="/dlgc_rrhh/assets/js/theme.js"></script>
    <script src="/dlgc_rrhh/assets/js/firstpage.js"></script>
    <script src="/dlgc_rrhh/assets/js/solicitud_permiso.js"></script>
</body>
</html>
