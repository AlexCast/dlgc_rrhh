<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DLGC | Administración SST</title>
    <link rel="icon" type="image/png" sizes="32x32" href="/dlgc_rrhh/assets/img/favicon.ico">
    <link rel="stylesheet" href="/dlgc_rrhh/assets/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="/dlgc_rrhh/assets/css/src.css">
    <link rel="stylesheet" href="/dlgc_rrhh/assets/css/sst.css">
    <style>
        body {
            --bg-card: var(--mnt-surface);
            --border-color: var(--mnt-border);
            --text-main: var(--mnt-text);
            --text-muted: var(--mnt-text-muted);
            --bg-secondary: var(--mnt-bg);
            --primary-color: var(--mnt-primary);
        }
        body.dark-theme {
            --bg-card: var(--mnt-surface);
            --border-color: var(--mnt-border);
            --text-main: var(--mnt-text);
            --text-muted: var(--mnt-text-muted);
            --bg-secondary: var(--mnt-bg);
            --primary-color: var(--mnt-primary);
        }
    </style>
    <script src="/dlgc_rrhh/assets/js/src.js" defer></script>
    <script src="/dlgc_rrhh/assets/js/theme.js"></script>
    <script src="/dlgc_rrhh/assets/js/alert-banner.js" defer></script>
</head>
<body>
    <header class="header-container">
        <div class="header-content">
            <div class="brand-section">
                <a href="<?php echo get_dashboard_url(); ?>" class="brand-logo" aria-label="Inicio">
                    <img src="/dlgc_rrhh/assets/img/logo1.png" alt="Distribuciones La Gran Cacharrería">
                </a>
            </div>

            <nav class="nav-menu">
                <div class="nav-item">
                    <a href="listar_comite.php" class="nav-link" aria-label="Comités">
                        <i class="fas fa-users" aria-hidden="true"></i>
                        <span class="nav-text">Comités</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="listar_quejas.php" class="nav-link" aria-label="Quejas">
                        <i class="fas fa-inbox" aria-hidden="true"></i>
                        <span class="nav-text">Buzón</span>
                    </a>
                </div>
                <?php if (has_module_permission(26, 'crear')): ?>
                <div class="nav-item">
                    <a href="forma_comite.php" class="nav-link" aria-label="Agregar miembro">
                        <i class="fas fa-plus" aria-hidden="true"></i>
                        <span class="nav-text">Agregar</span>
                    </a>
                </div>
                <?php endif; ?>
                <div class="nav-item">
                    <a href="<?php echo get_dashboard_url(); ?>" class="nav-link" aria-label="Volver">
                        <i class="fas fa-arrow-left" aria-hidden="true"></i>
                        <span class="nav-text">Volver</span>
                    </a>
                </div>
            </nav>
        </div>
    </header>
