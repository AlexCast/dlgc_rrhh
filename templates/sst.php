<?php
declare(strict_types=1);

require_once '../app/auth_guard.php';
require_once '../app/conexion.php';

require_module_access(25);

date_default_timezone_set('America/Bogota');

$idUsuarioSesion = $_SESSION['id_usuario'] ?? null;
$nombreCompleto = trim(
    ($_SESSION['primer_nombre'] ?? '') . ' ' .
    ($_SESSION['segundo_nombre'] ?? '') . ' ' .
    ($_SESSION['primer_apellido'] ?? '') . ' ' .
    ($_SESSION['segundo_apellido'] ?? '')
);

// Cargar miembros de comités
$miembros = [];
if ($conexion) {
    $sentencia = $conexion->query("SELECT * FROM fun_listar_sst_comite_miembros(NULL);");
    $miembros = $sentencia->fetchAll(PDO::FETCH_OBJ);
}

$comiteCopasst = array_filter($miembros, fn($m) => $m->tipo_comite === 'COPASST');
$comiteConvivencia = array_filter($miembros, fn($m) => $m->tipo_comite === 'COMITE_CONVIVENCIA');

// Cargar quejas del usuario
$quejas = [];
if ($conexion && is_string($idUsuarioSesion) && trim($idUsuarioSesion) !== '') {
    $sentenciaQuejas = $conexion->prepare("SELECT * FROM fun_listar_sst_quejas_usuario(:id_usuario);");
    $sentenciaQuejas->execute([':id_usuario' => $idUsuarioSesion]);
    $quejas = $sentenciaQuejas->fetchAll(PDO::FETCH_OBJ);
}

$csrfToken = htmlspecialchars(csrf_get_token(), ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seguridad y Salud en el Trabajo | DLGC</title>
    <link rel="stylesheet" href="/dlgc_rrhh/assets/css/firstpage.css">
    <link rel="stylesheet" href="/dlgc_rrhh/assets/css/sst.css">
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

            <?php $activeItem = 'sst'; include __DIR__ . '/partials/sidebar_nav.php'; ?>
            <a href="/dlgc_rrhh/templates/ficha_tecnica.php" class="sidebar-profile" aria-label="Ver mi perfil">
                <div class="profile-avatar" aria-hidden="true">
                    <?php if (!empty($_SESSION['foto_perfil'])): ?>
                        <img src="/dlgc_rrhh/uploads/perfiles/<?php echo htmlspecialchars($_SESSION['foto_perfil']); ?>" alt="Foto de perfil" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                    <?php else: ?>
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                    <?php endif; ?>
                </div>
                <div class="profile-info">
                    <span class="profile-name"><?php echo htmlspecialchars($_SESSION['nombre_completo'] ?? 'Usuario'); ?></span>
                    <span class="profile-role">SST</span>
                </div>
            </a>
        </aside>

        <main class="main-content">
            <header class="top-bar">
                <div class="top-bar-left">
                    <button id="open-sidebar" class="icon-btn mobile-only" aria-label="Abrir menú de navegación">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
                    </button>
                    <h1>Seguridad y Salud en el Trabajo</h1>
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
                <div class="sst-tabs" role="tablist" aria-label="Secciones SST">
                    <button type="button" class="sst-tab active" role="tab" aria-selected="true" aria-controls="tab-copasst" data-tab="copasst">COPASST</button>
                    <button type="button" class="sst-tab" role="tab" aria-selected="false" aria-controls="tab-convivencia" data-tab="convivencia">Comité de Convivencia</button>
                    <button type="button" class="sst-tab" role="tab" aria-selected="false" aria-controls="tab-buzon" data-tab="buzon">Buzón de Quejas y Sugerencias</button>
                </div>

                <section id="tab-copasst" class="sst-tab-panel active" role="tabpanel" tabindex="0">
                    <div class="sst-section-header">
                        <h2 class="card-title">Directorio COPASST</h2>
                        <p class="sst-section-desc">Comité Paritario de Seguridad y Salud en el Trabajo.</p>
                    </div>
                    <?php if (empty($comiteCopasst)): ?>
                        <div class="sst-empty">No hay miembros registrados en el COPASST.</div>
                    <?php else: ?>
                        <div class="sst-grid">
                            <?php foreach ($comiteCopasst as $miembro): ?>
                                <article class="sst-card">
                                    <div class="sst-card-icon" aria-hidden="true">
                                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                                    </div>
                                    <div class="sst-card-body">
                                        <h3><?php echo htmlspecialchars($miembro->nombre_completo); ?></h3>
                                        <span class="sst-card-role"><?php echo htmlspecialchars($miembro->cargo); ?></span>
                                        <?php if (!empty($miembro->correo)): ?>
                                            <a href="mailto:<?php echo htmlspecialchars($miembro->correo); ?>" class="sst-card-contact"><?php echo htmlspecialchars($miembro->correo); ?></a>
                                        <?php endif; ?>
                                        <?php if (!empty($miembro->telefono)): ?>
                                            <span class="sst-card-contact"><?php echo htmlspecialchars($miembro->telefono); ?></span>
                                        <?php endif; ?>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </section>

                <section id="tab-convivencia" class="sst-tab-panel" role="tabpanel" tabindex="0" hidden>
                    <div class="sst-section-header">
                        <h2 class="card-title">Comité de Convivencia Laboral</h2>
                        <p class="sst-section-desc">Canal de apoyo para casos de acoso laboral y conflictos.</p>
                    </div>
                    <?php if (empty($comiteConvivencia)): ?>
                        <div class="sst-empty">No hay miembros registrados en el Comité de Convivencia.</div>
                    <?php else: ?>
                        <div class="sst-grid">
                            <?php foreach ($comiteConvivencia as $miembro): ?>
                                <article class="sst-card">
                                    <div class="sst-card-icon" aria-hidden="true">
                                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                                    </div>
                                    <div class="sst-card-body">
                                        <h3><?php echo htmlspecialchars($miembro->nombre_completo); ?></h3>
                                        <span class="sst-card-role"><?php echo htmlspecialchars($miembro->cargo); ?></span>
                                        <?php if (!empty($miembro->correo)): ?>
                                            <a href="mailto:<?php echo htmlspecialchars($miembro->correo); ?>" class="sst-card-contact"><?php echo htmlspecialchars($miembro->correo); ?></a>
                                        <?php endif; ?>
                                        <?php if (!empty($miembro->telefono)): ?>
                                            <span class="sst-card-contact"><?php echo htmlspecialchars($miembro->telefono); ?></span>
                                        <?php endif; ?>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </section>

                <section id="tab-buzon" class="sst-tab-panel" role="tabpanel" tabindex="0" hidden>
                    <div class="content-split-grid">
                        <section class="data-card">
                            <h2 class="card-title">Nueva Queja / Sugerencia</h2>
                            <form id="sst-queja-form" class="sst-form" data-csrf-token="<?php echo $csrfToken; ?>">
                                <?php echo csrf_input(); ?>
                                <input type="hidden" id="id_queja" name="id_queja" value="">

                                <div class="form-group">
                                    <label for="tipo_peticion">Tipo de petición</label>
                                    <select id="tipo_peticion" name="tipo_peticion" required>
                                        <option value="">Selecciona...</option>
                                        <option value="QUEJA">Queja</option>
                                        <option value="SUGERENCIA">Sugerencia</option>
                                        <option value="RECLAMO">Reclamo</option>
                                        <option value="DENUNCIA">Denuncia</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="asunto">Asunto</label>
                                    <input type="text" id="asunto" name="asunto" maxlength="150" required placeholder="Resumen breve">
                                </div>

                                <div class="form-group">
                                    <label for="descripcion">Descripción</label>
                                    <textarea id="descripcion" name="descripcion" rows="5" required placeholder="Describe los hechos con el mayor detalle posible."></textarea>
                                </div>

                                <div class="sst-form-meta">
                                    <span>Identificación: <strong><?php echo htmlspecialchars($_SESSION['id_usuario'] ?? ''); ?></strong></span>
                                    <span>Nombre: <strong><?php echo htmlspecialchars($nombreCompleto); ?></strong></span>
                                </div>

                                <div class="form-actions">
                                    <button type="submit" id="btn-enviar-queja" class="btn btn-primary">Enviar</button>
                                    <button type="button" id="btn-cancelar-edicion" class="btn btn-outline" style="display:none;">Cancelar edición</button>
                                </div>
                            </form>
                            <div id="sst-form-feedback" class="sst-feedback" role="status" aria-live="polite"></div>
                        </section>

                        <section class="data-card">
                            <h2 class="card-title">Mis solicitudes</h2>
                            <?php if (empty($quejas)): ?>
                                <div class="sst-empty">Aún no has enviado ninguna queja o sugerencia.</div>
                            <?php else: ?>
                                <div class="sst-quejas-list">
                                    <?php foreach ($quejas as $queja): ?>
                                        <article class="sst-queja-card" data-id="<?php echo (int) $queja->id_queja; ?>">
                                            <div class="sst-queja-header">
                                                <span class="sst-queja-type"><?php echo htmlspecialchars($queja->tipo_peticion); ?></span>
                                                <span class="sst-queja-status status-<?php echo strtolower(str_replace([' ', '_'], '-', $queja->estado)); ?>"><?php echo htmlspecialchars($queja->estado); ?></span>
                                            </div>
                                            <h3 class="sst-queja-subject"><?php echo htmlspecialchars($queja->asunto); ?></h3>
                                            <p class="sst-queja-desc"><?php echo nl2br(htmlspecialchars($queja->descripcion)); ?></p>
                                            <?php if (!empty($queja->respuesta)): ?>
                                                <div class="sst-queja-response">
                                                    <strong>Respuesta:</strong>
                                                    <p><?php echo nl2br(htmlspecialchars($queja->respuesta)); ?></p>
                                                </div>
                                            <?php endif; ?>
                                            <div class="sst-queja-footer">
                                                <span class="sst-queja-date"><?php echo date('d/m/Y H:i', strtotime($queja->fec_insert)); ?></span>
                                                <?php if ((bool) $queja->puede_editar): ?>
                                                    <button type="button" class="btn btn-sm btn-warning btn-edit-queja" data-id="<?php echo (int) $queja->id_queja; ?>">Editar</button>
                                                <?php endif; ?>
                                                <?php if (!in_array($queja->estado, ['RESUELTO', 'CANCELADO_USUARIO', 'CANCELADO_ENCARGADO'], true)): ?>
                                                    <button type="button" class="btn btn-sm btn-danger btn-cancel-queja" data-id="<?php echo (int) $queja->id_queja; ?>">Cancelar</button>
                                                <?php endif; ?>
                                            </div>
                                        </article>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </section>
                    </div>
                </section>
            </div>
        </main>
    </div>

    <script src="/dlgc_rrhh/assets/js/theme.js"></script>
    <script src="/dlgc_rrhh/assets/js/sst.js"></script>
</body>
</html>
