<?php
declare(strict_types=1);

/**
 * Módulo Documentación (31): registros documentales del empleado.
 *
 * Módulo transversal: cualquier usuario autenticado lo ve (sin require_module_access).
 * Regla de negocio: solo VER e INSERTAR; no existen edición ni eliminación.
 * Toda consulta a base de datos se hace con procedimientos almacenados.
 */

require_once '../app/auth_guard.php';
require_once '../app/conexion.php';
require_once '../app/helpers/TurnstileHelper.php';

$turnstileSiteKey = TurnstileHelper::siteKey();

date_default_timezone_set('America/Bogota');

$idUsuarioSesion = $_SESSION['id_usuario'] ?? null;

$tipos = [];
$sentenciaTipos = $conexion->prepare('CALL proc_listar_tipos_documento()');
$sentenciaTipos->execute();
$tipos = $sentenciaTipos->fetchAll(PDO::FETCH_OBJ);
$sentenciaTipos->closeCursor();

$contratos = [];
$documentos = [];

if (is_string($idUsuarioSesion) && trim($idUsuarioSesion) !== '') {
    $sentenciaContratos = $conexion->prepare('CALL proc_listar_contratos_usuario(?)');
    $sentenciaContratos->execute([$idUsuarioSesion]);
    $contratos = $sentenciaContratos->fetchAll(PDO::FETCH_OBJ);
    $sentenciaContratos->closeCursor();

    $sentenciaDocs = $conexion->prepare('CALL proc_listar_documentos_usuario(?)');
    $sentenciaDocs->execute([$idUsuarioSesion]);
    $documentos = $sentenciaDocs->fetchAll(PDO::FETCH_OBJ);
    $sentenciaDocs->closeCursor();
}

function formatearFechaDoc(?string $fecha): string
{
    if (empty($fecha)) {
        return 'N/A';
    }

    $dt = date_create($fecha);
    return $dt === false
        ? htmlspecialchars($fecha, ENT_QUOTES, 'UTF-8')
        : $dt->format('d/m/Y H:i');
}

$csrfToken = htmlspecialchars(csrf_get_token(), ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Documentación | DLGC</title>
    <link rel="stylesheet" href="/assets/css/firstpage.css?v=<?php echo filemtime(__DIR__ . '/../assets/css/firstpage.css'); ?>">
    <link rel="stylesheet" href="/assets/css/documentacion.css?v=<?php echo filemtime(__DIR__ . '/../assets/css/documentacion.css'); ?>">
    <link rel="icon" type="image/png" sizes="32x32" href="/assets/img/favicon.ico">
    <?php if ($turnstileSiteKey !== ''): ?>
    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" defer></script>
    <?php endif; ?>
</head>
<body data-csrf="<?php echo $csrfToken; ?>">
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

            <?php $activeItem = 'documentacion'; include __DIR__ . '/partials/sidebar_nav.php'; ?>
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
                    <span class="profile-role">Documentación</span>
                </div>
            </a>
        </aside>

        <main class="main-content">
            <header class="top-bar">
                <div class="top-bar-left">
                    <button id="open-sidebar" class="icon-btn mobile-only" aria-label="Abrir menú de navegación">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
                    </button>
                    <h1>Documentación</h1>
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

                <section class="doc-header">
                    <div>
                        <h2>Mis documentos</h2>
                        <p class="text-muted">Registra y consulta los documentos asociados a tu hoja de vida laboral.</p>
                    </div>
                    <button type="button" id="doc-btn-nuevo" class="doc-btn doc-btn-primary">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                        Registrar documento
                    </button>
                </section>

                <div id="doc-alertas" class="doc-alertas" hidden></div>

                <section class="doc-card">
                    <div class="doc-card-header">
                        <h3>Registros documentales</h3>
                        <span class="text-muted" id="doc-contador"><?php echo count($documentos); ?> registro(s)</span>
                    </div>

                    <div class="doc-table-wrap">
                        <table class="doc-table">
                            <thead>
                                <tr>
                                    <th scope="col">Tipo</th>
                                    <th scope="col">Título</th>
                                    <th scope="col">Contrato</th>
                                    <th scope="col">Estado</th>
                                    <th scope="col">Archivo vigente</th>
                                    <th scope="col">Registrado</th>
                                </tr>
                            </thead>
                            <tbody id="doc-tbody">
                                <?php if (count($documentos) === 0): ?>
                                    <tr id="doc-fila-vacia">
                                        <td colspan="6" class="doc-empty">Aún no tienes documentos registrados.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($documentos as $doc): ?>
                                        <tr>
                                            <td data-label="Tipo"><?php echo htmlspecialchars((string) $doc->tipo_documento, ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td data-label="Título"><?php echo htmlspecialchars((string) $doc->titulo, ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td data-label="Contrato">
                                                <?php echo $doc->id_contrato ? '#' . (int) $doc->id_contrato : 'Sin contrato'; ?>
                                            </td>
                                            <td data-label="Estado">
                                                <span class="doc-badge doc-badge-<?php echo strtolower((string) $doc->estado); ?>">
                                                    <?php echo htmlspecialchars((string) $doc->estado, ENT_QUOTES, 'UTF-8'); ?>
                                                </span>
                                            </td>
                                            <td data-label="Archivo vigente">
                                                <?php if (!empty($doc->archivo_actual)): ?>
                                                    <span class="doc-archivo">
                                                        <?php if (!empty($doc->id_version)): ?>
                                                            <a class="doc-archivo-nombre doc-archivo-enlace" href="/app/gestor_descargar.php?id_version=<?php echo (int) $doc->id_version; ?>"><?php echo htmlspecialchars((string) $doc->archivo_actual, ENT_QUOTES, 'UTF-8'); ?></a>
                                                        <?php else: ?>
                                                            <span class="doc-archivo-nombre"><?php echo htmlspecialchars((string) $doc->archivo_actual, ENT_QUOTES, 'UTF-8'); ?></span>
                                                        <?php endif; ?>
                                                        <?php if ($doc->numero_version !== null): ?>
                                                            <span class="doc-badge doc-badge-vers">v<?php echo (int) $doc->numero_version; ?></span>
                                                        <?php endif; ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="text-muted">Sin archivo</span>
                                                <?php endif; ?>
                                            </td>
                                            <td data-label="Registrado"><?php echo formatearFechaDoc($doc->documento_fecha); ?></td>
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

    <!-- Modal: registrar documento (solo inserción, sin edición) -->
    <div class="doc-modal-overlay" id="doc-modal" hidden>
        <div class="doc-modal" role="dialog" aria-modal="true" aria-labelledby="doc-modal-titulo">
            <div class="doc-modal-header">
                <h3 id="doc-modal-titulo">Registrar documento</h3>
                <button type="button" id="doc-modal-cerrar" class="icon-btn" aria-label="Cerrar modal">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                </button>
            </div>
            <form id="doc-form" class="doc-form" enctype="multipart/form-data" novalidate>
                <?php echo csrf_input(); ?>
                <div class="doc-form-campo">
                    <label for="doc-tipo">Tipo de documento <span class="doc-obligatorio">*</span></label>
                    <select id="doc-tipo" name="id_tipo_documento" required>
                        <option value="">Selecciona un tipo...</option>
                        <?php foreach ($tipos as $tipo): ?>
                            <option value="<?php echo (int) $tipo->id_tipo_documento; ?>">
                                <?php echo htmlspecialchars((string) $tipo->nombre, ENT_QUOTES, 'UTF-8'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="doc-form-campo">
                    <label for="doc-titulo">Título <span class="doc-obligatorio">*</span></label>
                    <input type="text" id="doc-titulo" name="titulo" maxlength="150" minlength="3" placeholder="Ej: Contrato laboral 2026" required>
                    <span class="doc-ayuda">Entre 3 y 150 caracteres.</span>
                </div>
                <div class="doc-form-campo">
                    <label for="doc-contrato">Contrato asociado (opcional)</label>
                    <select id="doc-contrato" name="id_contrato">
                        <option value="">Sin contrato</option>
                        <?php foreach ($contratos as $contrato): ?>
                            <option value="<?php echo (int) $contrato->id_contrato; ?>">
                                <?php echo '#' . (int) $contrato->id_contrato . ' �€” ' . htmlspecialchars((string) $contrato->puesto, ENT_QUOTES, 'UTF-8'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="doc-form-campo">
                    <label for="doc-archivo">Archivo del documento <span class="doc-obligatorio">*</span></label>
                    <input type="file" id="doc-archivo" name="archivo" accept=".jpg,.jpeg,.png,.webp,.pdf" required>
                    <span class="doc-ayuda">JPG, PNG, WebP o PDF, máximo 10 MB.</span>
                </div>
                <?php if ($turnstileSiteKey !== ''): ?>
                <div class="cf-turnstile" style="margin-bottom: 0.75rem;" data-sitekey="<?php echo htmlspecialchars($turnstileSiteKey, ENT_QUOTES, 'UTF-8'); ?>" data-action="doc_empleado"></div>
                <?php endif; ?>
                <div class="doc-form-acciones">
                    <button type="submit" id="doc-btn-guardar" class="doc-btn doc-btn-primary">Guardar</button>
                    <button type="button" id="doc-modal-cancelar" class="doc-btn doc-btn-outline">Cancelar</button>
                </div>
            </form>
        </div>
    </div>

    <script src="/assets/js/theme.js?v=<?php echo filemtime(__DIR__ . '/../assets/js/theme.js'); ?>"></script>
    <script src="/assets/js/documentacion.js?v=<?php echo filemtime(__DIR__ . '/../assets/js/documentacion.js'); ?>" defer></script>
</body>
</html>
