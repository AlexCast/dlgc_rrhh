# Especificación de Requerimientos y Arquitectura del Sistema

> **Alcance:** Este documento describe exclusivamente lo implementado en el repositorio al 27 de julio de 2026. No incluye funcionalidades planeadas ni supuestas.
> **Proyecto:** DLGC_RRHH — Portal de Recursos Humanos de Distribuciones La Gran Cacharrería.

---

## 1. Visión General del Proyecto

### 1.1 Propósito

DLGC_RRHH es un portal web de gestión de Recursos Humanos diseñado para soportar los procesos de autenticación, autorización, administración de empleados, contratos, nómina, afiliaciones a seguridad social, solicitud de permisos/vacaciones y publicación de comunicados internos. La aplicación distingue entre usuarios con rol administrativo (acceso a módulos de gestión) y usuarios empleados (acceso a funciones operativas y de consulta).

### 1.2 Stack Tecnológico

| Capa | Tecnología | Evidencia |
|------|------------|-----------|
| Lenguaje servidor | PHP 8+ (modo procedural/OO mixto, `declare(strict_types=1)` en guards y endpoints) | [composer.json](composer.json), [app/auth_guard.php](app/auth_guard.php) |
| Servidor web | Apache 2.4 | Contexto de despliegue en `C:\Apache24\htdocs\DLGC_RRHH` y [`.htaccess`](.htaccess) |
| Base de datos | PostgreSQL 15+ (esquema `public`) | [app/conexion.php](app/conexion.php), [scripts/modelo_db.sql](scripts/modelo_db.sql) |
| Acceso a datos | PDO PostgreSQL con sentencias preparadas nativas | [app/conexion.php](app/conexion.php) |
| Correo electrónico | PHPMailer 7.1+ vía SMTP/TLS | [composer.json](composer.json), [app/helpers/Mailer.php](app/helpers/Mailer.php) |
| Frontend | HTML5, CSS3, JavaScript vanilla, FontAwesome | [assets/](assets/) |
| Gestión de dependencias | Composer | [vendor/autoload.php](vendor/autoload.php) |
| Scripts auxiliares | Python 3, PowerShell 5.1 | [scripts/add_csrf_tokens.py](scripts/add_csrf_tokens.py), [scripts/apply_src_guards.py](scripts/apply_src_guards.py), [scripts/test_csrf.ps1](scripts/test_csrf.ps1) |

---

## 2. Arquitectura y Patrones de Diseño

### 2.1 Estilo Arquitectónico

El sistema sigue un **monolito modular de tipo Cliente-Servidor** con renderizado server-side. No utiliza un framework MVC formal; en su lugar, organiza el código por tipo de responsabilidad:

- [`app/`](app/): lógica de aplicación, endpoints, helpers y guards de seguridad.
- [`templates/`](templates/): vistas PHP que generan HTML directamente.
- [`src/`](src/): módulos funcionales de negocio organizados por dominio (CRUDs administrativos).
- [`assets/`](assets/): recursos estáticos (CSS, JS, imágenes, fuentes).
- [`scripts/`](scripts/): esquema SQL, funciones almacenadas, triggers, semillas y utilidades.
- [`config/`](config/): configuración de correo SMTP.

La lógica de negocio transaccional está **delegada en funciones almacenadas de PostgreSQL**, de modo que PHP actúa principalmente como puente de presentación, seguridad y orquestación.

### 2.2 Patrones de Diseño Implementados

| Patrón | Implementación |
|--------|----------------|
| **Middleware / Guard** | [`app/auth_guard.php`](app/auth_guard.php) y [`app/src_guard.php`](app/src_guard.php) se incluyen al inicio de vistas y endpoints para validar sesión, user-agent, inactividad, CSRF y permisos. |
| **Helper / Wrapper** | [`app/helpers/Mailer.php`](app/helpers/Mailer.php) encapsula PHPMailer; [`app/helpers/RateLimiter.php`](app/helpers/RateLimiter.php) centraliza el rate limiting de correos. |
| **Repository-like** | Cada módulo de [`src/`](src/) expone operaciones homogéneas (`listar_*`, `forma_*`, `insertar_*`, `editar_*`, `update_*`, `eliminar_*`, `restore_*`) que invocan funciones almacenadas. |
| **Singleton de conexión** | [`app/conexion.php`](app/conexion.php) crea una única instancia PDO disponible mediante la variable `$conexion`. |
| **Front Controller / Endpoint** | Cada archivo PHP en [`app/`](app/) y [`src/`](src/) funciona como punto de entrada independiente para una operación. |
| **Template partials** | [`templates/partials/sidebar_nav.php`](templates/partials/sidebar_nav.php) y [`templates/partials/sidebar_admin.php`](templates/partials/sidebar_admin.php) reutilizan navegación lateral. |
| **RBAC (Role-Based Access Control)** | Tablas `t_roles`, `t_modulos`, `t_operaciones`, `t_roles_operaciones`, `t_usuarios_operaciones` modelan permisos granulares. |
| **Auditoría mediante triggers** | Los triggers de PostgreSQL completan automáticamente `usr_insert`, `fec_insert`, `usr_update`, `fec_update`, `usr_delete`, `fec_delete`. |
| **Soft Delete** | Las funciones `fun_softdelete_*` y `fun_restore_*` marcan registros como eliminados/restaurados sin borrarlos físicamente. |

### 2.3 Flujo de Datos y Componentes

```text
┌─────────────────────────────────────────────────────────────────────┐
│                            Navegador                                 │
│  (HTML/JS/CSS) ──► login.php ──► app/login.php ──► PostgreSQL       │
└──────────────────┬──────────────────────────────────────────────────┘
                   │
                   ▼
        ┌─────────────────────┐
        │   app/auth_guard    │  ← sesión, user-agent, inactividad, CSRF
        └──────────┬──────────┘
                   │
                   ▼
        ┌─────────────────────┐
        │   app/src_guard     │  ← permisos por módulo (crear/actualizar/eliminar/restaurar)
        └──────────┬──────────┘
                   │
                   ▼
        ┌─────────────────────┐
        │  src/*/*.php        │  ← CRUDs administrativos
        │  templates/*.php    │  ← vistas operativas
        └──────────┬──────────┘
                   │
                   ▼
        ┌─────────────────────┐
        │  Funciones/Triggers │  ← lógica de negocio en PostgreSQL
        │  PostgreSQL         │
        └─────────────────────┘
```

1. El cliente solicita una vista o envía un formulario.
2. Los guards validan sesión, user-agent, inactividad y token CSRF.
3. `src_guard.php` verifica el acceso al módulo y, si aplica, el permiso de acción.
4. El endpoint PHP consulta o muta datos a través de funciones almacenadas.
5. PostgreSQL aplica auditoría, soft delete o borrado físico mediante triggers.

---

## 3. Requerimientos Funcionales (RF)

### 3.1 Módulo de Autenticación y Gestión de Sesiones

#### [RF-001] Inicio de sesión con usuario o correo
- **Descripción:** El sistema permite autenticarse con `username` o `correo` electrónico y contraseña. Valida credenciales contra la función `fun_login_usuarios`, verifica el hash con `password_verify` y exige que el correo esté verificado.
- **Evidencia:** [app/login.php](app/login.php), [scripts/functions/usuarios/fun_login_usuarios.sql](scripts/functions/usuarios/fun_login_usuarios.sql)

#### [RF-002] Protección contra fijación de sesión
- **Descripción:** Tras un login exitoso, el sistema regenera el ID de sesión con `session_regenerate_id(true)`.
- **Evidencia:** [app/login.php](app/login.php)

#### [RF-003] Cierre de sesión seguro
- **Descripción:** El sistema vacía la sesión, invalida la cookie y destruye la sesión al cerrar sesión.
- **Evidencia:** [app/logout.php](app/logout.php)

#### [RF-004] Recuperación de contraseña mediante token
- **Descripción:** El usuario puede solicitar un enlace de recuperación por correo. Se genera un token criptográfico de 32 bytes (hash SHA-256 almacenado), con vigencia de 1 hora. El sistema no revela si el correo existe.
- **Evidencia:** [app/recuperar_contrasena.php](app/recuperar_contrasena.php), [app/restablecer_contrasena.php](app/restablecer_contrasena.php)

#### [RF-005] Registro de nuevos usuarios con verificación de correo
- **Descripción:** El sistema permite registrarse con código de registro (`TEMPORAL` o `UNICO_USO`), valida documento, correo y contraseña, almacena temporalmente en `t_registros_pendientes` y envía un enlace de verificación de 24 horas.
- **Evidencia:** [app/register.php](app/register.php), [app/verificar_correo.php](app/verificar_correo.php)

#### [RF-006] Rate limiting de envío de correos
- **Descripción:** Se limitan a 3 intentos por ventana de 15 minutos los envíos de correo de verificación y recuperación, registrados en `t_intentos_correo`.
- **Evidencia:** [app/helpers/RateLimiter.php](app/helpers/RateLimiter.php)

---

### 3.2 Módulo de Autorización (RBAC)

#### [RF-007] Modelo RBAC con roles, módulos y operaciones
- **Descripción:** El sistema gestiona roles (`t_roles`), módulos (`t_modulos`), operaciones (`t_operaciones`) y las relaciones `t_roles_operaciones` y `t_usuarios_operaciones`.
- **Evidencia:** [scripts/modelo_db.sql](scripts/modelo_db.sql), [scripts/inserts/rbac_seeds.sql](scripts/inserts/rbac_seeds.sql)

#### [RF-008] Carga de permisos en sesión al iniciar sesión
- **Descripción:** El login combina permisos del rol y permisos individuales del usuario mediante OR lógico, almacenando en `$_SESSION['permisos']` las claves `permiso_crear`, `permiso_actualizar`, `permiso_eliminar` y `permiso_restaurar` indexadas por `id_modulo`.
- **Evidencia:** [app/login.php](app/login.php)

#### [RF-009] Control de acceso a módulos
- **Descripción:** La existencia de la clave `id_modulo` en `$_SESSION['permisos']` determina si el usuario puede visualizar el módulo. Si no tiene acceso, se redirige a `access_denied.php`.
- **Evidencia:** [app/auth_guard.php](app/auth_guard.php)

#### [RF-010] Control de acciones dentro de un módulo
- **Descripción:** El sistema verifica permisos de `crear`, `actualizar`, `eliminar` y `restaurar` antes de permitir ejecutar la acción correspondiente en los CRUDs.
- **Evidencia:** [app/src_guard.php](app/src_guard.php)

#### [RF-011] Gestión de roles
- **Descripción:** CRUD administrativo para crear, listar, editar, eliminar y restaurar roles.
- **Evidencia:** [src/roles/](src/roles/), [scripts/functions/roles/](scripts/functions/roles/)

#### [RF-012] Gestión de módulos
- **Descripción:** CRUD administrativo para crear, listar, editar, eliminar y restaurar módulos.
- **Evidencia:** [src/modulos/](src/modulos/), [scripts/functions/modulos/](scripts/functions/modulos/)

#### [RF-013] Gestión de operaciones
- **Descripción:** CRUD administrativo para crear, listar, editar, eliminar y restaurar operaciones asociadas a un módulo.
- **Evidencia:** [src/operaciones/](src/operaciones/), [scripts/functions/operaciones/](scripts/functions/operaciones/)

#### [RF-014] Asignación de operaciones a roles
- **Descripción:** CRUD administrativo para relacionar roles con operaciones.
- **Evidencia:** [src/roles_operaciones/](src/roles_operaciones/), [scripts/functions/roles_operaciones/](scripts/functions/roles_operaciones/)

#### [RF-015] Permisos individuales por usuario
- **Descripción:** CRUD administrativo para asignar operaciones específicas a usuarios por fuera de su rol.
- **Evidencia:** [src/permisos_usuarios/](src/permisos_usuarios/), [scripts/functions/permisos_usuarios/](scripts/functions/permisos_usuarios/)

---

### 3.3 Módulo de Usuarios

#### [RF-016] Gestión de usuarios
- **Descripción:** CRUD administrativo para crear, listar, editar, eliminar y restaurar usuarios. Incluye asignación de rol, tipo y número de documento, nombres, apellidos, correo y contraseña.
- **Evidencia:** [src/usuarios/](src/usuarios/), [scripts/functions/usuarios/](scripts/functions/usuarios/)

---

### 3.4 Módulo de Empleados

#### [RF-017] Gestión de empleados
- **Descripción:** CRUD administrativo para crear, listar, editar, eliminar y restaurar empleados. Incluye datos personales, contacto, municipio, jefe inmediato y fechas de ingreso/egreso.
- **Evidencia:** [src/empleados/](src/empleados/), [scripts/functions/empleados/](scripts/functions/empleados/)

#### [RF-018] Directorio de empleados
- **Descripción:** Vista de consulta pública para usuarios autorizados que lista empleados con búsqueda y un calendario de disponibilidad simulado.
- **Evidencia:** [templates/empleados.php](templates/empleados.php), [assets/js/empleados.js](assets/js/empleados.js)

#### [RF-019] Ficha técnica del empleado
- **Descripción:** Vista de consulta que muestra datos personales, laborales y de seguridad social del usuario autenticado, obtenidos de la base de datos.
- **Evidencia:** [templates/ficha_tecnica.php](templates/ficha_tecnica.php), [assets/js/ficha_tecnica.js](assets/js/ficha_tecnica.js)

---

### 3.5 Módulos de Catálogos Maestros

#### [RF-020] Gestión de EPS
- **Descripción:** CRUD de catálogo de EPS con soft delete y restauración.
- **Evidencia:** [src/eps/](src/eps/), [scripts/functions/eps/](scripts/functions/eps/)

#### [RF-021] Gestión de ARL
- **Descripción:** CRUD de catálogo de ARL con soft delete y restauración.
- **Evidencia:** [src/arl/](src/arl/), [scripts/functions/arl/](scripts/functions/arl/)

#### [RF-022] Gestión de fondos de pensión
- **Descripción:** CRUD de catálogo de fondos de pensión con soft delete y restauración.
- **Evidencia:** [src/pension/](src/pension/), [scripts/functions/pension/](scripts/functions/pension/)

#### [RF-023] Gestión de cajas de compensación
- **Descripción:** CRUD de catálogo de cajas de compensación con soft delete y restauración.
- **Evidencia:** [src/cajacompensacion/](src/cajacompensacion/), [scripts/functions/cajacompensacion/](scripts/functions/cajacompensacion/)

#### [RF-024] Gestión de cesantías
- **Descripción:** CRUD de catálogo de fondos de cesantías con soft delete y restauración.
- **Evidencia:** [src/cesantias/](src/cesantias/), [scripts/functions/cesantias/](scripts/functions/cesantias/)

#### [RF-025] Gestión de bancos
- **Descripción:** CRUD de catálogo de bancos con soft delete y restauración.
- **Evidencia:** [src/banco/](src/banco/), [scripts/functions/bancos/](scripts/functions/bancos/)

#### [RF-026] Gestión de áreas
- **Descripción:** CRUD de catálogo de áreas de la empresa con soft delete y restauración.
- **Evidencia:** [src/area/](src/area/), [scripts/functions/areas/](scripts/functions/areas/)

---

### 3.6 Módulos de Contratos, Nómina y Afiliaciones

#### [RF-027] Gestión de contratos de empleados
- **Descripción:** CRUD de contratos laborales asociados a empleados y áreas.
- **Evidencia:** [src/contratos_empleados/](src/contratos_empleados/), [scripts/functions/contratos_empleados/](scripts/functions/contratos_empleados/)

#### [RF-028] Gestión de nómina
- **Descripción:** CRUD de registros de nómina asociados a empleados y bancos.
- **Evidencia:** [src/nomina/](src/nomina/), [scripts/functions/nomina/](scripts/functions/nomina/)

#### [RF-029] Gestión de afiliaciones de empleados
- **Descripción:** CRUD de afiliaciones a EPS, ARL, pensión, caja de compensación y cesantías por empleado.
- **Evidencia:** [src/afiliaciones_empleados/](src/afiliaciones_empleados/), [scripts/functions/afiliaciones_empleados/](scripts/functions/afiliaciones_empleados/)

---

### 3.7 Módulo de Solicitudes y Permisos

#### [RF-030] Creación de solicitudes de permiso o vacaciones
- **Descripción:** El usuario autenticado puede crear solicitudes de tipo "por días" o "por horas", seleccionar motivo, indicar si es remunerado (dinero, vacaciones o no remunerado) y agregar detalle cuando el motivo es "Otro".
- **Evidencia:** [templates/solicitud_permiso.php](templates/solicitud_permiso.php), [app/solicitud_permiso.php](app/solicitud_permiso.php), [assets/js/solicitud_permiso.js](assets/js/solicitud_permiso.js)

#### [RF-031] Cálculo automático de días y horas
- **Descripción:** El frontend calcula automáticamente el total de días (incluyendo inicio) y el total de horas con precisión de 0.5 horas según el tipo de solicitud.
- **Evidencia:** [assets/js/solicitud_permiso.js](assets/js/solicitud_permiso.js)

#### [RF-032] Validaciones de solicitud
- **Descripción:** El sistema valida que se seleccione tipo de solicitud, motivo, opción de remuneración y fechas/horas completas antes de persistir.
- **Evidencia:** [assets/js/solicitud_permiso.js](assets/js/solicitud_permiso.js), [app/solicitud_permiso.php](app/solicitud_permiso.php)

#### [RF-033] Mapeo de opción "No remunerado" a descuento N/A
- **Descripción:** El valor `NO` del formulario se mapea a `N/A` para cumplir el `CHECK` de la columna `metodo_descuento`.
- **Evidencia:** [app/solicitud_permiso.php](app/solicitud_permiso.php)

---

### 3.8 Módulo de Comunicados

#### [RF-034] Visualización de comunicados
- **Descripción:** Los usuarios con acceso al módulo 5 pueden visualizar una lista de comunicados institucionales.
- **Evidencia:** [templates/comunicados.php](templates/comunicados.php), [assets/js/comunicados.js](assets/js/comunicados.js)

---

### 3.9 Módulo de Códigos de Registro

#### [RF-035] Generación de códigos de registro
- **Descripción:** Los usuarios con permiso pueden generar códigos de registro de 4 dígitos, de tipo `TEMPORAL` o `UNICO_USO`.
- **Evidencia:** [templates/codigos_registro.php](templates/codigos_registro.php), [app/accion_codigos_registro.php](app/accion_codigos_registro.php), [assets/js/codigos_registro.js](assets/js/codigos_registro.js)

#### [RF-036] Cancelación de códigos de registro
- **Descripción:** Los usuarios con permiso pueden cancelar (marcar como eliminados) códigos de registro existentes.
- **Evidencia:** [templates/codigos_registro.php](templates/codigos_registro.php), [app/accion_codigos_registro.php](app/accion_codigos_registro.php)

---

### 3.10 Dashboards y Navegación

#### [RF-037] Dashboard de empleado
- **Descripción:** Los usuarios no administradores acceden a `firstpage.php` con resumen de métricas y accesos rápidos.
- **Evidencia:** [templates/firstpage.php](templates/firstpage.php), [templates/partials/sidebar_nav.php](templates/partials/sidebar_nav.php)

#### [RF-038] Dashboard de administrador
- **Descripción:** Los usuarios con `id_rol = 1` acceden a `secondpage.php` con accesos a módulos de gestión.
- **Evidencia:** [templates/secondpage.php](templates/secondpage.php), [templates/partials/sidebar_admin.php](templates/partials/sidebar_admin.php)

#### [RF-039] Navegación lateral condicionada por permisos
- **Descripción:** Tanto el menú de empleado como el de administrador renderizan ítems solo si el usuario tiene acceso al módulo correspondiente.
- **Evidencia:** [templates/partials/sidebar_nav.php](templates/partials/sidebar_nav.php), [templates/partials/sidebar_admin.php](templates/partials/sidebar_admin.php)

---

## 4. Requerimientos No Funcionales (RNF)

### 4.1 Rendimiento y Escalabilidad

#### [RNF-001] Uso de funciones almacenadas para operaciones transaccionales
- **Descripción:** Los CRUDs administrativos delegan inserciones, actualizaciones, eliminaciones y restauraciones en funciones almacenadas de PostgreSQL, reduciendo la cantidad de idas y vueltas entre PHP y la base de datos para la lógica de negocio.
- **Evidencia:** [scripts/functions/](scripts/functions/), [src/](src/)

#### [RNF-002] Sin caché explícita
- **Descripción:** El sistema no implementa capa de caché de aplicación (Redis, Memcached, etc.). Las consultas se ejecutan directamente contra PostgreSQL en cada petición.
- **Evidencia:** Revisión de [app/](app/) y [src/](src/)

---

### 4.2 Seguridad

#### [RNF-003] Protección CSRF mediante tokens de sesión
- **Descripción:** Cada formulario POST incluye un token CSRF de 32 bytes (`csrf_token`) y cada endpoint POST lo valida con `hash_equals`. El token se regenera tras login.
- **Evidencia:** [app/csrf_guard.php](app/csrf_guard.php), [app/login.php](app/login.php)

#### [RNF-004] Configuración segura de sesiones
- **Descripción:** Las sesiones utilizan `use_strict_mode`, `use_only_cookies`, `cookie_httponly`, `cookie_samesite = Lax` y `cookie_secure` condicional a HTTPS.
- **Evidencia:** [app/auth_guard.php](app/auth_guard.php)

#### [RNF-005] Prevención de secuestro de sesión por user-agent
- **Descripción:** El sistema almacena el `HTTP_USER_AGENT` en sesión al iniciar sesión y lo compara en cada petición con `hash_equals`; si difiere, termina la sesión.
- **Evidencia:** [app/auth_guard.php](app/auth_guard.php), [app/login.php](app/login.php)

#### [RNF-006] Expiración de sesión por inactividad
- **Descripción:** La sesión expira tras 30 minutos de inactividad.
- **Evidencia:** [app/auth_guard.php](app/auth_guard.php)

#### [RNF-007] Almacenamiento seguro de contraseñas
- **Descripción:** Las contraseñas se almacenan con `password_hash(..., PASSWORD_DEFAULT)` y se verifican con `password_verify`. La longitud mínima es de 8 caracteres.
- **Evidencia:** [app/register.php](app/register.php), [app/restablecer_contrasena.php](app/restablecer_contrasena.php), [app/login.php](app/login.php)

#### [RNF-008] Tokens criptográficos con hash SHA-256
- **Descripción:** Los tokens de verificación de correo y recuperación de contraseña se generan con `random_bytes(32)` y se almacenan como hash SHA-256.
- **Evidencia:** [app/register.php](app/register.php), [app/recuperar_contrasena.php](app/recuperar_contrasena.php)

#### [RNF-009] Sentencias preparadas y PDO
- **Descripción:** Todas las consultas a base de datos usan PDO con `ATTR_EMULATE_PREPARES = false` y sentencias preparadas, mitigando inyección SQL.
- **Evidencia:** [app/conexion.php](app/conexion.php), múltiples archivos en [src/](src/)

#### [RNF-010] Auditoría mediante triggers (cero PHP)
- **Descripción:** Los campos de auditoría (`usr_insert`, `fec_insert`, `usr_update`, `fec_update`, etc.) se completan exclusivamente por triggers de PostgreSQL; PHP no envía timestamps ni usuarios de auditoría en las operaciones que cumplen la convención.
- **Evidencia:** [scripts/triggers/auditorias.sql](scripts/triggers/auditorias.sql)

#### [RNF-011] Soft delete
- **Descripción:** Las funciones `fun_softdelete_*` marcan registros con `fec_delete` y `usr_delete` sin eliminar físicamente las filas. Las funciones `fun_restore_*` revierten la marca.
- **Evidencia:** [scripts/triggers/softdelete.sql](scripts/triggers/softdelete.sql), [scripts/functions/](scripts/functions/)

#### [RNF-012] Limpieza de registros temporales
- **Descripción:** Triggers `AFTER UPDATE` eliminan físicamente tokens usados o expirados de `t_codigos_registro`, `t_verificacion_correo`, `t_recuperacion_contrasena` y `t_registros_pendientes`. Además existe un script de limpieza periódica.
- **Evidencia:** [scripts/triggers/borrado_fisico.sql](scripts/triggers/borrado_fisico.sql), [scripts/cleanup_pending_registrations.php](scripts/cleanup_pending_registrations.php)

#### [RNF-013] Cabeceras anti-caché en vistas protegidas
- **Descripción:** `auth_guard.php` emite cabeceras `Cache-Control: no-store, no-cache, must-revalidate` y `Pragma: no-cache`.
- **Evidencia:** [app/auth_guard.php](app/auth_guard.php)

#### [RNF-014] Manejo de errores sin exposición de detalles
- **Descripción:** Los errores de base de datos se registran en `error_log` y al usuario se le presenta un mensaje genérico.
- **Evidencia:** [app/conexion.php](app/conexion.php)

---

### 4.3 Usabilidad y Responsividad

#### [RNF-015] Tema claro/oscuro
- **Descripción:** El sistema permite alternar entre tema claro y oscuro, persistiendo la preferencia en `localStorage` y respetando `prefers-color-scheme: dark`.
- **Evidencia:** [assets/js/theme.js](assets/js/theme.js), [assets/css/style.css](assets/css/style.css)

#### [RNF-016] Navegación lateral adaptativa
- **Descripción:** La interfaz incluye sidebar fijo en escritorio y menú colapsable en dispositivos móviles.
- **Evidencia:** [assets/css/firstpage.css](assets/css/firstpage.css), [templates/partials/sidebar_nav.php](templates/partials/sidebar_nav.php)

#### [RNF-017] Validaciones de cliente
- **Descripción:** Los formularios incluyen validaciones HTML (`required`, `pattern`, `minlength`, `maxlength`) y validaciones JavaScript para documentos, contraseñas, fechas y horas.
- **Evidencia:** [assets/js/login.js](assets/js/login.js), [assets/js/solicitud_permiso.js](assets/js/solicitud_permiso.js)

---

### 4.4 Mantenibilidad y Código

#### [RNF-018] Estructura modular por dominio
- **Descripción:** Los CRUDs administrativos se organizan en carpetas por entidad y comparten una convención de nombres uniforme (`listar_`, `forma_`, `insertar_`, `editar_`, `update_`, `eliminar_`, `restore_`).
- **Evidencia:** [src/](src/)

#### [RNF-019] Uso de partials reutilizables
- **Descripción:** Las vistas protegidas incluyen navegaciones laterales reutilizables desde `templates/partials/`.
- **Evidencia:** [templates/partials/sidebar_nav.php](templates/partials/sidebar_nav.php), [templates/partials/sidebar_admin.php](templates/partials/sidebar_admin.php)

#### [RNF-020] Scripts de automatización
- **Descripción:** Existen scripts Python para agregar tokens CSRF a formularios (`add_csrf_tokens.py`) y aplicar guards SRC a CRUDs existentes (`apply_src_guards.py`).
- **Evidencia:** [scripts/add_csrf_tokens.py](scripts/add_csrf_tokens.py), [scripts/apply_src_guards.py](scripts/apply_src_guards.py)

#### [RNF-021] Documentación de setup
- **Descripción:** El archivo `SETUP_RBAC.md` documenta el orden de despliegue del esquema, triggers, catálogos, funciones y seeds.
- **Evidencia:** [scripts/SETUP_RBAC.md](scripts/SETUP_RBAC.md)

---

### 4.5 Compatibilidad e Integración

#### [RNF-022] Correo SMTP mediante PHPMailer
- **Descripción:** El envío de correos transaccionales se realiza a través de PHPMailer configurado para SMTP con STARTTLS.
- **Evidencia:** [app/helpers/Mailer.php](app/helpers/Mailer.php), [config/mail_config.php](config/mail_config.php)

#### [RNF-023] Catálogo geográfico de Colombia
- **Descripción:** El sistema incluye datos de departamentos y municipios del DANE cargados mediante semillas SQL.
- **Evidencia:** [scripts/inserts/dpts_municipios.sql](scripts/inserts/dpts_municipios.sql)

#### [RNF-024] Migraciones incrementales
- **Descripción:** Existen archivos de migración SQL para agregar funcionalidades posteriores a la creación del esquema base, como la verificación de correo.
- **Evidencia:** [scripts/migrations/add_email_verification.sql](scripts/migrations/add_email_verification.sql)

---

## 5. Deuda Técnica y Observaciones

### 5.1 Credenciales y secretos hardcodeados

| Ubicación | Hallazgo | Severidad |
|-----------|----------|-----------|
| [app/conexion.php](app/conexion.php) | Credenciales de PostgreSQL (`postgres` / `0149`) hardcodeadas. Aunque el archivo incluye un comentario indicando que deberían provenir de variables de entorno, permanecen en el código versionado. | Alta |
| [config/mail_config.php](config/mail_config.php) | Contraseña de aplicación de Gmail (`recursoshumanosdlgc@gmail.com`) visible en texto plano. El archivo `.example` sugiere que no debería versionarse, pero el real sí está presente. | Crítica |
| [scripts/test_csrf.ps1](scripts/test_csrf.ps1) | Credenciales de prueba hardcodeadas (`admin` / `secret`). | Baja |

### 5.2 Violaciones a las convenciones de arquitectura del repositorio

| Ubicación | Hallazgo | Severidad |
|-----------|----------|-----------|
| [app/solicitud_permiso.php](app/solicitud_permiso.php) | Realiza `INSERT` directo en `t_solicitudes_permisos` enviando manualmente `usr_insert` y `fec_insert`. Existe `fun_insert_solicitudes_permisos.sql` para delegar la operación. | Alta |
| [app/solicitud_permiso.php](app/solicitud_permiso.php) | Realiza `INSERT` directo en `t_solicitudes_permisos_motivos` enviando campos de auditoría manualmente. | Media |
| [app/register.php](app/register.php) | Inserta directamente en `t_registros_pendientes` con `usr_insert`/`fec_insert`. | Media |
| [app/recuperar_contrasena.php](app/recuperar_contrasena.php) | Inserta/actualiza `t_recuperacion_contrasena` con campos de auditoría manuales. | Media |
| [app/restablecer_contrasena.php](app/restablecer_contrasena.php) | Actualiza `t_usuarios` con `usr_update`/`fec_update` manuales. | Media |
| [app/verificar_correo.php](app/verificar_correo.php) | Actualiza `t_usuarios` con `usr_update`/`fec_update` manuales. | Media |
| [app/accion_codigos_registro.php](app/accion_codigos_registro.php) | Inserta/actualiza `t_codigos_registro` con campos de auditoría manuales. | Media |
| [app/helpers/RateLimiter.php](app/helpers/RateLimiter.php) | Inserta/actualiza `t_intentos_correo` con `usr_insert`/`fec_insert` manuales. | Media |
| [src/usuarios/listar_usuarios.php](src/usuarios/listar_usuarios.php) | Muestra la columna `contrasena` (hash) en la tabla del CRUD, lo que constituye una fuga de información sensible. | Alta |

### 5.3 Datos estáticos, simulados o placeholders

| Ubicación | Hallazgo | Severidad |
|-----------|----------|-----------|
| [templates/firstpage.php](templates/firstpage.php) | Métricas y tabla de trámites completamente estáticas (`12 Días`, `3 Días`, `1 Activa`, fechas de ejemplo). | Media |
| [templates/secondpage.php](templates/secondpage.php) | Métricas y tabla de trámites estáticas (`45 Registrados`, `8 Por procesar`, `3 Esta semana`). | Media |
| [templates/comunicados.php](templates/comunicados.php) | Comunicados renderizados como HTML estático; no se listan desde `t_comunicados`. | Alta |
| [assets/js/empleados.js](assets/js/empleados.js) | Array de 10 empleados hardcodeados con nombres y cargos ficticios. | Alta |
| [assets/js/empleados.js](assets/js/empleados.js) | Calendario de disponibilidad fijo a julio de 2026 con lógica ficticia. | Media |
| [assets/js/solicitud_permiso.js](assets/js/solicitud_permiso.js) | Datos del empleado simulados (`Carlos Mendoza`, `1098765432`, `Área de Operaciones`) con comentario indicando que deben venir del servidor. | Alta |
| [templates/solicitud_permiso.php](templates/solicitud_permiso.php) | Campo `cargo_empleado` con valor fijo `Pendiente por asignar` y comentario de placeholder. | Media |
| [templates/index.html](templates/index.html) | Página pública con organigrama corporativo estático y estadísticas fijas. | Baja |

### 5.4 Módulos incompletos o no conectados

| Ubicación | Hallazgo | Severidad |
|-----------|----------|-----------|
| Módulo 2 — *Mis Incapacidades* | No existe plantilla asociada. En [templates/firstpage.php](templates/firstpage.php) hay un botón *“Reportar Incapacidad Médica”* sin `href` ni handler. | Alta |
| Módulo 5 — *Comunicados* | [templates/comunicados.php](templates/comunicados.php) muestra comunicados estáticos. Existe `fun_insert_comunicados.sql`, pero no hay CRUD frontend/backend conectado. | Alta |
| [src/src_empleado/](src/src_empleado/) | Carpeta vacía, sin archivos PHP. | Media |
| [src/src_jefe/](src/src_jefe/) | Carpeta vacía, sin archivos PHP. | Media |
| Permisos en frontend | Según la memoria del repositorio, la *Fase 4* (inyectar permisos a JS y ocultar/deshabilitar botones según permisos) está pendiente. Los CRUDs de `src/` aplican permisos en PHP, pero no se observa inyección a módulos dinámicos. | Media |

### 5.5 Problemas en esquema de base de datos

| Ubicación | Hallazgo | Severidad |
|-----------|----------|-----------|
| [scripts/triggers/softdelete.sql](scripts/triggers/softdelete.sql) | Solo el primer trigger está configurado correctamente como `BEFORE DELETE ON t_usuarios`. Los demás triggers declarados como `tri_soft_delete_usuarios` están definidos como `BEFORE INSERT OR UPDATE` y ejecutan `fun_audit_tablas()`, por lo que el soft delete automático no está aplicado a las demás tablas desde este archivo. | Alta |
| [scripts/triggers/auditorias.sql](scripts/triggers/auditorias.sql) y [scripts/triggers/softdelete.sql](scripts/triggers/softdelete.sql) | Nombres de triggers duplicados (`tri_audit_usuarios`, `tri_soft_delete_usuarios`) para todas las tablas. PostgreSQL los permite por tabla, pero dificulta el mantenimiento. | Media |
| Modelo general | Doble mecanismo de soft delete: las funciones `fun_softdelete_*` aplican borrado lógico por `UPDATE` directo, mientras que los triggers `BEFORE DELETE` (cuando están bien configurados) harían lo mismo. La aplicación debe decidir cuál mecanismo usa. | Media |
| Tablas de autenticación | `t_codigos_registro`, `t_verificacion_correo`, `t_recuperacion_contrasena` y `t_registros_pendientes` están diseñadas para borrado físico mediante triggers `AFTER UPDATE`, no para soft delete. | Baja (diseño intencional) |

### 5.6 Código de debug, mensajes inapropiados y residuos

| Ubicación | Hallazgo | Severidad |
|-----------|----------|-----------|
| [app/csrf_guard.php](app/csrf_guard.php) | Comentario `// DEBUG: log tokens only when they differ (remove after diagnosis)` que deja trazas de tokens en `error_log`. | Media |
| [app/verificar_correo.php](app/verificar_correo.php) | Mensaje de éxito hardcodeado y coloquial: `"Esta vaina funcionó.. Somos duros en ADSO"`. | Media |
| [scripts/functions/usuarios/fun_insert_usuarios.sql](scripts/functions/usuarios/fun_insert_usuarios.sql) | La función devuelve mensajes coloquiales e inapropiados. Además, `app/verificar_correo.php` compara el mensaje de éxito literalmente, por lo que un cambio en el mensaje rompería el flujo. | Media |
| [assets/js/comunicados.js](assets/js/comunicados.js) | `console.log("Módulo de comunicados cargado.")`. | Baja |
| [assets/js/ficha_tecnica.js](assets/js/ficha_tecnica.js) | `console.log('Módulo de Ficha Técnica cargado correctamente.')`. | Baja |
| [assets/js/solicitud_permiso.js](assets/js/solicitud_permiso.js) | `console.error('Error:', error)` en handler de `fetch`. | Baja |
| [assets/js/script.js](assets/js/script.js) | Código comentado con instrucción `// Opcional: Descomentar la siguiente línea...`. | Baja |
| [assets/js/ficha_tecnica.js](assets/js/ficha_tecnica.js) | Comentarios indicando funcionalidad pendiente (`Podríamos agregar interactividad adicional aquí`). | Baja |

### 5.7 Rutas rotas e inconsistencias

| Ubicación | Hallazgo | Severidad |
|-----------|----------|-----------|
| [templates/secondpage.php](templates/secondpage.php) | Enlace a `perfil_rrhh.php`, pero el archivo no existe en el workspace. | Alta |
| [templates/codigos_registro.php](templates/codigos_registro.php) | Mismo enlace roto a `perfil_rrhh.php`. | Alta |
| [templates/index.html](templates/index.html) | Referencia a `<script src="/DLGC_RRHH/assets/js/script.js">` (mayúsculas), mientras todo el proyecto usa `/dlgc_rrhh/`. En sistemas case-sensitive el script no cargaría. | Media |
| [templates/login.php](templates/login.php) | Incluye `recover_password.css` en la vista de login, aparentando residuo de copia/pegado. | Baja |
| Nomenclatura de directorios | Directorios en `src/` en singular (`src/area`, `src/banco`) versus sus homólogos en `scripts/functions/` en plural (`scripts/functions/areas`, `scripts/functions/bancos`). | Baja |

### 5.8 Observaciones generales

- **No se encontraron comentarios `TODO` / `FIXME` / `HACK` / `XXX` / `BUG`** en el código de negocio; la búsqueda arrojó únicamente falsos positivos (palabras como `metodo_no_valido`, textos de copyright y referencias a `BUG` en nombres de municipio).
- **No existe suite de pruebas automatizadas** (unitarias, de integración ni E2E).
- **No existe un archivo `README.md`** en la raíz del proyecto.
- **El módulo de comunicados** tiene funciones almacenadas para insertar/actualizar/listar, pero no existe interfaz administrativa conectada.
- **El módulo de incapacidades** (módulo 2) está referenciado en seeds y en memoria del repositorio, pero no tiene plantilla ni backend implementado.

---

## 6. Referencias Principales

- **Autenticación y autorización:** [app/auth_guard.php](app/auth_guard.php), [app/src_guard.php](app/src_guard.php), [app/csrf_guard.php](app/csrf_guard.php), [app/login.php](app/login.php)
- **Conexión a base de datos:** [app/conexion.php](app/conexion.php)
- **Correo electrónico:** [app/helpers/Mailer.php](app/helpers/Mailer.php), [config/mail_config.php](config/mail_config.php)
- **Esquema de base de datos:** [scripts/modelo_db.sql](scripts/modelo_db.sql), [scripts/triggers/](scripts/triggers/), [scripts/functions/](scripts/functions/)
- **Semillas RBAC:** [scripts/inserts/rbac_seeds.sql](scripts/inserts/rbac_seeds.sql)
- **Vistas:** [templates/](templates/)
- **CRUDs administrativos:** [src/](src/)
- **Setup y documentación:** [scripts/SETUP_RBAC.md](scripts/SETUP_RBAC.md)
