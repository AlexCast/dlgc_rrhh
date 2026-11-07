<?php
declare(strict_types=1);

/**
 * Módulo Gestor de Archivos (30): control de versiones de los archivos físicos
 * de los documentos de RRHH.
 *
 * Acceso restringido por RBAC: auth_guard + require_module_access(30).
 * Cada subida crea una versión nueva; la vigente se muestra en la tabla y las
 * anteriores quedan disponibles en el modal "Ver versiones" (histórico).
 * Toda consulta a base de datos se hace con procedimientos almacenados.
 */

require_once '../app/auth_guard.php';
require_once '../app/conexion.php';
require_once '../app/helpers/TurnstileHelper.php';

$turnstileSiteKey = TurnstileHelper::siteKey();

require_module_access(30);

date_default_timezone_set('America/Bogota');

$puedeCrear = has_module_permission(30, 'crear');

$documentos = [];
$sentenciaDocs = $conexion->prepare('CALL proc_listar_documentos_gestor()');
$sentenciaDocs->execute();
$documentos = $sentenciaDocs->fetchAll(PDO::FETCH_OBJ);
$sentenciaDocs->closeCursor();

function formatearFechaGestor(?string $fecha): string
{
    if (empty($fecha)) {
        return 'N/A';
    }

    $dt = date_create($fecha);
    return $dt === false
        ? htmlspecialchars($fecha, ENT_QUOTES, 'UTF-8')
        : $dt->format('d/m/Y H:i');
}

function formatearTamanoGestor(?int $bytes): string
{
    if ($bytes === null || $bytes <= 0) {
        return 'N/A';
    }

    if ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2, ',', '.') . ' MB';
    }

    return number_format($bytes / 1024, 0, ',', '.') . ' KB';
}

$csrfToken = htmlspecialchars(csrf_get_token(), ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestor de Archivos | DLGC</title>
    <link rel="stylesheet" href="/assets/css/firstpage.css?v=<?php echo filemtime(__DIR__ . '/../assets/css/firstpage.css'); ?>">
    <link rel="stylesheet" href="/assets/css/gestor_archivos.css?v=<?php echo filemtime(__DIR__ . '/../assets/css/gestor_archivos.css'); ?>">
    <link rel="icon" type="image/png" sizes="32x32" href="/assets/img/favicon.ico">
    <?php if ($turnstileSiteKey !== ''): ?>
    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" defer></script>
    <?php endif; ?>
</head>
<body
    data-csrf="<?php echo $csrfToken; ?>"
    data-puede-crear="<?php echo $puedeCrear ? '1' : '0'; ?>"
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

            <?php $activeItem = 'gestor_archivos'; include __DIR__ . '/partials/sidebar_nav.php'; ?>
            <a href="/templates/ficha_tecnica.php" class="sidebar-profile" aria-label="Ver mi perfil">
                <div class="profile-avatar" aria-hidden="true">
                    <?php if (!empty($_SESSION['foto_perfil'])): ?>
                        <img src="<?php echo htmlspecialchars($_SESSION['foto_perfil']); ?>" alt="Foto de perfil" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
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
                    <h1>Gestor de Archivos</h1>
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

            <div class="dashboard-body fade-in-up">

                <section class="gestor-header">
                    <div>
                        <h2>Archivos de documentos RRHH</h2>
                        <p class="text-muted">Consulta el archivo vigente de cada documento y su historial de versiones.</p>
                    </div>
                    <button type="button" id="gestor-btn-actualizar" class="gestor-btn gestor-btn-outline">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 4 23 10 17 10"></polyline><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"></path></svg>
                        Actualizar
                    </button>
                </section>

                <div id="gestor-alertas" class="gestor-alertas" hidden></div>

                <section class="gestor-card">
                    <div class="gestor-card-header">
                        <h3>Documentos y archivos vigentes</h3>
                        <span class="text-muted" id="gestor-contador"><?php echo count($documentos); ?> documento(s)</span>
                    </div>

                    <div class="gestor-table-wrap">
                        <table class="gestor-table">
                            <thead>
                                <tr>
                                    <th scope="col">Documento</th>
                                    <th scope="col">Empleado</th>
                                    <th scope="col">Archivo vigente</th>
                                    <th scope="col">Versiones</th>
                                    <th scope="col">Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="gestor-tbody">
                                <?php if (count($documentos) === 0): ?>
                                    <tr id="gestor-fila-vacia">
                                        <td colspan="5" class="gestor-empty">No hay documentos registrados todavía.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($documentos as $doc): ?>
                                        <tr>
                                            <td data-label="Documento">
                                                <div class="gestor-doc">
                                                    <span class="gestor-doc-titulo"><?php echo htmlspecialchars((string) $doc->titulo, ENT_QUOTES, 'UTF-8'); ?></span>
                                                    <span class="gestor-doc-tipo"><?php echo htmlspecialchars((string) $doc->tipo_documento, ENT_QUOTES, 'UTF-8'); ?></span>
                                                </div>
                                            </td>
                                            <td data-label="Empleado"><?php echo htmlspecialchars((string) ($doc->nombre_empleado ?? $doc->id_usuario), ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td data-label="Archivo vigente">
                                                <?php if (!empty($doc->archivo_actual)): ?>
                                                    <div class="gestor-archivo">
                                                        <span class="gestor-archivo-nombre"><?php echo htmlspecialchars((string) $doc->archivo_actual, ENT_QUOTES, 'UTF-8'); ?></span>
                                                        <span class="text-muted">
                                                            <?php echo formatearTamanoGestor($doc->archivo_tamano !== null ? (int) $doc->archivo_tamano : null); ?> ·
                                                            <?php echo formatearFechaGestor($doc->archivo_fecha); ?>
                                                        </span>
                                                        <a class="gestor-enlace" href="/app/gestor_descargar.php?id_version=<?php echo (int) $doc->id_version; ?>">
                                                            Descargar
                                                        </a>
                                                    </div>
                                                <?php else: ?>
                                                    <span class="text-muted">Sin archivo</span>
                                                <?php endif; ?>
                                            </td>
                                            <td data-label="Versiones">
                                                <span class="gestor-badge gestor-badge-versiones"><?php echo (int) $doc->total_versiones; ?></span>
                                            </td>
                                            <td data-label="Acciones">
                                                <div class="gestor-acciones">
                                                    <button type="button"
                                                        class="gestor-btn gestor-btn-outline gestor-btn-versiones"
                                                        data-id-documento="<?php echo (int) $doc->id_documento; ?>"
                                                        data-titulo="<?php echo htmlspecialchars((string) $doc->titulo, ENT_QUOTES, 'UTF-8'); ?>">
                                                        Ver versiones
                                                    </button>
                                                    <?php if ($puedeCrear): ?>
                                                        <button type="button"
                                                            class="gestor-btn gestor-btn-primary gestor-btn-subir"
                                                            data-id-documento="<?php echo (int) $doc->id_documento; ?>"
                                                            data-titulo="<?php echo htmlspecialchars((string) $doc->titulo, ENT_QUOTES, 'UTF-8'); ?>">
                                                            Subir versión
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
        </main>
    </div>

    <!-- Modal: historial de versiones -->
    <div class="gestor-modal-overlay" id="gestor-modal-versiones" hidden>
        <div class="gestor-modal" role="dialog" aria-modal="true" aria-labelledby="gestor-versiones-titulo">
            <div class="gestor-modal-header">
                <h3 id="gestor-versiones-titulo">Historial de versiones</h3>
                <button type="button" class="icon-btn gestor-modal-cerrar" aria-label="Cerrar modal">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                </button>
            </div>
            <div class="gestor-modal-body">
                <div id="gestor-versiones-list" class="gestor-versiones-list" aria-live="polite">
                    <p class="gestor-cargando">Cargando versiones...</p>
                </div>
            </div>
            <div class="gestor-modal-footer">
                <?php if ($puedeCrear): ?>
                    <button type="button" id="gestor-versiones-subir" class="gestor-btn gestor-btn-primary">Subir nueva versión</button>
                <?php endif; ?>
                <button type="button" class="gestor-btn gestor-btn-outline gestor-modal-cerrar">Cerrar</button>
            </div>
        </div>
    </div>

    <!-- Modal: subir nueva versión -->
    <div class="gestor-modal-overlay" id="gestor-modal-subir" hidden>
        <div class="gestor-modal" role="dialog" aria-modal="true" aria-labelledby="gestor-subir-titulo">
            <div class="gestor-modal-header">
                <h3 id="gestor-subir-titulo">Subir nueva versión</h3>
                <button type="button" class="icon-btn gestor-modal-cerrar" aria-label="Cerrar modal">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                </button>
            </div>
            <form id="gestor-form-subir" class="gestor-form" novalidate>
                <?php echo csrf_input(); ?>
                <input type="hidden" name="id_documento" id="gestor-subir-id-documento">
                <div class="gestor-form-campo">
                    <label for="gestor-subir-archivo">Archivo (PDF, JPG, PNG o WebP — máx. 10 MB) <span class="gestor-obligatorio">*</span></label>
                    <input type="file" id="gestor-subir-archivo" name="archivo" accept=".pdf,.jpg,.jpeg,.png,.webp,application/pdf,image/jpeg,image/png,image/webp" required>
                </div>
                <div class="gestor-form-campo">
                    <label for="gestor-subir-observacion">Observación (opcional)</label>
                    <textarea id="gestor-subir-observacion" name="observacion" maxlength="255" rows="3" placeholder="Ej: Se actualiza por Otro Sí"></textarea>
                    <span class="gestor-ayuda">La versión anterior se conserva en el historial.</span>
                </div>
                <?php if ($turnstileSiteKey !== ''): ?>
                <div class="cf-turnstile" style="margin-bottom: 0.75rem;" data-sitekey="<?php echo htmlspecialchars($turnstileSiteKey, ENT_QUOTES, 'UTF-8'); ?>" data-action="gestor_archivo"></div>
                <?php endif; ?>
                <div class="gestor-form-acciones">
                    <button type="submit" id="gestor-subir-guardar" class="gestor-btn gestor-btn-primary">Subir archivo</button>
                    <button type="button" class="gestor-btn gestor-btn-outline gestor-modal-cerrar">Cancelar</button>
                </div>
            </form>
        </div>
    </div>

    <script src="/assets/js/theme.js?v=<?php echo filemtime(__DIR__ . '/../assets/js/theme.js'); ?>"></script>
    <script src="/assets/js/gestor_archivos.js?v=<?php echo filemtime(__DIR__ . '/../assets/js/gestor_archivos.js'); ?>" defer></script>
</body>
</html>
