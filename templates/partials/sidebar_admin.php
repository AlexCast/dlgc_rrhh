<?php
declare(strict_types=1);

/**
 * Menú lateral reutilizable para el dashboard de administración (secondpage.php).
 * Variable esperada:
 *   $activeItem (string): slug de la opción activa.
 */
$activeItem = $activeItem ?? '';
?>
<nav class="sidebar-nav" aria-label="Enlaces del panel">
    <ul>
        <li>
            <a href="/dlgc_rrhh/templates/secondpage.php" class="nav-item<?php echo ($activeItem === 'inicio') ? ' active' : ''; ?>"<?php echo ($activeItem === 'inicio') ? ' aria-current="page"' : ''; ?>>
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
                <span>Inicio RRHH</span>
            </a>
        </li>
        <?php if (has_module_access(24)): ?>
        <li>
            <a href="/dlgc_rrhh/src/comunicados/listar_comunicados.php" class="nav-item<?php echo ($activeItem === 'admin_comunicados') ? ' active' : ''; ?>">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                <span>Administrar Comunicados</span>
            </a>
        </li>
        <?php endif; ?>
        <?php if (has_module_access(26)): ?>
        <li>
            <a href="/dlgc_rrhh/src/sst/listar_quejas.php" class="nav-item<?php echo ($activeItem === 'admin_sst') ? ' active' : ''; ?>">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path><path d="M12 8v4"></path><path d="M12 16h.01"></path></svg>
                <span>Administración SST</span>
            </a>
        </li>
        <?php endif; ?>
        <?php if (has_module_access(4)): ?>
        <li>
            <a href="/dlgc_rrhh/src/empleados/listar_empleados.php" class="nav-item<?php echo ($activeItem === 'empleados') ? ' active' : ''; ?>">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                <span>Empleados</span>
            </a>
        </li>
        <?php endif; ?>
        <?php if (has_module_access(13)): ?>
        <li>
            <a href="/dlgc_rrhh/src/contratos_empleados/listar_contratos_empleados.php" class="nav-item<?php echo ($activeItem === 'contratos') ? ' active' : ''; ?>">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                <span>Contratos</span>
            </a>
        </li>
        <?php endif; ?>
        <?php if (has_module_access(7)): ?>
        <li>
            <a href="/dlgc_rrhh/src/afiliaciones_empleados/listar_afiliaciones_empleados.php" class="nav-item<?php echo ($activeItem === 'afiliaciones') ? ' active' : ''; ?>">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                <span>Afiliaciones Empleados</span>
            </a>
        </li>
        <?php endif; ?>
        <?php if (has_module_access(15)): ?>
        <li>
            <a href="/dlgc_rrhh/src/nomina/listar_nomina.php" class="nav-item<?php echo ($activeItem === 'nomina') ? ' active' : ''; ?>">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path></svg>
                <span>Nómina</span>
            </a>
        </li>
        <?php endif; ?>
        <?php if (has_module_access(8)): ?>
        <li>
            <a href="/dlgc_rrhh/src/area/listar_area.php" class="nav-item<?php echo ($activeItem === 'areas') ? ' active' : ''; ?>">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
                <span>Áreas</span>
            </a>
        </li>
        <?php endif; ?>
        <?php if (has_module_access(14)): ?>
        <li>
            <a href="/dlgc_rrhh/src/eps/listar_eps.php" class="nav-item<?php echo ($activeItem === 'eps') ? ' active' : ''; ?>">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 12h-4l-3 9L9 3l-3 9H2"></path></svg>
                <span>EPS</span>
            </a>
        </li>
        <?php endif; ?>
        <?php if (has_module_access(9)): ?>
        <li>
            <a href="/dlgc_rrhh/src/arl/listar_arl.php" class="nav-item<?php echo ($activeItem === 'arl') ? ' active' : ''; ?>">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                <span>ARL</span>
            </a>
        </li>
        <?php endif; ?>
        <?php if (has_module_access(11)): ?>
        <li>
            <a href="/dlgc_rrhh/src/cajacompensacion/listar_cajacompensacion.php" class="nav-item<?php echo ($activeItem === 'caja') ? ' active' : ''; ?>">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path></svg>
                <span>Caja Compensación</span>
            </a>
        </li>
        <?php endif; ?>
        <?php if (has_module_access(16)): ?>
        <li>
            <a href="/dlgc_rrhh/src/pension/listar_pension.php" class="nav-item<?php echo ($activeItem === 'pension') ? ' active' : ''; ?>">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                <span>Pensión</span>
            </a>
        </li>
        <?php endif; ?>
        <?php if (has_module_access(12)): ?>
        <li>
            <a href="/dlgc_rrhh/src/cesantias/listar_cesantias.php" class="nav-item<?php echo ($activeItem === 'cesantias') ? ' active' : ''; ?>">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
                <span>Cesantías</span>
            </a>
        </li>
        <?php endif; ?>
        <?php if (has_module_access(10)): ?>
        <li>
            <a href="/dlgc_rrhh/src/banco/listar_banco.php" class="nav-item<?php echo ($activeItem === 'bancos') ? ' active' : ''; ?>">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path></svg>
                <span>Bancos</span>
            </a>
        </li>
        <?php endif; ?>
        <?php if (has_module_access(17)): ?>
        <li>
            <a href="/dlgc_rrhh/src/roles/listar_roles.php" class="nav-item<?php echo ($activeItem === 'roles') ? ' active' : ''; ?>">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                <span>Roles</span>
            </a>
        </li>
        <?php endif; ?>
        <?php if (has_module_access(18)): ?>
        <li>
            <a href="/dlgc_rrhh/src/modulos/listar_modulos.php" class="nav-item<?php echo ($activeItem === 'modulos') ? ' active' : ''; ?>">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
                <span>Módulos</span>
            </a>
        </li>
        <?php endif; ?>
        <?php if (has_module_access(19)): ?>
        <li>
            <a href="/dlgc_rrhh/src/operaciones/listar_operaciones.php" class="nav-item<?php echo ($activeItem === 'operaciones') ? ' active' : ''; ?>">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                <span>Operaciones</span>
            </a>
        </li>
        <?php endif; ?>
        <?php if (has_module_access(6)): ?>
        <li>
            <a href="/dlgc_rrhh/src/roles_operaciones/listar_roles_operaciones.php" class="nav-item<?php echo ($activeItem === 'src') ? ' active' : ''; ?>">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                <span>Roles Operaciones</span>
            </a>
        </li>
        <?php endif; ?>
        <?php if (has_module_access(21)): ?>
        <li>
            <a href="/dlgc_rrhh/src/permisos_usuarios/listar_permisos_usuarios.php" class="nav-item<?php echo ($activeItem === 'permisos_usuarios') ? ' active' : ''; ?>">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                <span>Permisos por Usuario</span>
            </a>
        </li>
        <?php endif; ?>
        <?php if (has_module_access(20)): ?>
        <li>
            <a href="/dlgc_rrhh/src/usuarios/listar_usuarios.php" class="nav-item<?php echo ($activeItem === 'usuarios') ? ' active' : ''; ?>">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                <span>Usuarios</span>
            </a>
        </li>
        <?php endif; ?>
        <?php if (has_module_access(23)): ?>
        <li>
            <a href="/dlgc_rrhh/templates/codigos_registro.php" class="nav-item<?php echo ($activeItem === 'codigos_registro') ? ' active' : ''; ?>">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                <span>Códigos de Registro</span>
            </a>
        </li>
        <?php endif; ?>
        <li>
            <a href="/dlgc_rrhh/templates/firstpage.php" class="nav-item">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                <span>Volver al Panel Empleado</span>
            </a>
        </li>
    </ul>
</nav>
