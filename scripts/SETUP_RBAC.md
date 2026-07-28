# Guía de setup RBAC para DLGC_RRHH

## Nota de seguridad: CSRF

El proyecto utiliza protección CSRF mediante tokens de sesión.

- El helper está en `app/csrf_guard.php`.
- `auth_guard.php` carga automáticamente el helper en todas las vistas protegidas.
- Todo formulario POST debe incluir `<?php echo csrf_input(); ?>` dentro del `<form>`.
- Todo endpoint POST debe validar con `csrf_validate()` antes de procesar datos.
- El token se regenera automáticamente tras el login exitoso.

Ejecutar en este orden desde cero cuando se borre y recree la base de datos.

## 1. Esquema base

`modelo_db.sql` crea todas las tablas.

```bash
psql -U postgres -d db_dlgc_rrhh -f "c:\Apache24\htdocs\DLGC_RRHH\scripts\modelo_db.sql"
```

## 2. Triggers de auditoría, soft delete y borrado físico

```bash
psql -U postgres -d db_dlgc_rrhh -f "c:\Apache24\htdocs\DLGC_RRHH\scripts\triggers\auditorias.sql"
psql -U postgres -d db_dlgc_rrhh -f "c:\Apache24\htdocs\DLGC_RRHH\scripts\triggers\softdelete.sql"
psql -U postgres -d db_dlgc_rrhh -f "c:\Apache24\htdocs\DLGC_RRHH\scripts\triggers\borrado_fisico.sql"
```

## 3. Catálogos geográficos

```bash
psql -U postgres -d db_dlgc_rrhh -f "c:\Apache24\htdocs\DLGC_RRHH\scripts\inserts\dpts_municipios.sql"
```

## 4. Funciones almacenadas

Ejecutar todas las funciones en `scripts/functions/` y subcarpetas.

Orden recomendado por dependencias:

```bash
# Catálogos maestros
psql -U postgres -d db_dlgc_rrhh -f "c:\Apache24\htdocs\DLGC_RRHH\scripts\functions\modulos\fun_insert_modulos.sql"
psql -U postgres -d db_dlgc_rrhh -f "c:\Apache24\htdocs\DLGC_RRHH\scripts\functions\modulos\fun_update_modulos.sql"
psql -U postgres -d db_dlgc_rrhh -f "c:\Apache24\htdocs\DLGC_RRHH\scripts\functions\modulos\fun_listar_modulos.sql"
psql -U postgres -d db_dlgc_rrhh -f "c:\Apache24\htdocs\DLGC_RRHH\scripts\functions\modulos\fun_softdelete_modulos.sql"
psql -U postgres -d db_dlgc_rrhh -f "c:\Apache24\htdocs\DLGC_RRHH\scripts\functions\modulos\fun_restore_modulos.sql"

psql -U postgres -d db_dlgc_rrhh -f "c:\Apache24\htdocs\DLGC_RRHH\scripts\functions\operaciones\fun_insert_operaciones.sql"
psql -U postgres -d db_dlgc_rrhh -f "c:\Apache24\htdocs\DLGC_RRHH\scripts\functions\operaciones\fun_update_operaciones.sql"
psql -U postgres -d db_dlgc_rrhh -f "c:\Apache24\htdocs\DLGC_RRHH\scripts\functions\operaciones\fun_listar_operaciones.sql"
psql -U postgres -d db_dlgc_rrhh -f "c:\Apache24\htdocs\DLGC_RRHH\scripts\functions\operaciones\fun_softdelete_operaciones.sql"
psql -U postgres -d db_dlgc_rrhh -f "c:\Apache24\htdocs\DLGC_RRHH\scripts\functions\operaciones\fun_restore_operaciones.sql"

psql -U postgres -d db_dlgc_rrhh -f "c:\Apache24\htdocs\DLGC_RRHH\scripts\functions\roles\fun_insert_roles.sql"
psql -U postgres -d db_dlgc_rrhh -f "c:\Apache24\htdocs\DLGC_RRHH\scripts\functions\roles\fun_update_roles.sql"
psql -U postgres -d db_dlgc_rrhh -f "c:\Apache24\htdocs\DLGC_RRHH\scripts\functions\roles\fun_listar_roles.sql"
psql -U postgres -d db_dlgc_rrhh -f "c:\Apache24\htdocs\DLGC_RRHH\scripts\functions\roles\fun_softdelete_roles.sql"
psql -U postgres -d db_dlgc_rrhh -f "c:\Apache24\htdocs\DLGC_RRHH\scripts\functions\roles\fun_restore_roles.sql"

psql -U postgres -d db_dlgc_rrhh -f "c:\Apache24\htdocs\DLGC_RRHH\scripts\functions\roles_operaciones\fun_insert_roles_operaciones.sql"
psql -U postgres -d db_dlgc_rrhh -f "c:\Apache24\htdocs\DLGC_RRHH\scripts\functions\roles_operaciones\fun_update_roles_operaciones.sql"
psql -U postgres -d db_dlgc_rrhh -f "c:\Apache24\htdocs\DLGC_RRHH\scripts\functions\roles_operaciones\fun_listar_roles_operaciones.sql"
psql -U postgres -d db_dlgc_rrhh -f "c:\Apache24\htdocs\DLGC_RRHH\scripts\functions\roles_operaciones\fun_softdelete_roles_operaciones.sql"
psql -U postgres -d db_dlgc_rrhh -f "c:\Apache24\htdocs\DLGC_RRHH\scripts\functions\roles_operaciones\fun_restore_roles_operaciones.sql"

# Permisos por usuario
psql -U postgres -d db_dlgc_rrhh -f "c:\Apache24\htdocs\DLGC_RRHH\scripts\functions\permisos_usuarios\fun_insert_permisos_usuarios.sql"
psql -U postgres -d db_dlgc_rrhh -f "c:\Apache24\htdocs\DLGC_RRHH\scripts\functions\permisos_usuarios\fun_update_permisos_usuarios.sql"
psql -U postgres -d db_dlgc_rrhh -f "c:\Apache24\htdocs\DLGC_RRHH\scripts\functions\permisos_usuarios\fun_listar_permisos_usuarios.sql"
psql -U postgres -d db_dlgc_rrhh -f "c:\Apache24\htdocs\DLGC_RRHH\scripts\functions\permisos_usuarios\fun_softdelete_permisos_usuarios.sql"
psql -U postgres -d db_dlgc_rrhh -f "c:\Apache24\htdocs\DLGC_RRHH\scripts\functions\permisos_usuarios\fun_restore_permisos_usuarios.sql"

# Usuarios
psql -U postgres -d db_dlgc_rrhh -f "c:\Apache24\htdocs\DLGC_RRHH\scripts\functions\usuarios\fun_insert_usuarios.sql"
psql -U postgres -d db_dlgc_rrhh -f "c:\Apache24\htdocs\DLGC_RRHH\scripts\functions\usuarios\fun_update_usuarios.sql"
psql -U postgres -d db_dlgc_rrhh -f "c:\Apache24\htdocs\DLGC_RRHH\scripts\functions\usuarios\fun_login_usuarios.sql"
psql -U postgres -d db_dlgc_rrhh -f "c:\Apache24\htdocs\DLGC_RRHH\scripts\functions\usuarios\fun_listar_usuarios.sql"
psql -U postgres -d db_dlgc_rrhh -f "c:\Apache24\htdocs\DLGC_RRHH\scripts\functions\usuarios\fun_softdelete_usuarios.sql"
psql -U postgres -d db_dlgc_rrhh -f "c:\Apache24\htdocs\DLGC_RRHH\scripts\functions\usuarios\fun_restore_usuarios.sql"

# Resto de catálogos y transaccionales
psql -U postgres -d db_dlgc_rrhh -f "c:\Apache24\htdocs\DLGC_RRHH\scripts\functions\areas\fun_insert_areas.sql"
psql -U postgres -d db_dlgc_rrhh -f "c:\Apache24\htdocs\DLGC_RRHH\scripts\functions\areas\fun_update_areas.sql"
psql -U postgres -d db_dlgc_rrhh -f "c:\Apache24\htdocs\DLGC_RRHH\scripts\functions\areas\fun_listar_areas.sql"
psql -U postgres -d db_dlgc_rrhh -f "c:\Apache24\htdocs\DLGC_RRHH\scripts\functions\areas\fun_softdelete_areas.sql"
psql -U postgres -d db_dlgc_rrhh -f "c:\Apache24\htdocs\DLGC_RRHH\scripts\functions\areas\fun_restore_areas.sql"

psql -U postgres -d db_dlgc_rrhh -f "c:\Apache24\htdocs\DLGC_RRHH\scripts\functions\bancos\fun_insert_bancos.sql"
psql -U postgres -d db_dlgc_rrhh -f "c:\Apache24\htdocs\DLGC_RRHH\scripts\functions\bancos\fun_update_bancos.sql"
psql -U postgres -d db_dlgc_rrhh -f "c:\Apache24\htdocs\DLGC_RRHH\scripts\functions\bancos\fun_listar_bancos.sql"
psql -U postgres -d db_dlgc_rrhh -f "c:\Apache24\htdocs\DLGC_RRHH\scripts\functions\bancos\fun_softdelete_bancos.sql"
psql -U postgres -d db_dlgc_rrhh -f "c:\Apache24\htdocs\DLGC_RRHH\scripts\functions\bancos\fun_restore_bancos.sql"

psql -U postgres -d db_dlgc_rrhh -f "c:\Apache24\htdocs\DLGC_RRHH\scripts\functions\eps\fun_insert_eps.sql"
psql -U postgres -d db_dlgc_rrhh -f "c:\Apache24\htdocs\DLGC_RRHH\scripts\functions\eps\fun_update_eps.sql"
psql -U postgres -d db_dlgc_rrhh -f "c:\Apache24\htdocs\DLGC_RRHH\scripts\functions\eps\fun_listar_eps.sql"
psql -U postgres -d db_dlgc_rrhh -f "c:\Apache24\htdocs\DLGC_RRHH\scripts\functions\eps\fun_softdelete_eps.sql"
psql -U postgres -d db_dlgc_rrhh -f "c:\Apache24\htdocs\DLGC_RRHH\scripts\functions\eps\fun_restore_eps.sql"

psql -U postgres -d db_dlgc_rrhh -f "c:\Apache24\htdocs\DLGC_RRHH\scripts\functions\arl\fun_insert_arl.sql"
psql -U postgres -d db_dlgc_rrhh -f "c:\Apache24\htdocs\DLGC_RRHH\scripts\functions\arl\fun_update_arl.sql"
psql -U postgres -d db_dlgc_rrhh -f "c:\Apache24\htdocs\DLGC_RRHH\scripts\functions\arl\fun_listar_arl.sql"
psql -U postgres -d db_dlgc_rrhh -f "c:\Apache24\htdocs\DLGC_RRHH\scripts\functions\arl\fun_softdelete_arl.sql"
psql -U postgres -d db_dlgc_rrhh -f "c:\Apache24\htdocs\DLGC_RRHH\scripts\functions\arl\fun_restore_arl.sql"

psql -U postgres -d db_dlgc_rrhh -f "c:\Apache24\htdocs\DLGC_RRHH\scripts\functions\cajacompensacion\fun_insert_cajacompensacion.sql"
psql -U postgres -d db_dlgc_rrhh -f "c:\Apache24\htdocs\DLGC_RRHH\scripts\functions\cajacompensacion\fun_update_cajacompensacion.sql"
psql -U postgres -d db_dlgc_rrhh -f "c:\Apache24\htdocs\DLGC_RRHH\scripts\functions\cajacompensacion\fun_listar_cajacompensacion.sql"
psql -U postgres -d db_dlgc_rrhh -f "c:\Apache24\htdocs\DLGC_RRHH\scripts\functions\cajacompensacion\fun_softdelete_cajacompensacion.sql"
psql -U postgres -d db_dlgc_rrhh -f "c:\Apache24\htdocs\DLGC_RRHH\scripts\functions\cajacompensacion\fun_restore_cajacompensacion.sql"

psql -U postgres -d db_dlgc_rrhh -f "c:\Apache24\htdocs\DLGC_RRHH\scripts\functions\pension\fun_insert_pension.sql"
psql -U postgres -d db_dlgc_rrhh -f "c:\Apache24\htdocs\DLGC_RRHH\scripts\functions\pension\fun_update_pension.sql"
psql -U postgres -d db_dlgc_rrhh -f "c:\Apache24\htdocs\DLGC_RRHH\scripts\functions\pension\fun_listar_pension.sql"
psql -U postgres -d db_dlgc_rrhh -f "c:\Apache24\htdocs\DLGC_RRHH\scripts\functions\pension\fun_softdelete_pension.sql"
psql -U postgres -d db_dlgc_rrhh -f "c:\Apache24\htdocs\DLGC_RRHH\scripts\functions\pension\fun_restore_pension.sql"

psql -U postgres -d db_dlgc_rrhh -f "c:\Apache24\htdocs\DLGC_RRHH\scripts\functions\cesantias\fun_insert_cesantias.sql"
psql -U postgres -d db_dlgc_rrhh -f "c:\Apache24\htdocs\DLGC_RRHH\scripts\functions\cesantias\fun_update_cesantias.sql"
psql -U postgres -d db_dlgc_rrhh -f "c:\Apache24\htdocs\DLGC_RRHH\scripts\functions\cesantias\fun_listar_cesantias.sql"
psql -U postgres -d db_dlgc_rrhh -f "c:\Apache24\htdocs\DLGC_RRHH\scripts\functions\cesantias\fun_softdelete_cesantias.sql"
psql -U postgres -d db_dlgc_rrhh -f "c:\Apache24\htdocs\DLGC_RRHH\scripts\functions\cesantias\fun_restore_cesantias.sql"

# Empleados y dependientes
psql -U postgres -d db_dlgc_rrhh -f "c:\Apache24\htdocs\DLGC_RRHH\scripts\functions\empleados\fun_insert_empleados.sql"
psql -U postgres -d db_dlgc_rrhh -f "c:\Apache24\htdocs\DLGC_RRHH\scripts\functions\empleados\fun_update_empleados.sql"
psql -U postgres -d db_dlgc_rrhh -f "c:\Apache24\htdocs\DLGC_RRHH\scripts\functions\empleados\fun_listar_empleados.sql"
psql -U postgres -d db_dlgc_rrhh -f "c:\Apache24\htdocs\DLGC_RRHH\scripts\functions\empleados\fun_softdelete_empleados.sql"
psql -U postgres -d db_dlgc_rrhh -f "c:\Apache24\htdocs\DLGC_RRHH\scripts\functions\empleados\fun_restore_empleados.sql"

psql -U postgres -d db_dlgc_rrhh -f "c:\Apache24\htdocs\DLGC_RRHH\scripts\functions\contratos_empleados\fun_insert_contratos_empleados.sql"
psql -U postgres -d db_dlgc_rrhh -f "c:\Apache24\htdocs\DLGC_RRHH\scripts\functions\contratos_empleados\fun_update_contratos_empleados.sql"
psql -U postgres -d db_dlgc_rrhh -f "c:\Apache24\htdocs\DLGC_RRHH\scripts\functions\contratos_empleados\fun_listar_contratos_empleados.sql"
psql -U postgres -d db_dlgc_rrhh -f "c:\Apache24\htdocs\DLGC_RRHH\scripts\functions\contratos_empleados\fun_softdelete_contratos_empleados.sql"
psql -U postgres -d db_dlgc_rrhh -f "c:\Apache24\htdocs\DLGC_RRHH\scripts\functions\contratos_empleados\fun_restore_contratos_empleados.sql"

psql -U postgres -d db_dlgc_rrhh -f "c:\Apache24\htdocs\DLGC_RRHH\scripts\functions\afiliaciones_empleados\fun_insert_afiliaciones_empleados.sql"
psql -U postgres -d db_dlgc_rrhh -f "c:\Apache24\htdocs\DLGC_RRHH\scripts\functions\afiliaciones_empleados\fun_update_afiliaciones_empleados.sql"
psql -U postgres -d db_dlgc_rrhh -f "c:\Apache24\htdocs\DLGC_RRHH\scripts\functions\afiliaciones_empleados\fun_listar_afiliaciones_empleados.sql"
psql -U postgres -d db_dlgc_rrhh -f "c:\Apache24\htdocs\DLGC_RRHH\scripts\functions\afiliaciones_empleados\fun_softdelete_afiliaciones_empleados.sql"
psql -U postgres -d db_dlgc_rrhh -f "c:\Apache24\htdocs\DLGC_RRHH\scripts\functions\afiliaciones_empleados\fun_restore_afiliaciones_empleados.sql"

psql -U postgres -d db_dlgc_rrhh -f "c:\Apache24\htdocs\DLGC_RRHH\scripts\functions\nomina\fun_insert_nomina.sql"
psql -U postgres -d db_dlgc_rrhh -f "c:\Apache24\htdocs\DLGC_RRHH\scripts\functions\nomina\fun_update_nomina.sql"
psql -U postgres -d db_dlgc_rrhh -f "c:\Apache24\htdocs\DLGC_RRHH\scripts\functions\nomina\fun_listar_nomina.sql"
psql -U postgres -d db_dlgc_rrhh -f "c:\Apache24\htdocs\DLGC_RRHH\scripts\functions\nomina\fun_softdelete_nomina.sql"
psql -U postgres -d db_dlgc_rrhh -f "c:\Apache24\htdocs\DLGC_RRHH\scripts\functions\nomina\fun_restore_nomina.sql"

# Permisos y días festivos
psql -U postgres -d db_dlgc_rrhh -f "c:\Apache24\htdocs\DLGC_RRHH\scripts\functions\tipos_permisos\fun_insert_tipos_permisos.sql"
psql -U postgres -d db_dlgc_rrhh -f "c:\Apache24\htdocs\DLGC_RRHH\scripts\functions\tipos_permisos\fun_update_tipos_permisos.sql"
psql -U postgres -d db_dlgc_rrhh -f "c:\Apache24\htdocs\DLGC_RRHH\scripts\functions\tipos_permisos\fun_listar_tipos_permisos.sql"
psql -U postgres -d db_dlgc_rrhh -f "c:\Apache24\htdocs\DLGC_RRHH\scripts\functions\tipos_permisos\fun_softdelete_tipos_permisos.sql"
psql -U postgres -d db_dlgc_rrhh -f "c:\Apache24\htdocs\DLGC_RRHH\scripts\functions\tipos_permisos\fun_restore_tipos_permisos.sql"

psql -U postgres -d db_dlgc_rrhh -f "c:\Apache24\htdocs\DLGC_RRHH\scripts\functions\solicitudes_permisos\fun_insert_solicitudes_permisos.sql"
psql -U postgres -d db_dlgc_rrhh -f "c:\Apache24\htdocs\DLGC_RRHH\scripts\functions\solicitudes_permisos\fun_update_solicitudes_permisos.sql"
psql -U postgres -d db_dlgc_rrhh -f "c:\Apache24\htdocs\DLGC_RRHH\scripts\functions\solicitudes_permisos\fun_listar_solicitudes_permisos.sql"
psql -U postgres -d db_dlgc_rrhh -f "c:\Apache24\htdocs\DLGC_RRHH\scripts\functions\solicitudes_permisos\fun_softdelete_solicitudes_permisos.sql"
psql -U postgres -d db_dlgc_rrhh -f "c:\Apache24\htdocs\DLGC_RRHH\scripts\functions\solicitudes_permisos\fun_restore_solicitudes_permisos.sql"

psql -U postgres -d db_dlgc_rrhh -f "c:\Apache24\htdocs\DLGC_RRHH\scripts\functions\solicitudes_permisos_motivos\fun_insert_solicitudes_permisos_motivos.sql"
psql -U postgres -d db_dlgc_rrhh -f "c:\Apache24\htdocs\DLGC_RRHH\scripts\functions\solicitudes_permisos_motivos\fun_update_solicitudes_permisos_motivos.sql"
psql -U postgres -d db_dlgc_rrhh -f "c:\Apache24\htdocs\DLGC_RRHH\scripts\functions\solicitudes_permisos_motivos\fun_listar_solicitudes_permisos_motivos.sql"
psql -U postgres -d db_dlgc_rrhh -f "c:\Apache24\htdocs\DLGC_RRHH\scripts\functions\solicitudes_permisos_motivos\fun_softdelete_solicitudes_permisos_motivos.sql"
psql -U postgres -d db_dlgc_rrhh -f "c:\Apache24\htdocs\DLGC_RRHH\scripts\functions\solicitudes_permisos_motivos\fun_restore_solicitudes_permisos_motivos.sql"

psql -U postgres -d db_dlgc_rrhh -f "c:\Apache24\htdocs\DLGC_RRHH\scripts\functions\dias_festivos\fun_insert_dias_festivos.sql"
psql -U postgres -d db_dlgc_rrhh -f "c:\Apache24\htdocs\DLGC_RRHH\scripts\functions\dias_festivos\fun_update_dias_festivos.sql"
psql -U postgres -d db_dlgc_rrhh -f "c:\Apache24\htdocs\DLGC_RRHH\scripts\functions\dias_festivos\fun_listar_dias_festivos.sql"
psql -U postgres -d db_dlgc_rrhh -f "c:\Apache24\htdocs\DLGC_RRHH\scripts\functions\dias_festivos\fun_softdelete_dias_festivos.sql"
psql -U postgres -d db_dlgc_rrhh -f "c:\Apache24\htdocs\DLGC_RRHH\scripts\functions\dias_festivos\fun_restore_dias_festivos.sql"

psql -U postgres -d db_dlgc_rrhh -f "c:\Apache24\htdocs\DLGC_RRHH\scripts\functions\permisos_dias_aprobados\fun_insert_permisos_dias_aprobados.sql"
psql -U postgres -d db_dlgc_rrhh -f "c:\Apache24\htdocs\DLGC_RRHH\scripts\functions\permisos_dias_aprobados\fun_update_permisos_dias_aprobados.sql"
psql -U postgres -d db_dlgc_rrhh -f "c:\Apache24\htdocs\DLGC_RRHH\scripts\functions\permisos_dias_aprobados\fun_listar_permisos_dias_aprobados.sql"
psql -U postgres -d db_dlgc_rrhh -f "c:\Apache24\htdocs\DLGC_RRHH\scripts\functions\permisos_dias_aprobados\fun_softdelete_permisos_dias_aprobados.sql"
psql -U postgres -d db_dlgc_rrhh -f "c:\Apache24\htdocs\DLGC_RRHH\scripts\functions\permisos_dias_aprobados\fun_restore_permisos_dias_aprobados.sql"
```

## 5. Seeds RBAC

```bash
psql -U postgres -d db_dlgc_rrhh -f "c:\Apache24\htdocs\DLGC_RRHH\scripts\inserts\rbac_seeds.sql"
```

Esto crea roles, módulos, operaciones, asignaciones y tipos de permiso.

## 6. Primer usuario

Insertar al menos un usuario con rol administrador desde PHP o SQL. Ejemplo:

```sql
SELECT fun_insert_usuarios(
    '1099740148',
    1,
    'admin',
    'CC',
    'Admin',
    'Admin',
    'admin@dlgc.com',
    '$2y$10$hash_de_password_hash_aqui',
    NULL,
    NULL
);
```

La contraseña debe generarse con `password_hash()` de PHP.

## Notas importantes

- Cerrar sesión del navegador después de ejecutar los seeds. `login.php` recarga permisos solo al iniciar sesión.
- El archivo `scripts/inserts/user_insert.sql` está obsoleto; usar `rbac_seeds.sql`.
- Si en el futuro se regeneran CRUDs con `generate_rbac_crud.py`, actualizar `rbac_seeds.sql` con los nuevos módulos y operaciones.
