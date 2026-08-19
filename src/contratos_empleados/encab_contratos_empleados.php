<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DLGC | Empleados</title>
    <link rel="icon" type="image/png" sizes="32x32" href="/dlgc_rrhh/assets/img/favicon.ico">
    <link rel="icon" type="image/png" sizes="32x32" href="/dlgc_rrhh/assets/img/favicon.ico">
    <link rel="stylesheet" href="/dlgc_rrhh/assets/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="/dlgc_rrhh/assets/css/src.css">
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
                    <a href="listar_contratos_empleados.php" class="nav-link" aria-label="Listar">
                        <i class="fas fa-list" aria-hidden="true"></i>
                        <span class="nav-text">Listar</span>
                    </a>
                </div>
                <?php if (has_module_permission(13, 'crear')): ?>
                <div class="nav-item">
                                    <a href="forma_contratos_empleados.php" class="nav-link" aria-label="Agregar">
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
