<?php
require_once '../app/auth_guard.php';
require_once '../app/conexion.php';

require_module_access(5);

// La base de datos almacena hora local de Colombia; forzamos esa zona en PHP
// para que los cálculos relativos ("hace X horas") sean correctos.
date_default_timezone_set('America/Bogota');

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

// Cargar comunicados activos enriquecidos con estado de lectura del usuario actual
$sentenciaComunicados = $conexion->prepare('SELECT * FROM fun_listar_comunicados_activos(?);');
$sentenciaComunicados->execute([$idUsuarioSesion]);
$comunicados = $sentenciaComunicados->fetchAll(PDO::FETCH_OBJ);

function tiempoRelativo(?string $fecha): string
{
    if (empty($fecha)) {
        return 'Fecha desconocida';
    }

    $dt = date_create($fecha);
    if ($dt === false) {
        return htmlspecialchars($fecha, ENT_QUOTES, 'UTF-8');
    }

    $now = new DateTime();
    $diff = $now->diff($dt);

    if ($diff->y > 0) {
        return 'Hace ' . $diff->y . ' año' . ($diff->y > 1 ? 's' : '');
    }
    if ($diff->m > 0) {
        return 'Hace ' . $diff->m . ' mes' . ($diff->m > 1 ? 'es' : '');
    }
    if ($diff->d > 0) {
        return 'Hace ' . $diff->d . ' día' . ($diff->d > 1 ? 's' : '');
    }
    if ($diff->h > 0) {
        return 'Hace ' . $diff->h . ' hora' . ($diff->h > 1 ? 's' : '');
    }
    if ($diff->i > 0) {
        return 'Hace ' . $diff->i . ' minuto' . ($diff->i > 1 ? 's' : '');
    }
    return 'Hace un momento';
}

require_once __DIR__ . '/../app/helpers/HtmlSanitizer.php';

function truncarTexto(?string $texto, int $limite = 150): string
{
    $textoPlano = strip_tags(trim((string) $texto));
    if (mb_strlen($textoPlano) <= $limite) {
        return $textoPlano;
    }
    return mb_substr($textoPlano, 0, $limite) . '...';
}

function limpiarContenidoHtml(?string $html): string
{
    return HtmlSanitizer::clean($html);
}

function formatearFechaHora(?string $fecha): string
{
    if (empty($fecha)) {
        return 'N/A';
    }

    $dt = date_create($fecha);
    if ($dt === false) {
        return htmlspecialchars($fecha, ENT_QUOTES, 'UTF-8');
    }

    return $dt->format('d/m/Y H:i');
}

$categoriasClase = [
    'GENERAL' => 'tag-general',
    'URGENTE' => 'tag-urgent',
    'EVENTO' => 'tag-event',
    'INFORMACION' => 'tag-info',
    'INSTITUCIONAL' => 'tag-institutional',
];

$csrfToken = htmlspecialchars(csrf_get_token(), ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comunicados | DLGC</title>
    <link rel="stylesheet" href="/dlgc_rrhh/assets/css/firstpage.css">
    <link rel="stylesheet" href="/dlgc_rrhh/assets/css/comunicados.css">
    <link rel="stylesheet" href="/dlgc_rrhh/assets/css/comunicados_templates.css">
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

            <div class="dashboard-body fade-in-up comunicados-public-app">

                <section class="comunicados-header">
                    <div>
                        <h2>Últimas Noticias</h2>
                        <p class="text-muted">Mantente al día con la información oficial de la empresa.</p>
                    </div>
                    <?php if (has_module_permission(24, 'crear') || has_module_permission(24, 'actualizar')): ?>
                    <a href="/dlgc_rrhh/src/comunicados/listar_comunicados.php" class="btn-admin-comunicados">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                        Gestionar
                    </a>
                    <?php endif; ?>
                </section>

                <div class="comunicados-list" data-csrf-token="<?php echo $csrfToken; ?>">
                    <?php if (count($comunicados) === 0): ?>
                        <div class="comunicado-card comunicado-empty">
                            <p>No hay comunicados publicados en este momento.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($comunicados as $com): ?>
                            <?php
                            $tagClass = $categoriasClase[$com->categoria] ?? 'tag-general';
                            $contenidoCompleto = limpiarContenidoHtml((string) $com->contenido);
                            $textoPlano = strip_tags($contenidoCompleto);
                            $esCorto = mb_strlen($textoPlano) <= 150;
                            $contenidoCorto = truncarTexto($contenidoCompleto, 150);
                            $visto = (bool) $com->visto_por_usuario;
                            $categoryClassMap = [
                                'GENERAL' => 'is-general',
                                'URGENTE' => 'is-urgent',
                                'EVENTO' => 'is-event',
                                'INFORMACION' => 'is-info',
                                'INSTITUCIONAL' => 'is-institutional',
                            ];
                            $modalCategoryClass = $categoryClassMap[$com->categoria] ?? 'is-general';
                            ?>
                            <article
                                class="comunicado-card <?php echo $visto ? 'comunicado-visto' : 'comunicado-nuevo'; ?>"
                                data-id-comunicado="<?php echo (int) $com->id_comunicado; ?>"
                                data-titulo="<?php echo htmlspecialchars((string) $com->titulo, ENT_QUOTES, 'UTF-8'); ?>"
                                data-categoria="<?php echo htmlspecialchars((string) $com->categoria, ENT_QUOTES, 'UTF-8'); ?>"
                                data-categoria-clase="<?php echo $modalCategoryClass; ?>"
                                data-autor="<?php echo htmlspecialchars((string) ($com->usr_insert ?? 'Administrador'), ENT_QUOTES, 'UTF-8'); ?>"
                                data-fecha="<?php echo tiempoRelativo($com->fec_insert); ?>"
                                data-fecha-completa="<?php echo formatearFechaHora($com->fec_insert); ?>"
                                data-contenido="<?php echo htmlspecialchars($contenidoCompleto, ENT_QUOTES, 'UTF-8'); ?>"
                                data-vistos="<?php echo (int) $com->total_vistos; ?>"
                                data-visto="<?php echo $visto ? '1' : '0'; ?>"
                                role="button"
                                tabindex="0"
                            >
                                <div class="comunicado-meta">
                                    <span class="comunicado-tag <?php echo $tagClass; ?>">
                                        <?php echo htmlspecialchars((string) $com->categoria, ENT_QUOTES, 'UTF-8'); ?>
                                    </span>
                                    <?php if (!$visto): ?>
                                        <span class="comunicado-nuevo-badge">Nuevo</span>
                                    <?php endif; ?>
                                </div>

                                <div class="comunicado-author">
                                    <div class="author-avatar">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                                    </div>
                                    <div class="author-info">
                                        <span class="author-name"><?php echo htmlspecialchars((string) ($com->usr_insert ?? 'Administrador'), ENT_QUOTES, 'UTF-8'); ?></span>
                                        <span class="comunicado-date"><?php echo tiempoRelativo($com->fec_insert); ?></span>
                                    </div>
                                </div>

                                <h3 class="comunicado-title"><?php echo htmlspecialchars((string) $com->titulo, ENT_QUOTES, 'UTF-8'); ?></h3>

                                <div class="comunicado-content-wrapper">
                                    <?php if ($esCorto): ?>
                                        <div class="comunicado-content">
                                            <?php echo $contenidoCompleto; ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="comunicado-content comunicado-truncado">
                                            <?php echo nl2br(htmlspecialchars($contenidoCorto, ENT_QUOTES, 'UTF-8'), false); ?>
                                        </div>
                                        <button type="button" class="btn-ver-mas" data-accion="expandir">
                                            Ver más
                                        </button>
                                    <?php endif; ?>
                                </div>

                                <div class="comunicado-footer">
                                    <span class="vistos-count">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                        <?php echo (int) $com->total_vistos; ?> visto<?php echo ((int) $com->total_vistos) !== 1 ? 's' : ''; ?>
                                    </span>
                                    <?php if ($visto): ?>
                                        <span class="visto-badge">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                            Leído
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Modal de lectura de comunicado -->
            <div class="cp-modal-overlay" id="cp-modal-comunicado" aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="cp-modal-title">
                <div class="cp-modal" role="document">
                    <header class="cp-modal-header">
                        <button type="button" class="cp-modal-close" data-cp-close aria-label="Cerrar comunicado">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                        <span class="cp-modal-category" id="cp-modal-category">Categoría</span>
                        <h2 class="cp-modal-title" id="cp-modal-title">Título del comunicado</h2>
                        <div class="cp-modal-meta">
                            <div class="cp-modal-avatar">
                                <i class="fas fa-user"></i>
                            </div>
                            <div class="cp-modal-info">
                                <span class="cp-modal-author" id="cp-modal-author">Autor</span>
                                <span class="cp-modal-date" id="cp-modal-date">Fecha</span>
                            </div>
                        </div>
                    </header>
                    <div class="cp-modal-body">
                        <div class="cp-modal-content" id="cp-modal-content"></div>
                    </div>
                    <div class="cp-modal-footer">
                        <span class="cp-modal-vistos">
                            <i class="fas fa-eye"></i>
                            <span id="cp-modal-vistos">0 vistos</span>
                        </span>
                        <div class="cp-modal-actions">
                            <button type="button" class="cp-btn cp-btn-secondary" data-cp-close>
                                <i class="fas fa-times"></i> Cerrar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Scripts -->
    <script src="/dlgc_rrhh/assets/js/theme.js"></script>
    <script src="/dlgc_rrhh/assets/js/comunicados.js"></script>
</body>
</html>
