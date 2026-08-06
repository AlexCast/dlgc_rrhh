<?php
declare(strict_types=1);

/**
 * Menú lateral reutilizable para las vistas de empleado.
 * Variable esperada:
 *   $activeItem (string): slug de la opción activa.
 */
$activeItem = $activeItem ?? '';

$iconInicio       = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>';
$iconComunicados  = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>';
$iconEmpleados    = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>';
$iconSolicitudes  = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>';
$iconSst           = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path><path d="M12 8v4"></path><path d="M12 16h.01"></path></svg>';
$iconSegSocial    = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>';
$iconEmpresa      = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="4" y="2" width="16" height="20" rx="2" ry="2"></rect><line x1="12" y1="6" x2="12.01" y2="6"></line><line x1="12" y1="10" x2="12.01" y2="10"></line><line x1="12" y1="14" x2="12.01" y2="14"></line><line x1="12" y1="18" x2="12.01" y2="18"></line></svg>';
$iconSoftware     = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect><line x1="8" y1="21" x2="16" y2="21"></line><line x1="12" y1="17" x2="12" y2="21"></line></svg>';
$iconEps          = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 12h-4l-3 9L9 3l-3 9H2"></path></svg>';
$iconArl          = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>';
$iconPension      = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>';
$iconCaja         = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path></svg>';
$iconCesantias    = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>';
$iconBancos       = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path></svg>';
$iconNomina       = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path></svg>';
$iconContratos    = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>';
$iconAfiliaciones = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>';
$iconAreas        = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>';
$iconRoles        = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>';
$iconOperaciones  = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>';
$iconUsuarios     = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>';
$iconSrc          = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>';
$iconModulos      = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>';
$iconFestivos     = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line><path d="M8 14h.01M12 14h.01M16 14h.01M8 18h.01M12 18h.01"></path></svg>';
$iconVacaciones   = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.8 19.2 16 11l3.5-3.5C21 6 21.5 4 21 3c-1-.5-3 0-4.5 1.5L13 8 4.8 6.2c-.5-.1-1 .1-1.3.5l-.4.5c-.4.5-.2 1.2.3 1.5L9 12l-2 3H4l-1 1 3 2 2 3 1-1v-3l3-2 3.5 5.3c.3.5 1 .7 1.5.3l.5-.4c.4-.3.6-.8.5-1.3z"></path></svg>';
$iconChevron      = '<svg class="chevron" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"></polyline></svg>';

$grupos = [
    'seguridad_social' => [
        'titulo' => 'Seguridad Social',
        'icono'  => $iconSegSocial,
        'modulos' => [
            ['id' => 14, 'slug' => 'eps',        'titulo' => 'EPS',               'url' => '/dlgc_rrhh/src/eps/listar_eps.php',               'icono' => $iconEps],
            ['id' => 9,  'slug' => 'arl',        'titulo' => 'ARL',               'url' => '/dlgc_rrhh/src/arl/listar_arl.php',               'icono' => $iconArl],
            ['id' => 16, 'slug' => 'pension',    'titulo' => 'Pensión',           'url' => '/dlgc_rrhh/src/pension/listar_pension.php',       'icono' => $iconPension],
            ['id' => 11, 'slug' => 'caja',       'titulo' => 'Caja Compensación', 'url' => '/dlgc_rrhh/src/cajacompensacion/listar_cajacompensacion.php', 'icono' => $iconCaja],
            ['id' => 12, 'slug' => 'cesantias',  'titulo' => 'Cesantías',         'url' => '/dlgc_rrhh/src/cesantias/listar_cesantias.php',   'icono' => $iconCesantias],
        ],
    ],
    'empresa' => [
        'titulo' => 'Empresa',
        'icono'  => $iconEmpresa,
        'modulos' => [
            ['id' => 10, 'slug' => 'bancos',       'titulo' => 'Bancos',               'url' => '/dlgc_rrhh/src/banco/listar_banco.php',                         'icono' => $iconBancos],
            ['id' => 15, 'slug' => 'nomina',       'titulo' => 'Nómina',               'url' => '/dlgc_rrhh/src/nomina/listar_nomina.php',                       'icono' => $iconNomina],
            ['id' => 13, 'slug' => 'contratos',    'titulo' => 'Contratos Empleados',  'url' => '/dlgc_rrhh/src/contratos_empleados/listar_contratos_empleados.php', 'icono' => $iconContratos],
            ['id' => 7,  'slug' => 'afiliaciones', 'titulo' => 'Afiliaciones Empleados','url' => '/dlgc_rrhh/src/afiliaciones_empleados/listar_afiliaciones_empleados.php', 'icono' => $iconAfiliaciones],
            ['id' => 8,  'slug' => 'areas',        'titulo' => 'Áreas',                'url' => '/dlgc_rrhh/src/area/listar_area.php',                           'icono' => $iconAreas],
            ['id' => 28, 'slug' => 'dias_festivos','titulo' => 'Días Festivos',        'url' => '/dlgc_rrhh/src/dias_festivos/listar_dias_festivos.php',         'icono' => $iconFestivos],
        ],
    ],
    'software' => [
        'titulo' => 'Software',
        'icono'  => $iconSoftware,
        'modulos' => [
            ['id' => 17, 'slug' => 'roles',       'titulo' => 'Roles',            'url' => '/dlgc_rrhh/src/roles/listar_roles.php',                   'icono' => $iconRoles],
            ['id' => 19, 'slug' => 'operaciones', 'titulo' => 'Operaciones',      'url' => '/dlgc_rrhh/src/operaciones/listar_operaciones.php',       'icono' => $iconOperaciones],
            ['id' => 20, 'slug' => 'usuarios',    'titulo' => 'Usuarios',         'url' => '/dlgc_rrhh/src/usuarios/listar_usuarios.php',             'icono' => $iconUsuarios],
            ['id' => 6,  'slug' => 'src',         'titulo' => 'Roles Operaciones','url' => '/dlgc_rrhh/src/roles_operaciones/listar_roles_operaciones.php', 'icono' => $iconSrc],
            ['id' => 18, 'slug' => 'modulos',     'titulo' => 'Módulos',          'url' => '/dlgc_rrhh/src/modulos/listar_modulos.php',               'icono' => $iconModulos],
        ],
    ],
];

$gruposVisibles = [];
foreach ($grupos as $key => $grupo) {
    foreach ($grupo['modulos'] as $modulo) {
        if (has_module_access($modulo['id'])) {
            $gruposVisibles[$key] = $grupo;
            break;
        }
    }
}

// Módulos SRC/administrativos sueltos (no agrupados) que el usuario tenga asignados.
// La clave es el slug usado como $activeItem en cada vista.
$modulosAdminSueltos = [
    ['id' => 24, 'slug' => 'admin_comunicados', 'titulo' => 'Administrar Comunicados', 'url' => '/dlgc_rrhh/src/comunicados/listar_comunicados.php', 'icono' => $iconSrc],
    ['id' => 26, 'slug' => 'admin_sst',         'titulo' => 'Administración SST',      'url' => '/dlgc_rrhh/src/sst/listar_quejas.php',              'icono' => $iconSst],
    ['id' => 29, 'slug' => 'vacaciones',        'titulo' => 'Gestión de Vacaciones',   'url' => '/dlgc_rrhh/templates/vacaciones.php',               'icono' => $iconVacaciones],
];

function renderNavItem(string $url, string $titulo, string $icono, string $slug, string $activeItem): string {
    $isActive = $activeItem === $slug;
    $activeClass = $isActive ? ' active' : '';
    $ariaCurrent = $isActive ? ' aria-current="page"' : '';
    return sprintf(
        '<li><a href="%s" class="nav-item%s"%s>%s<span>%s</span></a></li>',
        htmlspecialchars($url),
        $activeClass,
        $ariaCurrent,
        $icono,
        htmlspecialchars($titulo)
    );
}

function renderGrupo(string $key, array $grupo, string $activeItem, string $iconChevron): string {
    $hijoActivo = false;
    foreach ($grupo['modulos'] as $modulo) {
        if (has_module_access($modulo['id']) && $activeItem === $modulo['slug']) {
            $hijoActivo = true;
            break;
        }
    }
    $expanded = $hijoActivo ? 'true' : 'false';
    $openClass = $hijoActivo ? ' open' : '';

    $html = '<li class="nav-group' . $openClass . '">';
    $html .= '<button type="button" class="nav-item nav-group-toggle" aria-expanded="' . $expanded . '" aria-controls="nav-group-' . $key . '">';
    $html .= $grupo['icono'];
    $html .= '<span>' . htmlspecialchars($grupo['titulo']) . '</span>';
    $html .= $iconChevron;
    $html .= '</button>';
    $html .= '<ul id="nav-group-' . $key . '" class="nav-group-menu">';
    foreach ($grupo['modulos'] as $modulo) {
        if (has_module_access($modulo['id'])) {
            $html .= renderNavItem($modulo['url'], $modulo['titulo'], $modulo['icono'], $modulo['slug'], $activeItem);
        }
    }
    $html .= '</ul></li>';
    return $html;
}
?>
<nav class="sidebar-nav" aria-label="Enlaces del panel">
    <ul>
        <?php echo renderNavItem('/dlgc_rrhh/templates/firstpage.php',      'Inicio',                $iconInicio,      'inicio',      $activeItem); ?>
        <?php echo renderNavItem('/dlgc_rrhh/templates/comunicados.php',   'Comunicados',           $iconComunicados, 'comunicados', $activeItem); ?>
        <?php echo renderNavItem('/dlgc_rrhh/templates/empleados.php',     'Empleados',             $iconEmpleados,   'empleados',   $activeItem); ?>
        <?php echo renderNavItem('/dlgc_rrhh/templates/solicitud_permiso.php', 'Solicitudes y Permisos', $iconSolicitudes, 'solicitudes', $activeItem); ?>
        <?php echo renderNavItem('/dlgc_rrhh/templates/sst.php',           'SST',                   $iconSst,         'sst',         $activeItem); ?>

        <?php foreach ($gruposVisibles as $key => $grupo): ?>
            <?php echo renderGrupo($key, $grupo, $activeItem, $iconChevron); ?>
        <?php endforeach; ?>

        <?php foreach ($modulosAdminSueltos as $modulo): ?>
            <?php if (has_module_access($modulo['id'])): ?>
                <?php echo renderNavItem($modulo['url'], $modulo['titulo'], $modulo['icono'], $modulo['slug'], $activeItem); ?>
            <?php endif; ?>
        <?php endforeach; ?>
    </ul>
</nav>

<script>
(function () {
    document.querySelectorAll('.nav-group-toggle').forEach(function (toggle) {
        toggle.addEventListener('click', function () {
            var group = this.closest('.nav-group');
            var isOpen = group.classList.toggle('open');
            this.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        });
    });
})();
</script>
