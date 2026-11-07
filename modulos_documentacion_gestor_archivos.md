# Módulos Documentación y Gestor de Archivos

Documentación técnica de los módulos **Documentación (ID 31)** y **Gestor de Archivos (ID 30)** del portal DLGC_RRHH: modelo de datos, procedimientos almacenados, endpoints, RBAC, frontend, despliegue y pruebas.

---

## 1. Resumen ejecutivo

| Aspecto | Documentación (31) | Gestor de Archivos (30) |
|---|---|---|
| Naturaleza | Módulo transversal: visible para **todos** los usuarios autenticados desde el sidebar, sin asignación RBAC previa | Módulo restringido por RBAC estándar (`auth_guard` + `t_operaciones`/`t_roles_operaciones`) |
| Operaciones permitidas | **Solo VER e INSERTAR** (no existen edición ni eliminación) | VER / CREAR / ACTUALIZAR / ELIMINAR / RESTAURAR |
| Función | Registro documental del empleado (contrato, certificados, etc.) | Control de versiones de los archivos físicos: archivo **Vigente** + **Históricos** |
| Vistas | `templates/documentacion.php` | `templates/gestor_archivos.php` |
| Backend | `app/doc_guardar.php`, `app/doc_listar.php` | `app/gestor_listar.php`, `app/gestor_historial.php`, `app/gestor_subir.php`, `app/gestor_descargar.php` |
| Frontend | `assets/js/documentacion.js`, `assets/css/documentacion.css` | `assets/js/gestor_archivos.js`, `assets/css/gestor_archivos.css` |

Regla central del Gestor: subir una nueva versión **nunca sobrescribe** el registro anterior. Cada subida inserta una fila nueva en `t_documentos_versiones`; la versión vigente queda marcada con `es_actual = 1` y las anteriores pasan automáticamente al histórico.

---

## 2. Modelo de datos

Las tablas ya están definidas en [scripts/modelo_mariadb.sql](scripts/modelo_mariadb.sql) (sección "MÓDULO DE DOCUMENTOS RRHH").

### 2.1 `t_tipos_documento_rrhh` (catálogo)

| Columna | Tipo | Notas |
|---|---|---|
| `id_tipo_documento` | INT AUTO_INCREMENT | PK |
| `codigo` | VARCHAR(30) UNIQUE | Ej: `CONTRATO`, `CERTIFICADO_EPS` |
| `nombre` | VARCHAR(80) | Etiqueta visible |
| + columnas de auditoría | | `usr_*` / `fec_*` |

Semillas:
- 4 tipos en el propio modelo (`CONTRATO`, `CERTIFICADO_EPS`, `CERTIFICADO_ARL`, `HOJA_VIDA`).
- 7 tipos adicionales idempotentes en [scripts/inserts/documentos_seeds.sql](scripts/inserts/documentos_seeds.sql) (`CERTIFICADO_LABORAL`, `OTROSI`, `AUTORIZACION_AFILIACION`, `CERTIFICADO_BANCARIO`, `CERTIFICADO_PENSION`, `CERTIFICADO_CAJA`, `OTRO`).

### 2.2 `t_documentos_rrhh` (contenedor lógico del documento)

| Columna | Tipo | Notas |
|---|---|---|
| `id_documento` | INT AUTO_INCREMENT | PK |
| `id_usuario` | VARCHAR(20) | Empleado dueño (FK a `t_empleados`) |
| `id_tipo_documento` | INT | FK a `t_tipos_documento_rrhh` |
| `id_contrato` | INT NULL | Opcional; FK compuesta `(id_contrato, id_usuario)` a `t_contratos_empleados` |
| `titulo` | VARCHAR(150) | |
| `estado` | VARCHAR(20) | CHECK: `ACTIVO` / `INACTIVO` / `ARCHIVADO` |

> La FK compuesta exige que `t_contratos_empleados` tenga `UNIQUE (id_contrato, id_usuario)` (constraint `uq_contrato_usuario`, declarada en el modelo actual).

### 2.3 `t_documentos_versiones` (archivos físicos y versionado)

| Columna | Tipo | Notas |
|---|---|---|
| `id_version` | INT AUTO_INCREMENT | PK |
| `id_documento` | INT | FK a `t_documentos_rrhh` |
| `numero_version` | INT NULL | NULL mientras esté PENDIENTE/RECHAZADA; 1, 2, 3… al aprobar |
| `proveedor` | VARCHAR(20) | CHECK: `LOCAL`, `R2` (default `LOCAL`) |
| `storage_key` | VARCHAR(255) UNIQUE | Ruta relativa del archivo (ej: `documentos/5/ab12cd34.pdf`) |
| `nombre_original` | VARCHAR(255) | Nombre con el que se subió |
| `mime_type` | VARCHAR(100) | CHECK: `application/pdf`, `image/jpeg`, `image/png` |
| `tamano_bytes` | INT | CHECK: > 0 y ≤ 10 485 760 (10 MB) |
| `hash_sha256` | CHAR(64) | Integridad y detección de duplicados |
| `es_actual` | BOOLEAN | Máximo una versión vigente por documento (`uq_version_actual` vía columna virtual `actual_unico`) |
| `observacion` | VARCHAR(255) NULL | Ej: "Se actualiza por Otro Sí" |
| `estado_aprobacion` | VARCHAR(20) | CHECK `PENDIENTE`/`APROBADO`/`RECHAZADO` con coherencia total (`chk_versiones_estado`) |
| `id_aprobador` | VARCHAR(20) | FK a `t_usuarios` |

Regla de negocio del esquema (respeta el CHECK `chk_versiones_estado`):
- `PENDIENTE` / `RECHAZADO`: `numero_version = NULL`, `es_actual = 0`.
- `APROBADO`: `numero_version` asignado, `id_aprobador` y `fec_resolucion` no nulos.

En esta implementación, **la subida aprueba en el acto** (el usuario con permiso de subir es el aprobador), manteniendo la invariante de una sola versión vigente.

### 2.4 Triggers (auditoría, soft delete y borrado físico)

| Trigger | Archivo | Efecto |
|---|---|---|
| `tri_audit_<tabla>_ins` / `tri_audit_<tabla>_upd` (para las 3 tablas de documentos) | [scripts/triggers/auditorias.sql](scripts/triggers/auditorias.sql) | BEFORE INSERT/UPDATE: completan automáticamente `usr_insert`/`fec_insert` y `usr_update`/`fec_update` con `fun_audit_actor()` (`@app_current_user`, con fallback a `CURRENT_USER`) |
| `tri_soft_delete_<tabla>` (para las 3 tablas de documentos) | [scripts/triggers/softdelete.sql](scripts/triggers/softdelete.sql) | BEFORE DELETE: bloquean el borrado físico con `SIGNAL SQLSTATE '45000'` (mismo patrón que el resto del proyecto) |
| — | [scripts/triggers/borrado_fisico.sql](scripts/triggers/borrado_fisico.sql) | **No aplica** a estas tablas (ver nota en §10.7) |

> El rechazo de una versión por RRHH **no es un borrado**: es un cambio de estado a `RECHAZADO` que conserva `motivo_rechazo`, `id_aprobador` y `fec_resolucion` (lo exige el CHECK `chk_versiones_estado`). Además, MariaDB prohíbe que un trigger borre filas de su propia tabla (error 1442), por lo que el patrón de "borrado físico al rechazar" no es viable ni deseable: se perdería la trazabilidad.

---

## 3. Procedimientos almacenados

> **Cero SQL plano**: todo el acceso a datos de ambos módulos se hace exclusivamente con `fun_*`/`proc_*` a través de `app/conexion.php` (PDO, `ATTR_EMULATE_PREPARES = false`, sentencias preparadas).
>
> Los parámetros de texto declaran `COLLATE utf8mb4_unicode_ci` explícito para evitar el error 1267 "Illegal mix of collations" cuando el servidor/BD usa `utf8mb4_uca1400_ai_ci` (ver §10).

### 3.1 `scripts/functions/documentos_rrhh/`

| Objeto | Firma | Descripción |
|---|---|---|
| [fun_insert_documentos_rrhh.sql](scripts/functions/documentos_rrhh/fun_insert_documentos_rrhh.sql) | `(wid_usuario, wid_tipo_documento, wid_contrato NULL, wtitulo) → VARCHAR(255)` | Inserta un registro `ACTIVO`. Valida: usuario sea empleado activo, tipo existente y contrato (si viene) pertenezca al mismo empleado. Devuelve mensaje de éxito o error amigable. |
| [proc_listar_documentos_usuario.sql](scripts/functions/documentos_rrhh/proc_listar_documentos_usuario.sql) | `IN wid_usuario VARCHAR(20)` | Documentos del empleado + nombre del tipo + datos de la versión vigente (LEFT JOIN). |
| [proc_listar_tipos_documento.sql](scripts/functions/documentos_rrhh/proc_listar_tipos_documento.sql) | — | Catálogo de tipos activos. |

### 3.2 `scripts/functions/contratos_empleados/` (adicional)

| Objeto | Firma | Descripción |
|---|---|---|
| [proc_listar_contratos_usuario.sql](scripts/functions/contratos_empleados/proc_listar_contratos_usuario.sql) | `IN wid_usuario VARCHAR(20)` | Contratos vigentes del empleado (para el select del formulario de Documentación). |

### 3.3 `scripts/functions/documentos_versiones/`

| Objeto | Firma | Descripción |
|---|---|---|
| [proc_subir_version_documento.sql](scripts/functions/documentos_versiones/proc_subir_version_documento.sql) | 9 IN params → fila `resultado` | **Núcleo del versionado**. Transaccional: ① valida parámetros; ② `SELECT … FOR UPDATE` sobre el documento (serializa la numeración); ③ rechaza duplicados por `hash_sha256`; ④ `UPDATE es_actual = 0` a la versión anterior; ⑤ `MAX(numero_version) + 1`; ⑥ INSERT como `APROBADO` con `es_actual = 1`; ⑦ `COMMIT`. Cualquier error hace `ROLLBACK` y devuelve mensaje. (Es PROCEDIMIENTO porque MariaDB no permite `COMMIT/ROLLBACK` en funciones.) |
| [proc_listar_versiones_documento.sql](scripts/functions/documentos_versiones/proc_listar_versiones_documento.sql) | `IN wid_documento INT` | Historial completo (todas las versiones no borradas), vigente primero. |
| [proc_listar_documentos_gestor.sql](scripts/functions/documentos_versiones/proc_listar_documentos_gestor.sql) | — | Todos los documentos con empleado (nombres), tipo, versión vigente y `total_versiones`. |
| [proc_obtener_version_archivo.sql](scripts/functions/documentos_versiones/proc_obtener_version_archivo.sql) | `IN wid_version INT` | Fila única de la versión (usada por la descarga). |

### 3.4 Aplicación de los SPs

Se aplican con el mecanismo estándar del proyecto (detecta las carpetas nuevas automáticamente):

```powershell
.\scripts\rebuild_functions.ps1          # Windows (pide la contraseña una vez)
# o
./scripts/rebuild_functions.sh           # Linux/macOS
```

---

## 4. Endpoints backend (`app/`)

Todos incluyen `session_bootstrap` + `conexion` + `auth_guard`. Las mutaciones exigen CSRF (`csrf_validate`) y toda entrada pasa por [InputSanitizer](app/helpers/InputSanitizer.php). Respuestas en JSON (`success`, `message`).

| Endpoint | Método | Acceso | Validaciones / lógica | SP utilizado |
|---|---|---|---|---|
| [doc_guardar.php](app/doc_guardar.php) | POST | Autenticado (transversal) | CSRF; `intValue` (tipo, contrato), `boundedText` (título 3–150). Registra el documento **del usuario en sesión** (nadie puede crear documentos a nombre de otro). | `fun_insert_documentos_rrhh` |
| [doc_listar.php](app/doc_listar.php) | GET | Autenticado (transversal) | Lista los documentos del usuario en sesión. | `proc_listar_documentos_usuario` |
| [gestor_listar.php](app/gestor_listar.php) | GET | `has_module_access(30)` | Lista documentos + archivo vigente. | `proc_listar_documentos_gestor` |
| [gestor_historial.php](app/gestor_historial.php) | GET | `has_module_access(30)` | `id_documento` validado con `intValue`. | `proc_listar_versiones_documento` |
| [gestor_subir.php](app/gestor_subir.php) | POST (multipart) | `has_module_access(30)` + `has_module_permission(30,'crear')` | CSRF; archivo: error de subida, tamaño 1 B–10 MB, **MIME real con `finfo`** (solo PDF/JPG/PNG/WebP), nombre saneado (`basename`), SHA-256. Comprime antes de subir (Fase 4): imágenes con GD (máx. 1600 px, JPEG q80; PNG con alpha se conserva) y PDFs con Ghostscript (perfil `/ebook`; si no logra ≥15% de ahorro reintenta con `/screen`; si aún así no hay ahorro, sube el original). Con `provider=R2` en `config/storage_config.php` sube a R2 con llave `documentos/<id_documento>/<token>.<ext>`; si R2 no está configurado usa el disco (`uploads/documentos/...`, proveedor `LOCAL`). Registra con el usuario en sesión como aprobador. Si el SP falla, elimina el archivo del almacenamiento (rollback). | `proc_subir_version_documento` |
| [gestor_descargar.php](app/gestor_descargar.php) | GET | `has_module_access(30)` | `id_version` con `intValue`. La ruta **nunca** viene del cliente: se resuelve desde la DB, se valida con `realpath()` que quede dentro de `uploads/`, se re-verifica el SHA-256 y se sirve con `Content-Disposition: attachment` + `X-Content-Type-Options: nosniff`. | `proc_obtener_version_archivo` |

### Ejemplo de flujo

```
POST /app/gestor_subir.php            (FormData: csrf_token, id_documento, archivo, observacion)
  → valida CSRF y permiso "crear" del módulo 30
  → valida MIME/tamaño/hash
  → mueve el archivo a uploads/documentos/<id>/<token>.pdf
  → CALL proc_subir_version_documento(...)   ← transacción: desmarca la vigente y numera MAX+1
  → responde { success: true, message: "Versión 2 subida correctamente." }

GET /app/gestor_historial.php?id_documento=5
  → { success: true, versions: [ { v2, es_actual: "1" }, { v1, es_actual: "0" }, … ] }
```

---

## 5. Vistas y frontend

### 5.1 `templates/documentacion.php`

- Layout dashboard estándar (sidebar + top-bar + `dashboard-body`), coherente con `sst.php`/`vacaciones.php`.
- Tabla "Mis documentos": tipo, título, contrato, estado, **archivo vigente** (nombre + versión) y fecha. **Sin botones de editar ni eliminar**.
- Modal "Registrar documento" (tipo, título, contrato opcional). El propietario siempre es el usuario en sesión.
- El backend no pide `require_module_access(31)`: es transversal por diseño.

### 5.2 `templates/gestor_archivos.php`

- Requiere `require_module_access(30)`; expone en `data-puede-crear` si el usuario tiene permiso de crear.
- Tabla principal: documento (título + tipo), empleado, **archivo vigente** (nombre, tamaño, fecha, enlace Descargar), contador de versiones y acciones.
- Acciones por fila:
  - **Ver versiones** → abre el modal de historial (carga por AJAX).
  - **Subir versión** (solo con permiso `crear`) → modal con `<input type="file">` y observación opcional.
- Modal de historial: una tarjeta por versión con badge **VIGENTE** / `vN`, nombre, tamaño, fecha, estado de aprobación, observación y enlace de descarga.

### 5.3 Assets

| Archivo | Responsabilidad |
|---|---|
| [assets/js/documentacion.js](assets/js/documentacion.js) | Abrir/cerrar modal, `POST` con `FormData` + CSRF a `doc_guardar.php`, refresco de la tabla vía `doc_listar.php`. El DOM se construye con `createElement`/`textContent` (sin inyección de HTML). |
| [assets/js/gestor_archivos.js](assets/js/gestor_archivos.js) | Delegación de eventos en la tabla, carga del historial (`gestor_historial.php`), subida multipart (`gestor_subir.php`), refresco de tabla e historial tras cada subida. |
| [assets/css/documentacion.css](assets/css/documentacion.css) | Tabla, badges, botones, modal y alertas del módulo 31 (usa variables de tema de `firstpage.css`). |
| [assets/css/gestor_archivos.css](assets/css/gestor_archivos.css) | Estilos de la tabla, badges VIGENTE/histórica, modales y tarjetas del historial (módulo 30). |

### 5.4 Sidebar

Hay **dos** partials de sidebar (el dashboard elige según el rol en `firstpage.php` y `ficha_tecnica.php`):

- [templates/partials/sidebar_nav.php](templates/partials/sidebar_nav.php) — empleados y vistas generales:
  - **Documentación**: entrada estática renderizada para todos los usuarios autenticados (sin `has_module_access`), con icono propio.
  - **Gestor de Archivos**: agregado a `$modulosAdminSueltos`, visible solo con `has_module_access(30)`.
- [templates/partials/sidebar_admin.php](templates/partials/sidebar_admin.php) — el sidebar que ve el **ADMINISTRADOR** (`id_rol = 1`) en el dashboard y en `ficha_tecnica.php`:
  - **Documentación**: entrada estática, igual que en `sidebar_nav`.
  - **Gestor de Archivos**: en el grupo "Administración", visible con `has_module_access(30)`.

> Regla para mantenimiento futuro: cualquier módulo nuevo debe agregarse a **ambos** partials, porque el admin nunca ve `sidebar_nav.php`.

---

## 6. RBAC

Actualizado en [scripts/inserts/rbac_seeds_mariadb.sql](scripts/inserts/rbac_seeds_mariadb.sql) y [scripts/inserts/seed_admin_rbac.sql](scripts/inserts/seed_admin_rbac.sql).

| Módulo | ID | Operaciones (id_operacion = id_modulo × 10 + n) |
|---|---|---|
| Gestor de Archivos | 30 | VER (301), CREAR (302), ACTUALIZAR (303), ELIMINAR (304), RESTAURAR (305) |
| Documentación | 31 | **VER (311) y CREAR (312) únicamente** — por regla de negocio no se crean 313/314/315 |

Asignaciones:

- **ADMINISTRADOR (rol 1)**: todas las operaciones de 30 y 31 (cubiertas por el `INSERT … SELECT` genérico de la sección 4 y por bloques explícitos idempotentes añadidos).
- **EMPLEADO (rol 2)**: 311 (VER) y 312 (CREAR) de Documentación. **No** recibe nada del módulo 30.
- `seed_admin_rbac.sql` incluye un bloque explícito que garantiza los permisos directos del admin sobre 30/31 en aplicaciones incrementales.

---

## 7. Almacenamiento de archivos

- **Proveedor `R2` (activo por defecto en producción)**: los archivos se suben al bucket privado con llave `documentos/<id_documento>/<token_aleatorio>.<ext>` y se descargan vía `gestor_descargar.php` (misma verificación SHA-256). El bucket es privado: no hay URL pública.
- **Proveedor `LOCAL` (desarrollo/legacy)**: los archivos se guardan en el disco del servidor bajo `uploads/documentos/<id_documento>/<token_aleatorio>.<ext>` (directorio creado automáticamente por el endpoint; `/uploads/` ya está en `.gitignore`).
- En la base de datos solo se persiste la **referencia** (`storage_key`, ruta relativa) más `nombre_original`, `mime_type`, `tamano_bytes` y `hash_sha256`.
- El CHECK de `proveedor` quedó en `('LOCAL', 'R2')` con default `LOCAL`, en [scripts/modelo_mariadb.sql](scripts/modelo_mariadb.sql). Para bases existentes, [scripts/inserts/alter_storage_r2.sql](scripts/inserts/alter_storage_r2.sql) trae el `ALTER TABLE` equivalente (idempotente).
- Migrar a R2 en el futuro no requiere tocar el modelo ni las vistas: basta con cambiar el proveedor al subir y el almacenamiento de `storage_key`.

### Descarga segura

1. `id_version` validado como entero.
2. La fila se busca por SP; `mime_type` debe estar en la lista blanca.
3. `realpath(uploads/<storage_key>)` debe quedar **dentro** de `uploads/` (anti path-traversal; la ruta nunca la aporta el cliente).
4. Se recalcula el SHA-256 del archivo y se compara con `hash_equals` contra la columna.
5. Se sirve con `Content-Disposition: attachment`, `Content-Length`, `X-Content-Type-Options: nosniff` y `Cache-Control: private, no-store`.

---

## 8. Seguridad

| Control | Dónde |
|---|---|
| Autenticación/sesión, user-agent e inactividad | `app/auth_guard.php` (común a todo el proyecto) |
| CSRF en todas las mutaciones (POST) | `csrf_validate()` en `doc_guardar.php` y `gestor_subir.php`; `csrf_input()` en ambos formularios y token en `data-csrf` para los envíos Fetch |
| RBAC del módulo 30 | `has_module_access(30)` + `has_module_permission(30, 'crear')` |
| Transversalidad controlada del 31 | Sin `require_module_access`, pero el propietario del documento siempre es `$_SESSION['id_usuario']` (sin IDOR) |
| Inyección SQL | Solo `fun_*`/`proc_*` mediante PDO con `ATTR_EMULATE_PREPARES = false` |
| Validación de entradas | `InputSanitizer::intValue`, `boundedText`, whitelist de MIME con `finfo`, tamaño, nombre con `basename` |
| Integridad | SHA-256 calculado al subir y re-verificado al descargar; rechazo de duplicados en el SP |
| XSS en el frontend | Render del DOM exclusivamente con `createElement`/`textContent` (sin `innerHTML` con datos) |
| Auditoría en BD | Triggers `tri_audit_*` en las 3 tablas: los campos `usr_*`/`fec_*` los completa la BD (nadie los manipula desde la app) |
| Borrado físico | Bloqueado por triggers `tri_soft_delete_*` (`SIGNAL 45000`) en las 3 tablas |

---

## 9. Despliegue

### 9.1 Instalación desde cero

Orden recomendado (idempotente):

```sql
-- 1. Tablas (si no existen): extraer la sección "MÓDULO DE DOCUMENTOS RRHH" de scripts/modelo_mariadb.sql
--    o reconstruir la base con el modelo completo.
--    Requisito previo: t_contratos_empleados debe tener UNIQUE (id_contrato, id_usuario).

-- 2. Catálogo de tipos + proveedor LOCAL (bases existentes):
source scripts/inserts/documentos_seeds.sql;

-- 3. Módulos, operaciones y roles:
source scripts/inserts/rbac_seeds_mariadb.sql;

-- 4. Permisos directos del admin (opcional, si se usa ese mecanismo):
source scripts/inserts/seed_admin_rbac.sql;
```

```powershell
# 5. Procedimientos almacenados (detecta las carpetas nuevas):
.\scripts\rebuild_functions.ps1
```

- Permisos del servidor: el usuario de Apache debe poder **escribir** en `uploads/` (Linux: `chown www-data:www-data uploads`). El subdirectorio `uploads/documentos/` se crea solo.
- No hay build de frontend (HTML/CSS/JS vanilla), igual que el resto del proyecto.

### 9.2 Aplicación rápida sobre una base existente (ALTERs)

[scripts/inserts/alter_tablas_documentos.sql](scripts/inserts/alter_tablas_documentos.sql) contiene todo lo que necesita una base existente sin reconstruir el modelo:

```sql
source scripts/inserts/alter_tablas_documentos.sql;
```

El archivo incluye (idempotente):
1. **Collation**: `ALTER DATABASE CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;` — corrige el error 1267 que rompe el login (ver §10.1).
2. `CREATE TABLE IF NOT EXISTS` de las 3 tablas de documentos.
3. `UNIQUE (id_contrato, id_usuario)` en `t_contratos_empleados` (con guardián de duplicidad).
4. `proveedor='LOCAL'` en `t_documentos_versiones`.

Y **después**, en este orden:

```powershell
.\scripts\rebuild_functions.ps1        # OBLIGATORIO tras el cambio de collation
```

```sql
source scripts/inserts/rbac_seeds_mariadb.sql;
source scripts/inserts/documentos_seeds.sql;
```

> Si la base fue reconstruida con `modelo_mariadb.sql` (que hace DROP+CREATE), también hay que volver a aplicar **triggers** y el **seed del admin**, o el login fallará por otras razones:
>
> ```sql
> source scripts/triggers/auditorias.sql;
> source scripts/triggers/softdelete.sql;
> source scripts/triggers/borrado_fisico.sql;
> source scripts/inserts/seed_admin_rbac.sql;   -- usuario: admin / clave: Admin123*
> ```

---

## 10. Notas y consideraciones

1. **Collation (causa del "error del servidor" en login)**: si la base/servidor usa `utf8mb4_uca1400_ai_ci` (default de MariaDB 13) y las tablas `utf8mb4_unicode_ci`, al recrear rutinas con `rebuild_functions.ps1` sus parámetros VARCHAR heredan la collation de la BD y `WHERE columna = param` lanza **ERROR 1267** (afecta a `proc_login_usuarios`, por eso `login.php` responde `error_servidor`). **Solución** (ya aplicada en el ambiente local):
   ```sql
   ALTER DATABASE CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```
   seguido de `.\scripts\rebuild_functions.ps1`. Ver §9.2. Los SPs de estos módulos declaran además `COLLATE utf8mb4_unicode_ci` explícito en sus parámetros de texto, por lo que funcionan incluso en bases con otro default.
2. **`proc_subir_version_documento` es procedimiento y no función** porque MariaDB prohíbe `COMMIT/ROLLBACK` dentro de funciones. Devuelve el mensaje como una fila `resultado` (compatible con `fetchColumn()`).
3. **Aprobación en la subida**: el flujo actual aprueba la versión en el momento de subir (el usuario autorizado es el aprobador). El esquema ya contempla los estados `PENDIENTE`/`RECHAZADO` por si en el futuro se implementa un flujo de aprobación por separado.
4. **Documentación sin editar/eliminar**: por regla de negocio no existen `fun_update_*` ni `fun_softdelete_*` para `t_documentos_rrhh`, y las vistas no renderizan esos botones.
5. Los campos de auditoría (`usr_insert`/`fec_insert`, etc.) se completan en los SPs con `COALESCE(@app_current_user, CURRENT_USER())`, siguiendo la convención del proyecto.
6. El archivo `logs/mailer.log` muestra fallos de envío de correo por **extensión cURL no disponible** (problema preexistente del entorno PHP, ajeno a estos módulos).
7. **Borrado físico en documentos**: las 3 tablas están protegidas con guardas de soft delete (`SIGNAL` en `softdelete.sql`). El rechazo de versiones se modela como estado `RECHAZADO` (con su motivo y resolutor), nunca como `DELETE`. Si en el futuro se necesita eliminar la fila de una versión rechazada, debe hacerse desde la app o un procedimiento (no con trigger, por el error 1442 de MariaDB).

---

## 11. Pruebas realizadas

| Prueba | Resultado |
|---|---|
| `php -l` sobre los 9 archivos PHP nuevos/modificados | ✅ Sin errores |
| Render server-side de `documentacion.php` y `gestor_archivos.php` con sesión simulada (CLI) | ✅ HTML completo, sidebar con ambos enlaces, sin warnings |
| `doc_guardar.php` sin token CSRF | ✅ 403 JSON |
| `doc_guardar.php` con CSRF y usuario no empleado | ✅ Error amigable del SP |
| `doc_listar.php`, `gestor_listar.php`, `gestor_historial.php` | ✅ JSON correcto |
| `gestor_subir.php` sin archivo | ✅ Validación JSON |
| E2E en MariaDB: empleado → documento → v1 → v2 → duplicado rechazado | ✅ v1 queda `es_actual=0`, v2 `es_actual=1`; historial y listados correctos; datos de prueba eliminados |
| HTTP vía Apache sin sesión | ✅ 302 a `login.php` en los 3 puntos de entrada |
| RBAC aplicado | ✅ Admin con 301–305 y 311–312; EMPLEADO solo 311–312 |
| Corrección de collation + login | ✅ `ALTER DATABASE … utf8mb4_unicode_ci` + rebuild de funciones; `CALL proc_login_usuarios('admin')` sin error 1267; simulación completa de login (proc → `password_verify` → `load_user_session`) con permisos 30 y 31 cargados |
| Triggers de las 3 tablas | ✅ 6 triggers de auditoría + 3 guardas de soft delete creados; INSERT sin auditoría manual la completa el trigger; UPDATE completa `usr_update`/`fec_update`; DELETE físico bloqueado con `SIGNAL 45000` |
| Sidebar de admin | ✅ `sidebar_admin.php` actualizado (Documentación + Gestor de Archivos); render verificado con sesión simulada de rol 1 |
