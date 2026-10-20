# Migración a MariaDB — DLGC_RRHH

Registro de la migración del motor de base de datos de PostgreSQL a **MariaDB** para el
proyecto DLGC_RRHH, empezando por la arquitectura RBAC (login incluido).

- **Motor destino:** MariaDB 13.0.2 (instalado en `C:\Program Files\MariaDB 13.0\bin`).
- **Estado:** PostgreSQL ya no está escuchando (puerto 5432 caído); MariaDB corre en
  `127.0.0.1:3306`.
- **Estrategia:** corte directo a MariaDB (sin capa de compatibilidad dual).
- **Alcance de esta fase:** RBAC completo — login, sesión, guards y CRUDs de
  `usuarios`, `roles`, `modulos`, `operaciones`, `roles_operaciones` y `permisos_usuarios`.

---

## 1. Estado previo de la migración

La capa SQL ya estaba convertida a MariaDB (esquema, triggers y funciones), pero la capa
PHP todavía mezclaba sintaxis PostgreSQL con MariaDB. Puntos críticos detectados:

| # | Ubicación | Problema |
|---|-----------|----------|
| 1 | `app/conexion.php` | DSN `pgsql:` apuntando a PostgreSQL (caído) |
| 2 | `app/login.php` | Llamaba `fun_login_usuarios(:userInput)`; ya no existe: ahora es `proc_login_usuarios` (procedimiento) |
| 3 | `app/helpers/SessionUserLoader.php` | `BOOL_OR(...)` (agregado PostgreSQL) |
| 4 | `app/auth_guard.php` | `set_config('app.current_user', ...)` (PostgreSQL) |
| 5 | `app/helpers/RememberMeHelper.php` | `INTERVAL '30 days'`, `current_setting(...)` |
| 6 | `app/helpers/RateLimiter.php` | Upsert `ON CONFLICT ... EXCLUDED` (PostgreSQL) |
| 7 | `src/usuarios/insertar_usuarios.php` y `update_usuarios.php` | Orden de parámetros incorrecto contra `fun_insert_usuarios` / `fun_update_usuarios` |
| 8 | `src/{roles,modulos,operaciones}/insertar_*.php` | Variables sin `$` (`id_rol` en vez de `$id_rol`) y parámetros nombrados `:param` |
| 9 | `src/permisos_usuarios/*` | Cast `id_modulo::integer` (PostgreSQL) |
| 10 | Todos los CRUDs RBAC | Parámetros nombrados `:param` (no soportados por PDO MySQL con `ATTR_EMULATE_PREPARES=false`) |

---

## 2. Base de datos (Fase 0)

El esquema, los triggers y las funciones RBAC ya estaban creados en `db_dlgc_rrhh`. En esta
fase se completó lo que faltaba:

- **Esquema:** [scripts/modelo_mariadb.sql](scripts/modelo_mariadb.sql) (38 tablas, ya cargado).
- **Triggers:** cargados `auditorias.sql`, `softdelete.sql` y `borrado_fisico.sql` (92 triggers).
- **Funciones RBAC:** cargadas desde `scripts/functions/{usuarios,roles,modulos,operaciones,roles_operaciones,permisos_usuarios}`.
- **Seeds RBAC:** se creó [scripts/inserts/rbac_seeds_mariadb.sql](scripts/inserts/rbac_seeds_mariadb.sql),
  conversión de `rbac_seeds.sql` (PostgreSQL `ON CONFLICT ... EXCLUDED` → MariaDB
  `ON DUPLICATE KEY UPDATE ... VALUES()`). Carga:
  - 2 roles, 28 módulos, 134 operaciones, 141 asignaciones rol-operación, 5 tipos de permiso.
- **Seed de admin para pruebas:** [scripts/inserts/seed_admin_rbac.sql](scripts/inserts/seed_admin_rbac.sql)
  crea/restaura `admin` y le asigna **todos** los permisos directos en
  `t_usuarios_operaciones` (idempotente).

### Comandos de carga (referencia)

```powershell
$exe = 'C:\Program Files\MariaDB 13.0\bin\mariadb.exe'
$db  = 'db_dlgc_rrhh'

# Esquema (solo si la BD está vacía)
Get-Content -Raw 'scripts\modelo_mariadb.sql' | & $exe -h 127.0.0.1 -P 3306 -u root -p $db

# Triggers
Get-Content -Raw 'scripts\triggers\auditorias.sql'    | & $exe -h 127.0.0.1 -P 3306 -u root -p $db
Get-Content -Raw 'scripts\triggers\softdelete.sql'    | & $exe -h 127.0.0.1 -P 3306 -u root -p $db
Get-Content -Raw 'scripts\triggers\borrado_fisico.sql'| & $exe -h 127.0.0.1 -P 3306 -u root -p $db

# Seeds RBAC (versión MariaDB)
Get-Content -Raw 'scripts\inserts\rbac_seeds_mariadb.sql' | & $exe -h 127.0.0.1 -P 3306 -u root -p $db

# Admin de pruebas con todos los permisos (idempotente)
Get-Content -Raw 'scripts\inserts\seed_admin_rbac.sql' | & $exe -h 127.0.0.1 -P 3306 -u root -p $db
```

> Las funciones RBAC ya están creadas. Si se necesita recrearlas desde cero, cargar en este
> orden por dominio: `modulos` → `operaciones` → `roles` → `roles_operaciones` →
> `permisos_usuarios` → `usuarios`.

---

## 3. Cambios en PHP (Fases 1–4)

### Conexión y configuración

- [app/conexion.php](app/conexion.php): DSN cambiado a
  `mysql:host=...;port=...;dbname=...;charset=utf8mb4`. Se mantiene
  `ATTR_EMULATE_PREPARES = false` y `SET @app_current_user = ?` para auditoría.
- [.env](.env) y [.env.example](.env.example): `DB_PORT=3306`, `DB_USER=root`.

### Login y sesión

- [app/login.php](app/login.php): usa `CALL proc_login_usuarios(?)` y cierra el cursor
  (`closeCursor()`) tras `fetch()` para liberar el result set del procedimiento.
- [app/helpers/SessionUserLoader.php](app/helpers/SessionUserLoader.php): `BOOL_OR(...)` →
  `MAX(...)` (devuelve 0/1; `normalize_permissions` ya castea a bool) y parámetros
  posicionales `?`.
- [app/helpers/RememberMeHelper.php](app/helpers/RememberMeHelper.php): `INTERVAL '30 days'` →
  `INTERVAL 30 DAY`, `current_setting('app.current_user', true)` → `fun_audit_actor()` y
  parámetros posicionales.
- [app/helpers/RateLimiter.php](app/helpers/RateLimiter.php): upsert
  `ON CONFLICT ... EXCLUDED` → `ON DUPLICATE KEY UPDATE ... VALUES()` y parámetros posicionales.

### Guards

- [app/auth_guard.php](app/auth_guard.php): `SELECT set_config(...)` →
  `SET @app_current_user = ?` (consistente con `conexion.php`).

### CRUDs RBAC

- `src/usuarios/insertar_usuarios.php` y `update_usuarios.php`: parámetros posicionales y
  **orden canónico** (`id_usuario, id_rol, username, tipo_documento, primer_nombre,
  primer_apellido, correo, contrasena, segundo_nombre, segundo_apellido`), idéntico al de
  `fun_insert_usuarios` y `app/verificar_correo.php`. `update_usuarios.php` ahora hace
  `password_hash()` antes de llamar a la función.
- `src/roles`, `src/modulos`, `src/operaciones`: parámetros posicionales y corrección de
  variables sin `$` en los `insertar_*`.
- `src/permisos_usuarios/listar_permisos_usuarios.php` y `forma_permisos_usuarios.php`:
  eliminado el cast `id_modulo::integer`.

### Función SQL corregida

- [scripts/functions/usuarios/fun_update_usuarios.sql](scripts/functions/usuarios/fun_update_usuarios.sql):
  reescrita para aceptar los **10 parámetros** que envía `update_usuarios.php` (antes solo
  tenía 6 y no actualizaba `id_rol`, `username`, `tipo_documento` ni `contrasena`).

### Dashboard (templates/firstpage.php)

- [templates/firstpage.php](templates/firstpage.php):
  - Parámetros nombrados `:param` → posicionales `?` en todas las consultas.
  - `ORDER BY ... DESC NULLS LAST` → `ORDER BY ... DESC` (en MariaDB el `DESC` ya deja los
    `NULL` al final).
  - `SELECT * FROM fun_calcular_saldo_vacaciones(:id_usuario)` →
    `CALL proc_calcular_saldo_vacaciones(?)` + `closeCursor()` (en MariaDB es procedimiento).
  - `EXTRACT(EPOCH FROM (hora_fin - hora_inicio))` → `TIME_TO_SEC(TIMEDIFF(hora_fin, hora_inicio))`.
  - `ILIKE ANY (ARRAY[...])` → `(col LIKE '...' OR col LIKE '...')`.
  - `EXTRACT(YEAR FROM ...)` → `YEAR(...)`.
  - `STRING_AGG(col, ', ' ORDER BY col)` → `GROUP_CONCAT(col ORDER BY col SEPARATOR ', ')`.
  - `INTERVAL '7 days'` → `INTERVAL 7 DAY`.
  - Casts `col::date` → `DATE(col)`; concatenación `'a' || col` → `CONCAT('a', col)`.

### Registro / verificación / recuperación (auth adyacente)

- [app/register.php](app/register.php), [app/accion_codigos_registro.php](app/accion_codigos_registro.php),
  [app/verificar_correo.php](app/verificar_correo.php), [app/recuperar_contrasena.php](app/recuperar_contrasena.php),
  [app/restablecer_contrasena.php](app/restablecer_contrasena.php) y
  [templates/codigos_registro.php](templates/codigos_registro.php):
  - Parámetros nombrados `:param` → posicionales `?`.
  - `lastInsertId('t_registros_pendientes_id_registro_pendiente_seq')` →
    `lastInsertId()` (MariaDB no usa secuencias).
  - Duplicado detectado con `$e->errorInfo[1] == 1062` (antes `$e->getCode() === '23505'`,
    código específico de PostgreSQL).
  - `ORDER BY ... DESC NULLS LAST` → `ORDER BY ... DESC` en `codigos_registro.php`.

### SRC restante (comunicados y SST)

- [src/comunicados/visto_por.php](src/comunicados/visto_por.php): parámetro nombrado
  `:id_comunicado` → `?`.
- [src/sst/listar_quejas.php](src/sst/listar_quejas.php): llamaba
  `SELECT * FROM fun_listar_sst_quejas_encargado(:estado)` (función que no existe en
  MariaDB) → `CALL proc_listar_sst_quejas_encargado(?)` + `closeCursor()`.
- El resto de `src/comunicados/*` ya usaba parámetros posicionales y no tenía casts SQL.

### Llamadas `fun_*` → `CALL proc_*` (procedimientos)

Varios endpoints/templates aún llamaban funciones que en MariaDB pasaron a ser
procedimientos. Se convirtieron a `CALL proc_*(?)` + `closeCursor()`:

- `src/sst/listar_comite.php` y `templates/sst.php`: `proc_listar_sst_comite_miembros`.
- `templates/sst.php`: `proc_listar_sst_quejas_usuario`.
- `templates/comunicados.php`: `proc_listar_comunicados_activos` (+ fix de `NULLS LAST` y
  `:id_usuario` en la consulta del cargo).
- `app/vacaciones_saldo.php` y `app/vacaciones_reporte.php`:
  `proc_calcular_saldo_vacaciones` y `proc_listar_vacaciones_ajustes`.

### Permisos / solicitudes (app/permiso_* y solicitud_permiso.php)

- `permiso_listar.php`: `:id_usuario` → `?`, `NULLS LAST` eliminado, `STRING_AGG(...)` →
  `GROUP_CONCAT(... SEPARATOR ', ')`.
- `permiso_detalle.php`: `:id_permiso` → `?` (4 consultas).
- `permiso_reporte.php`: `generate_series(...)` → `fun_dias_habiles_rango(...)`,
  `EXTRACT(EPOCH FROM ...)` → `TIME_TO_SEC(TIMEDIFF(...))`, `EXTRACT(DOW FROM ...)` →
  `DAYOFWEEK(...)`, `gs.dia::date` eliminado, `STRING_AGG` → `GROUP_CONCAT`, parámetros
  posicionales.
- `permiso_resolver.php`: `SELECT * FROM fun_resolver_permisos_aprobaciones(...)` →
  `CALL proc_resolver_permisos_aprobaciones(?, ?, ?, ?, ?, ?)` + `closeCursor()`.
- `solicitud_permiso.php`: `fun_insert_solicitudes_permisos` →
  `CALL proc_insert_solicitudes_permisos(?)`; los arrays PostgreSQL `{...}` ahora se envían
  como JSON (`json_encode`), `es_por_horas` como `1/0`; evidencias con parámetros
  posicionales + `closeCursor()`.

### Función SQL nueva (saldo de vacaciones escalar)

- [scripts/functions/vacaciones/fun_saldo_disponible_vacaciones.sql](scripts/functions/vacaciones/fun_saldo_disponible_vacaciones.sql):
  helper escalar que devuelve el saldo disponible (replica la política de
  `proc_calcular_saldo_vacaciones`).
- [scripts/functions/permisos_aprobaciones/proc_resolver_permisos_aprobaciones.sql](scripts/functions/permisos_aprobaciones/proc_resolver_permisos_aprobaciones.sql):
  `SET vsaldo = fun_calcular_saldo_vacaciones(...)` →
  `SET vsaldo = fun_saldo_disponible_vacaciones(...)`.

### Festivos / vacaciones / SST / empleado (resto de app/) y plantillas

- Parámetros posicionales en `app/empleado_ocupacion.php`, `app/festivos_listar.php`
  (`EXTRACT(YEAR FROM ...)` → `YEAR(...)`), `app/vacaciones_ajuste_insertar.php`,
  `app/vacaciones_ajuste_anular.php`, `app/vacaciones_ajuste_restaurar.php`,
  `app/sst_guardar_queja.php`, `app/sst_actualizar_queja.php`,
  `app/sst_cancelar_queja.php` y `app/vacaciones_reporte.php`.
- `NULLS LAST` eliminado en `app/empleados_listar.php` y `templates/empleados.php`.
- `:id_jefe` → `?` en `templates/bandeja_permisos.php` y
  `templates/partials/sidebar_nav.php`.
- `templates/ficha_tecnica.php`: `LEFT JOIN LATERAL (...)` reescrito como subconsulta
  correlacionada por PK (`id_contrato` / `id_nomina`) porque MariaDB no soporta `LATERAL`.

### Seeds no RBAC

- [scripts/inserts/dpts_municipios.sql](scripts/inserts/dpts_municipios.sql): eliminado
  `setval(...)` (secuencias de PostgreSQL); MariaDB sincroniza el `AUTO_INCREMENT`
  automáticamente al insertar IDs explícitos.
- [scripts/inserts/institutions_insert.sql](scripts/inserts/institutions_insert.sql):
  `BEGIN;` → `START TRANSACTION;` y
  `set_config('app.current_user', ...)` → `SET @app_current_user = ...`.
- [scripts/inserts/festivos_seed.sql](scripts/inserts/festivos_seed.sql): sin cambios
  (llama `fun_sembrar_festivos_colombia`).
- Fix en [fun_sembrar_festivos_colombia.sql](scripts/functions/dias_festivos/fun_sembrar_festivos_colombia.sql)
  y [proc_calcular_festivos_colombia.sql](scripts/functions/dias_festivos/proc_calcular_festivos_colombia.sql):
  `TRUNCATE TABLE` → `DELETE FROM` y se eliminó el `DROP TEMPORARY TABLE` dentro de la
  función (DDL con commit implícito no permitido en funciones).

### Corrección de esquema en la BD viva

- `t_verificacion_correo.id_registro_pendiente` tenía la FK **sin** `ON DELETE CASCADE` en
  la base viva (aunque `modelo_mariadb.sql` sí lo declara). Eso rompía el trigger
  `tri_limpiar_registros_pendientes`. Se aplicó:

  ```sql
  ALTER TABLE t_verificacion_correo DROP FOREIGN KEY `1`;
  ALTER TABLE t_verificacion_correo
      ADD CONSTRAINT fk_verificacion_pendiente
      FOREIGN KEY (id_registro_pendiente)
      REFERENCES t_registros_pendientes(id_registro_pendiente)
      ON DELETE CASCADE;
  ```

  El mismo cambio quedó reflejado en `scripts/modelo_mariadb.sql` (la FK ahora se declara
  como `CONSTRAINT fk_verificacion_pendiente ... ON DELETE CASCADE`), para que el archivo
  fuente y la BD viva coincidan. Si la BD se recrea desde `modelo_mariadb.sql` no hace
  falta el ALTER.

---

## 4. Configuración del entorno (importante)

En `C:\php\php.ini` se habilitaron las extensiones que requiere la app:

- `pdo_mysql` — driver PDO para MariaDB.
- `mbstring` — `InputSanitizer` y validaciones de texto.
- `curl` — Turnstile y almacenamiento de evidencias.
- `fileinfo` — validación MIME de evidencias.
- `gd`, `intl`, `openssl`, `zip` — PhpSpreadsheet, dompdf y PHPMailer.

**Reiniciar Apache** después de cualquier cambio en `php.ini` para que cargue las
extensiones. Requiere PowerShell como administrador:

```powershell
Restart-Service -Name 'Apache2.4' -Force
# o desde la carpeta de Apache
C:\Apache24\bin\httpd.exe -k restart
```

> Sin `pdo_mysql`, el login devuelve "No se pudo establecer conexión con la base de datos".
> Sin `mbstring`, aparece "Call to undefined function mb_strtolower()" en
> `app/helpers/InputSanitizer.php`.

### Enrutamiento (raíz del servidor)

`DocumentRoot` apunta a `C:/Apache24/htdocs/dlgc_rrhh` (el proyecto **es** la raíz del
sitio). Por eso las URLs deben ser `/templates/...`, `/src/...`, `/app/...` **sin** el
prefijo `/dlgc_rrhh/`:

```text
http://localhost/templates/login.php          (correcto)
http://localhost/src/cajacompensacion/...     (correcto)
http://localhost/dlgc_rrhh/src/...            (404, NO usar)
```

Se corrigieron los enlaces que aún incluían `/dlgc_rrhh/` (sidebars, páginas legales,
cookie banner, `assets/js/*.js`, `src/sst/pie_sst.php` y los `ErrorDocument` de
`.htaccess`).

---

## 5. Usuario administrador

`rbac_seeds.sql` no crea usuarios; solo roles, módulos, operaciones y asignaciones. Crear el
primer usuario con `password_hash()` de PHP:

```php
php -r "echo password_hash('TU_CLAVE', PASSWORD_DEFAULT), PHP_EOL;"
```

```sql
INSERT INTO t_usuarios
    (id_usuario, id_rol, username, tipo_documento, primer_nombre, primer_apellido,
     correo, correo_verificado, contrasena)
VALUES
    ('1234567890', 1, 'admin', 'CC', 'Admin', 'General',
     'admin@dlgc.com', TRUE, '<hash_generado>');
```

> Para las pruebas de esta fase se creó un usuario `admin` con clave temporal
> `Admin123*`. **Cambiarla en producción.**

### Creación rápida con seed

Para pruebas rápidas existe [scripts/inserts/seed_admin_rbac.sql](scripts/inserts/seed_admin_rbac.sql):
crea/restaura el usuario `admin` (`admin` / `Admin123*`, rol 1) y le asigna **todos** los
permisos directos en `t_usuarios_operaciones`. Es idempotente:

```powershell
Get-Content -Raw 'scripts\inserts\seed_admin_rbac.sql' | & 'C:\Program Files\MariaDB 13.0\bin\mariadb.exe' -h 127.0.0.1 -P 3306 -u root -p db_dlgc_rrhh
```

---

## 6. Verificación realizada

- `php -l` sin errores en todos los archivos PHP editados.
- SQL directo contra MariaDB:
  - `CALL proc_login_usuarios('admin')` devuelve el registro esperado.
  - Consulta de permisos por rol con `MAX(...)` devuelve los 28 módulos con sus 4 banderas.
  - 23 operaciones CRUD RBAC (`insert/update/softdelete/restore` de los 6 dominios) en una
    transacción con `ROLLBACK` — todas correctas.
  - Triggers de auditoría rellenan `usr_*`/`fec_*` con `fun_audit_actor()` y el trigger de
    soft-delete bloquea `DELETE` físico (error 1644).
- CLI PHP con `pdo_mysql`:
  - Conexión + `CALL proc_login_usuarios` + `password_verify` → OK.
  - `load_user_session()` carga 28 módulos en `$_SESSION['permisos']` con
    `permiso_crear/actualizar/eliminar/restaurar` correctos.
- Flujo de registro/verificación/recuperación contra MariaDB (en transacción con
  `ROLLBACK`):
  - Validación de código de registro temporal → OK.
  - Lookup de token de verificación + `fun_insert_usuarios` → `OK`.
  - Marcar token como usado dispara `tri_limpiar_registros_pendientes` y elimina el
    registro pendiente (tras el fix de `ON DELETE CASCADE`).
  - Lookup y marcado de token de recuperación de contraseña → OK.
- SRC restante:
  - `php -l` limpio en `src/comunicados/visto_por.php` y `src/sst/listar_quejas.php`.
  - `CALL proc_listar_sst_quejas_encargado('')` ejecuta sin error (buzón vacío).
  - Grep de patrones PostgreSQL sobre `src/` → 0 coincidencias.
- Dashboard (`firstpage.php`) contra MariaDB:
  - `CALL proc_calcular_saldo_vacaciones('1234567890')` devuelve la rama "sin empleado"
    (`anios_servicio = 0`) sin error.
  - Consultas de incapacidades, novedades (`INTERVAL 7 DAY`), últimos trámites
    (`GROUP_CONCAT`, `TIME_TO_SEC(TIMEDIFF(...))`) y el CTE de trámites de empresa
    (`CONCAT`, `DATE(...)`) — todas ejecutan correctamente.
  - `fun_dias_habiles_rango('2026-10-12', '2026-10-16')` = 5.
- Permisos / solicitudes contra MariaDB:
  - `php -l` limpio en los 5 archivos `app/permiso_*` + `solicitud_permiso.php`.
  - `fun_saldo_disponible_vacaciones('1234567890')` = `0.00`.
  - Consultas de `permiso_listar` (`GROUP_CONCAT`) y `permiso_reporte`
    (`fun_dias_habiles_rango`, `TIME_TO_SEC`, `DAYOFWEEK`) ejecutan sin error.
  - `CALL proc_resolver_permisos_aprobaciones(999999, 'JEFE', ...)` devuelve el error
    controlado "No existe ese nivel..." (ya no falla por `fun_calcular_saldo_vacaciones`).
- Resto de `app/` + plantillas:
  - `php -l` limpio en los 14 archivos editados (festivos, vacaciones_ajuste, sst,
    empleado_ocupacion, empleados_listar, ficha_tecnica, empleados, bandeja_permisos,
    sidebar_nav, vacaciones_reporte).
  - Grep de patrones PostgreSQL sobre todo el proyecto PHP → solo falsos positivos
    (`InputSanitizer::text`); 0 restos SQL reales.
- Seeds no RBAC:
  - `fun_sembrar_festivos_colombia(2026)` → "18 festivo(s) insertado(s)" (en transacción).
  - `fun_insert_banco(...) FROM (VALUES ...)` → "Banco insertado correctamente."
  - `dpts_municipios.sql` carga sin error (en transacción con `ROLLBACK`).

---

## 7. Pendiente

La capa PHP (`app/`, `src/`, `templates/`) y los seeds ya no tienen restos de PostgreSQL.
Queda solo documentación/histórico:

- **Migraciones:** `scripts/migrations/*.sql` siguen en sintaxis PostgreSQL (histórico; ya
  no se aplican sobre la BD actual).
- **Documentación:** `scripts/SETUP_RBAC.md` y `scripts/SETUP_DATABASE.md` aún usan
  comandos `psql`; actualizarlos a `mariadb`.

### Nota de compatibilidad

`scripts/inserts/rbac_seeds.sql` (original PostgreSQL) se conserva intacto. Para MariaDB usar
siempre `scripts/inserts/rbac_seeds_mariadb.sql`.

---

## 8. Módulos de pruebas

Después de reiniciar Apache, ejecutar estos pasos en orden. Se asume el usuario `admin`
creado en la sección 5.

### Módulo 1 — Login y RBAC

1. Abrir `http://localhost/templates/login.php`.
2. Iniciar sesión con `admin` y su clave.
   - **Esperado:** redirige a `http://localhost/templates/firstpage.php` (sin error 500).
3. Verificar que el sidebar muestra los módulos de administración (Usuarios, Roles, Módulos,
   Operaciones, Roles Operaciones, Permisos por Usuario), porque el rol 1 tiene todo.
4. Abrir `http://localhost/src/usuarios/listar_usuarios.php`.
   - **Esperado:** lista el usuario `admin` con su rol.
5. Crear un rol de prueba: `src/roles/forma_roles.php` → `id_rol=99`, `nombre_rol=QA`.
   - **Esperado:** redirige a `listar_roles.php?success=...` y el rol aparece como activo.
6. Editar, eliminar (soft delete) y restaurar ese rol.
   - **Esperado:** en cada paso el mensaje de éxito y el badge "Activos/Eliminados" cambia.
7. Cerrar sesión (`../app/logout.php`) e intentar abrir `src/usuarios/listar_usuarios.php`.
   - **Esperado:** redirige al login.

### Módulo 2 — Dashboard

1. Con sesión de `admin`, abrir `http://localhost/templates/firstpage.php`.
   - **Esperado:** la página renderiza las tarjetas "Vacaciones Disponibles",
     "Incapacidades este año" y "Solicitudes en Proceso" con valores numéricos (0 o más).
2. Verificar que aparece "Empleados Activos" y "Novedades Reportadas" (módulo 4/27 del admin).
3. Verificar que la tabla "Últimos Trámites e Incapacidades (TÚ)" muestra
   "No hay trámites recientes" si no hay datos, sin errores SQL.
4. Abrir el segundo slide "Últimos Trámites y Afiliaciones de la Empresa" (flechas del
   carrusel) — visible para admin.
   - **Esperado:** muestra "No hay trámites recientes" sin error.

> Para validar datos reales, crear un empleado + solicitud + afiliación y repetir el
> Módulo 2; las tarjetas deben reflejar los conteos.

### Módulo 3 — Registro, verificación de correo y recuperación

1. Con `admin`, abrir `http://localhost/templates/codigos_registro.php`.
   - **Esperado:** la página carga y muestra el código temporal y el código único activos.
2. Generar un código temporal (botón de generar).
   - **Esperado:** aparece el mensaje con el código de 4 dígitos y su vencimiento.
3. Cerrar sesión y, en `login.php`, ir a la pestaña **Registrarse** e ingresar ese código.
   - **Esperado:** avanza al formulario (paso 2) sin error.
4. Llenar el formulario (documento CC de 10 dígitos, usuario en minúsculas, correo y clave).
   - **Esperado:** redirige al login con `code=registro_pendiente_verificacion`.
   - **Verificación en BD:** debe existir la fila en `t_registros_pendientes` y su token en
     `t_verificacion_correo`.
5. Verificar el correo con el enlace recibido. Si el SMTP no está configurado, usar un token
   manual:
   ```sql
   SELECT id_registro_pendiente FROM t_registros_pendientes ORDER BY id_registro_pendiente DESC LIMIT 1;
   INSERT INTO t_verificacion_correo
       (id_registro_pendiente, token_hash, fecha_expiracion, usr_insert, fec_insert)
   VALUES (<id>, SHA2('mytoken', 256), NOW() + INTERVAL 1 DAY, 'test', NOW());
   ```
   y abrir `http://localhost/app/verificar_correo.php?token=mytoken`.
   - **Esperado:** redirige al login con `code=correo_verificado`; la fila de
     `t_registros_pendientes` desaparece (la limpia el trigger) y el usuario ya existe en
     `t_usuarios` con `correo_verificado = TRUE`.
6. Iniciar sesión con el usuario recién verificado (rol EMPLEADO).
   - **Esperado:** entra al dashboard sin los módulos de administración en el sidebar.
7. Cerrar sesión y, en **¿Olvidaste tu contraseña?**, solicitar recuperación con el correo.
   - **Esperado:** redirige con `code=solicitud_procesada`.
8. Abrir el enlace del correo (o forzar un token igual que en el paso 5, pero en
   `t_recuperacion_contrasena`) y definir una nueva clave.
   - **Esperado:** redirige al login con `code=contrasena_actualizada`; la nueva clave
     funciona y la anterior ya no.

### Módulo 4 — Comunicados (SRC) y Buzón SST (SRC)

1. Con `admin`, abrir `http://localhost/src/comunicados/listar_comunicados.php`.
   - **Esperado:** carga el listado de comunicados sin error SQL.
2. Crear un comunicado (`forma_comunicados.php`) y guardarlo.
   - **Esperado:** redirige con `success` y aparece en el listado.
3. Editar, eliminar (soft delete) y restaurar ese comunicado.
   - **Esperado:** cada acción muestra su mensaje y cambia el contador Activos/Eliminados.
4. Con `admin`, abrir `http://localhost/src/sst/listar_quejas.php`.
   - **Esperado:** carga el buzón (vacío muestra "No hay solicitudes para mostrar") sin
     error; con datos, muestra las quejas y el selector de filtro por estado funciona.
