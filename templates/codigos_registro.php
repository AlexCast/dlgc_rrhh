<?php
require_once '../app/auth_guard.php';
require_once '../app/conexion.php';

require_module_access(23);

$flashMensaje = $_SESSION['flash_mensaje'] ?? null;
$flashTipo = $_SESSION['flash_tipo'] ?? 'success';
unset($_SESSION['flash_mensaje'], $_SESSION['flash_tipo']);

$stmt = $conexion->query(
    "SELECT id_codigo, codigo, tipo, fecha_expiracion, usado, fec_delete
     FROM t_codigos_registro
     ORDER BY fec_insert DESC, id_codigo DESC"
);
$codigos = $stmt->fetchAll(PDO::FETCH_OBJ);

$temporal = array_values(array_filter($codigos, function ($c) {
    return $c->tipo === 'TEMPORAL'
        && $c->fec_delete === null
        && ($c->fecha_expiracion === null || $c->fecha_expiracion >= date('Y-m-d H:i:s'));
}))[0] ?? null;

$unico = array_values(array_filter($codigos, function ($c) {
    return $c->tipo === 'UNICO_USO' && $c->usado === false && $c->fec_delete === null;
}))[0] ?? null;

function formatearCodigo(?string $codigo): string
{
    return $codigo !== null && trim($codigo) !== ''
        ? htmlspecialchars($codigo, ENT_QUOTES, 'UTF-8')
        : '';
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

$puedeGenerar = has_module_permission(23, 'crear');
$puedeCancelar = has_module_permission(23, 'eliminar');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Códigos de Registro | DLGC</title>
    <link rel="stylesheet" href="/dlgc_rrhh/assets/css/firstpage.css">
    <link rel="stylesheet" href="/dlgc_rrhh/assets/css/codigos_registro.css">
    <link rel="icon" type="image/png" sizes="32x32" href="/dlgc_rrhh/assets/img/favicon.ico">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
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

            <?php $activeItem = 'codigos_registro'; include __DIR__ . '/partials/sidebar_admin.php'; ?>
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
                    <h1>Códigos de Acceso al Registro</h1>
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

            <div class="dashboard-body fade-in-up codes-page-body">

                <?php if ($flashMensaje !== null): ?>
                    <div class="codes-alert <?php echo $flashTipo === 'error' ? 'error' : 'success'; ?>" role="status">
                        <?php echo htmlspecialchars($flashMensaje, ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                <?php endif; ?>

                <section class="codes-welcome" aria-labelledby="codes-title">
                    <div class="welcome-text">
                        <h2 id="codes-title">Control de registros de cuentas</h2>
                        <p>Genera o cancela el código que permitirá a nuevas personas activar su cuenta en el portal.</p>
                    </div>
                </section>

                <div class="codes-card">
                    <div class="codes-tabs" role="tablist" aria-label="Tipo de código de registro">
                        <button class="codes-tab active" type="button" role="tab" aria-selected="true" aria-controls="panel-unico" id="tab-unico">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                            Único de un solo uso
                        </button>
                        <button class="codes-tab" type="button" role="tab" aria-selected="false" aria-controls="panel-temporal" id="tab-temporal">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                            Temporal
                        </button>
                    </div>

                    <div class="codes-panels">
                        <div id="panel-unico" class="codes-panel active" role="tabpanel" aria-labelledby="tab-unico">
                            <div class="codes-panel-intro">
                                <h3 class="codes-panel-title">Código Único</h3>
                                <p class="codes-panel-desc">Una persona lo usa para registrarse y el sistema genera automáticamente el siguiente.</p>
                            </div>

                            <div class="codes-display <?php echo $unico ? 'active' : 'empty'; ?>" data-copy-target="codigo-unico">
                                <span class="codes-display-value" id="codigo-unico">
                                    <?php echo $unico ? formatearCodigo($unico->codigo) : 'Sin código activo'; ?>
                                </span>
                                <span class="codes-display-meta">Una vez usado se invalida.</span>
                            </div>

                            <div class="codes-actions">
                                <?php if ($puedeGenerar): ?>
                                <form method="POST" action="../app/accion_codigos_registro.php" class="codes-form">
                                    <?php echo csrf_input(); ?>
                                    <input type="hidden" name="accion" value="generar_unico">
                                    <button type="submit" class="codes-btn codes-btn-primary">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M23 4v6h-6M1 20v-6h6M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path></svg>
                                        Generar Nuevo
                                    </button>
                                </form>
                                <?php endif; ?>

                                <?php if ($unico && $puedeCancelar): ?>
                                <form method="POST" action="../app/accion_codigos_registro.php" class="codes-form" onsubmit="return confirm('¿Cancelar el código único activo?');">
                                    <?php echo csrf_input(); ?>
                                    <input type="hidden" name="accion" value="cancelar_unico">
                                    <button type="submit" class="codes-btn codes-btn-danger">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>
                                        Cancelar
                                    </button>
                                </form>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div id="panel-temporal" class="codes-panel" role="tabpanel" aria-labelledby="tab-temporal" aria-hidden="true">
                            <div class="codes-panel-intro">
                                <h3 class="codes-panel-title">Código Temporal</h3>
                                <p class="codes-panel-desc">Un mismo código compartido que vence automáticamente en la fecha configurada.</p>
                            </div>

                            <div class="codes-display <?php echo $temporal ? 'active' : 'empty'; ?>" data-copy-target="codigo-temporal">
                                <span class="codes-display-value" id="codigo-temporal">
                                    <?php echo $temporal ? formatearCodigo($temporal->codigo) : 'Sin código activo'; ?>
                                </span>
                                <?php if ($temporal): ?>
                                    <span class="codes-display-meta">Vence el <?php echo formatearCodigo($temporal->fecha_expiracion); ?></span>
                                <?php else: ?>
                                    <span class="codes-display-meta">Válido para múltiples registros hasta su vencimiento.</span>
                                <?php endif; ?>
                            </div>

                            <div class="codes-actions">
                                <?php if ($puedeGenerar): ?>
                                <form method="POST" action="../app/accion_codigos_registro.php" class="codes-form">
                                    <?php echo csrf_input(); ?>
                                    <input type="hidden" name="accion" value="generar_temporal">
                                    <label for="dias_temporal" class="codes-sr-only">Días de vigencia</label>
                                    <div class="codes-input-pill">
                                        <span>Vigencia</span>
                                        <input type="number" id="dias_temporal" name="dias" min="1" max="7" value="7" required>
                                        <span>días</span>
                                    </div>
                                    <button type="submit" class="codes-btn codes-btn-primary">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M23 4v6h-6M1 20v-6h6M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path></svg>
                                        Generar / Renovar
                                    </button>
                                </form>
                                <?php endif; ?>

                                <?php if ($temporal && $puedeCancelar): ?>
                                <form method="POST" action="../app/accion_codigos_registro.php" class="codes-form" onsubmit="return confirm('¿Cancelar el código temporal activo?');">
                                    <?php echo csrf_input(); ?>
                                    <input type="hidden" name="accion" value="cancelar_temporal">
                                    <button type="submit" class="codes-btn codes-btn-danger">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>
                                        Cancelar
                                    </button>
                                </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </main>
    </div>

    <div id="codes-toast" class="codes-toast" role="status" aria-live="polite">Código copiado</div>

    <script src="/dlgc_rrhh/assets/js/theme.js"></script>
    <script src="/dlgc_rrhh/assets/js/firstpage.js"></script>
    <script src="/dlgc_rrhh/assets/js/codigos_registro.js"></script>
</body>
</html>
