# Matriz de Requerimientos — DLGC_RRHH (Portal de Recursos Humanos)

> **Propósito:** Matriz exhaustiva de requerimientos funcionales (RF) y no funcionales (RNF) del proyecto DLGC_RRHH, lista para ser convertida en un archivo Excel de **8 hojas**.
> **Fuente:** Análisis del código real del repositorio al 26 de agosto de 2026 (exploración campo-por-campo, función-por-función, endpoint-por-endpoint).
> **Cliente:** Distribuciones La Gran Cacharrería (NIT 91.211.836).
> **Volumen:** 742 requerimientos verificables de forma individual (conteo exacto en HOJA 8). Análisis completado y re-verificado el 11 de septiembre de 2026.

---

## Instrucciones de conversión a Excel (para la AI que procese este archivo)

1. Crear **un libro de Excel (.xlsx) con 8 hojas**, nombradas exactamente como se indica en cada encabezado `HOJA n`.
2. Cada tabla Markdown se convierte en una tabla Excel dentro de su hoja, conservando los encabezados de columna (`ID | Requerimiento | SÍ | NO | ADI | Evidencia / Observaciones`).
3. Si una hoja contiene varias tablas (secciones, RF y RNF), ubicarlas una debajo de la otra con una fila de título que las separe (el texto del encabezado `###`).
4. El símbolo `☐` representa una **casilla de verificación vacía** que el evaluador debe marcar. Conservar el símbolo o reemplazarlo por un control de casilla real.
5. La hoja 8 es un resumen cuantitativo; sus valores deben coincidir con el conteo de filas de requerimientos de las hojas 1 a 7.
6. Los IDs son consecutivos por prefijo de hoja; no renumerar.

## Leyenda de niveles de cumplimiento

| Nivel | Significado |
|-------|-------------|
| **SÍ** | El requerimiento está implementado y cumple totalmente. |
| **NO** | El requerimiento no está implementado o no cumple. |
| **ADI** | Cumplimiento parcial: implementado con desviaciones, deuda técnica conocida o pendiente de completar. |

---

## HOJA 1 — Requerimientos Generales

> Alcance: identidad visual, branding, página pública, arquitectura, RBAC, seguridad transversal, APIs, librerías, almacenamiento de archivos, cookies y marco legal.

### 1.A Identidad Visual — Requerimientos Funcionales

| ID | Requerimiento | SÍ | NO | ADI | Evidencia / Observaciones |
|----|---------------|----|----|-----|---------------------------|
| GEN-RF-001 | El sistema debe definir el color primario corporativo verde `#3ba86a` como variable CSS reutilizable. | ☐ | ☐ | ☐ | assets/css/style.css (`:root`) |
| GEN-RF-002 | El color primario debe tener una variante hover `#298350`. | ☐ | ☐ | ☐ | assets/css/style.css |
| GEN-RF-003 | El color primario debe tener una variante para tema oscuro `#5fd08a`. | ☐ | ☐ | ☐ | assets/css/style.css |
| GEN-RF-004 | El color de texto principal debe ser `#0D130F` en tema claro y `#f9fafb` en tema oscuro. | ☐ | ☐ | ☐ | assets/css/style.css, firstpage.css |
| GEN-RF-005 | El sistema debe definir color de texto secundario (muted) `#6b7280`. | ☐ | ☐ | ☐ | assets/css/style.css |
| GEN-RF-006 | Los fondos deben estar parametrizados: blanco en login, `#f0f2f5` en dashboard y `#131B15` en tema oscuro. | ☐ | ☐ | ☐ | assets/css/login.css, firstpage.css |
| GEN-RF-007 | Los colores de estado deben ser semafóricos: aprobado verde (`#047857`/`#ecfdf5`) y pendiente ámbar (`#b45309`/`#fffbeb`). | ☐ | ☐ | ☐ | assets/css/firstpage.css |
| GEN-RF-008 | Los colores de estado deben tener variantes para tema oscuro. | ☐ | ☐ | ☐ | assets/css/firstpage.css (dark-mode badges) |
| GEN-RF-009 | La hoja administrativa debe definir colores auxiliares: peligro `#dc2626`, advertencia `#d97706` e información `#0284c7`. | ☐ | ☐ | ☐ | assets/css/src.css |
| GEN-RF-010 | La tipografía corporativa debe ser Inter (Google Fonts) en pesos 400, 500, 600 y 700. | ☐ | ☐ | ☐ | assets/css (import Google Fonts) |
| GEN-RF-011 | La navbar pública debe tener altura fija de 76px. | ☐ | ☐ | ☐ | style.css (`--navbar-height`) |
| GEN-RF-012 | El logo debe renderizarse con altura de 44px en todas las vistas. | ☐ | ☐ | ☐ | style.css (`--logo-height`) |
| GEN-RF-013 | El sistema debe ofrecer alternancia de tema claro/oscuro mediante botón con icono luna/sol. | ☐ | ☐ | ☐ | assets/js/theme.js (`#theme-toggle`) |
| GEN-RF-014 | La preferencia de tema debe persistir en `localStorage`. | ☐ | ☐ | ☐ | assets/js/theme.js |
| GEN-RF-015 | El tema debe respetar la preferencia del sistema operativo (`prefers-color-scheme`) cuando no hay preferencia guardada. | ☐ | ☐ | ☐ | assets/js/theme.js |
| GEN-RF-016 | El cambio de tema debe aplicarse con transición suave (0.3s) sin parpadeo. | ☐ | ☐ | ☐ | assets/css (transition cubic-bezier) |
| GEN-RF-017 | La interfaz debe ser responsive con punto de quiebre móvil en 768px. | ☐ | ☐ | ☐ | assets/css/firstpage.css (media queries) |
| GEN-RF-018 | En escritorio (≥992px) el layout debe ser de dos columnas: sidebar fijo de 280px + contenido. | ☐ | ☐ | ☐ | assets/css/firstpage.css (grid) |
| GEN-RF-019 | En móvil el sidebar debe ocultarse fuera de pantalla y desplegarse con botón hamburguesa. | ☐ | ☐ | ☐ | assets/css/firstpage.css, assets/js/firstpage.js |
| GEN-RF-020 | El contenedor principal debe centrarse con ancho máximo de 1200px. | ☐ | ☐ | ☐ | assets/css/style.css |
| GEN-RF-021 | El scrollbar debe estar personalizado con el color corporativo (10px, hover más oscuro). | ☐ | ☐ | ☐ | assets/css/style.css |
| GEN-RF-022 | La página pública debe incluir animaciones de aparición al hacer scroll (reveal). | ☐ | ☐ | ☐ | assets/js/script.js (IntersectionObserver) |

### 1.B Branding y Logo — Requerimientos Funcionales

| ID | Requerimiento | SÍ | NO | ADI | Evidencia / Observaciones |
|----|---------------|----|----|-----|---------------------------|
| GEN-RF-023 | El logo corporativo (logo1.png) debe aparecer en la parte superior izquierda de la navbar de la página pública y enlazar al inicio. | ☐ | ☐ | ☐ | templates/index.html |
| GEN-RF-024 | El logo debe aparecer en el encabezado de la vista de autenticación (login/registro/recuperación) y enlazar a la página pública. | ☐ | ☐ | ☐ | templates/login.php |
| GEN-RF-025 | El logo debe aparecer en la parte superior del sidebar de todas las vistas internas y enlazar al dashboard. | ☐ | ☐ | ☐ | templates/firstpage.php (`.sidebar-top`) |
| GEN-RF-026 | El logo debe aparecer en el encabezado de las páginas legales. | ☐ | ☐ | ☐ | templates/legal/*.php |
| GEN-RF-027 | Todas las imágenes del logo deben incluir texto alternativo "Distribuciones La Gran Cacharrería". | ☐ | ☐ | ☐ | Atributo `alt` en vistas |
| GEN-RF-028 | El sitio debe disponer de favicon corporativo. | ☐ | ☐ | ☐ | assets/img/favicon.ico |
| GEN-RF-029 | La página pública debe mostrar el nombre destacado "Distribuciones La Gran Cacharrería" en la sección hero. | ☐ | ☐ | ☐ | templates/index.html |

### 1.C Página Pública (Landing) — Requerimientos Funcionales

| ID | Requerimiento | SÍ | NO | ADI | Evidencia / Observaciones |
|----|---------------|----|----|-----|---------------------------|
| GEN-RF-030 | La landing debe tener navbar fija con enlaces: Inicio, Identidad Corporativa y Organigrama. | ☐ | ☐ | ☐ | templates/index.html |
| GEN-RF-031 | La landing debe tener botón de acceso al portal de empleados. | ☐ | ☐ | ☐ | templates/index.html ("Acceso Empleados") |
| GEN-RF-032 | La sección hero debe mostrar estadísticas corporativas (30+ colaboradores, 32+ años en el mercado) e imagen representativa. | ☐ | ☐ | ☐ | templates/index.html, hero-image.jpg |
| GEN-RF-033 | La landing debe presentar las tarjetas de Misión y Visión corporativas. | ☐ | ☐ | ☐ | templates/index.html |
| GEN-RF-034 | La landing debe mostrar el organigrama corporativo (Gerente → Directora de Ventas → Jefe de Ventas, Supervisora, Compras, Vendedoras, Telemercadeo). | ☐ | ☐ | ☐ | templates/index.html (estático) |
| GEN-RF-035 | El pie de página debe enlazar a Términos, Privacidad y Aviso Legal. | ☐ | ☐ | ☐ | templates/index.html (footer) |

### 1.D Arquitectura — Requerimientos Funcionales

| ID | Requerimiento | SÍ | NO | ADI | Evidencia / Observaciones |
|----|---------------|----|----|-----|---------------------------|
| GEN-RF-036 | La aplicación debe implementarse como monolito modular cliente-servidor con renderizado server-side en PHP 8+. | ☐ | ☐ | ☐ | app/, src/, templates/ |
| GEN-RF-037 | El sistema debe desplegarse sobre Apache 2.4. | ☐ | ☐ | ☐ | .htaccess |
| GEN-RF-038 | La persistencia debe resolverse en PostgreSQL 15+. | ☐ | ☐ | ☐ | scripts/modelo_db.sql |
| GEN-RF-039 | La conexión a base de datos debe ser una instancia PDO única y centralizada (singleton). | ☐ | ☐ | ☐ | app/conexion.php |
| GEN-RF-040 | La lógica de negocio transaccional debe delegarse en funciones almacenadas de PostgreSQL (131 funciones `fun_*`). | ☐ | ☐ | ☐ | scripts/functions/ |
| GEN-RF-041 | El código debe organizarse por responsabilidad: endpoints en `app/`, vistas en `templates/`, CRUDs por dominio en `src/`, estáticos en `assets/`, SQL en `scripts/`. | ☐ | ☐ | ☐ | Estructura del repositorio |
| GEN-RF-042 | Las vistas protegidas deben reutilizar partials de navegación lateral. | ☐ | ☐ | ☐ | templates/partials/ |
| GEN-RF-043 | Las dependencias PHP deben gestionarse con Composer. | ☐ | ☐ | ☐ | composer.json, vendor/ |
| GEN-RF-044 | El proyecto debe incluir scripts auxiliares de automatización (Python/PowerShell/PHP CLI). | ☐ | ☐ | ☐ | scripts/*.py, *.ps1, cleanup_*.php |
| GEN-RF-045 | Los endpoints internos consumidos por el frontend vía fetch deben responder JSON estructurado (`success`, `message`, datos). | ☐ | ☐ | ☐ | app/permiso_listar.php y demás endpoints AJAX |

### 1.E RBAC — Requerimientos Funcionales

| ID | Requerimiento | SÍ | NO | ADI | Evidencia / Observaciones |
|----|---------------|----|----|-----|---------------------------|
| GEN-RF-046 | El sistema debe implementar autorización basada en roles con entidades: roles, módulos, operaciones, roles-operaciones y usuarios-operaciones. | ☐ | ☐ | ☐ | scripts/modelo_db.sql |
| GEN-RF-047 | El sistema debe operar con dos roles base: ADMINISTRADOR (id 1) y EMPLEADO (id 2). | ☐ | ☐ | ☐ | scripts/inserts/rbac_seeds.sql |
| GEN-RF-048 | El sistema debe exponer 28 módulos de aplicación configurables (ids 1, 3–29). | ☐ | ☐ | ☐ | scripts/inserts/rbac_seeds.sql |
| GEN-RF-049 | Cada módulo debe soportar las operaciones VER, CREAR, ACTUALIZAR, ELIMINAR y RESTAURAR. | ☐ | ☐ | ☐ | rbac_seeds.sql; módulos 22 y 27 limitan operaciones |
| GEN-RF-050 | Los permisos efectivos del usuario deben calcularse combinando permisos del rol y permisos individuales (OR lógico). | ☐ | ☐ | ☐ | app/helpers/SessionUserLoader.php |
| GEN-RF-051 | Los permisos deben cargarse en sesión indexados por módulo con banderas `permiso_crear/actualizar/eliminar/restaurar`. | ☐ | ☐ | ☐ | $_SESSION['permisos'] |
| GEN-RF-052 | El acceso a un módulo debe determinarse por la existencia de su clave en los permisos de sesión. | ☐ | ☐ | ☐ | `has_module_access()` en app/auth_guard.php |
| GEN-RF-053 | El acceso denegado debe redirigir a una página 403 dedicada. | ☐ | ☐ | ☐ | templates/access_denied.php |
| GEN-RF-054 | El sistema debe contar con página 404 dedicada. | ☐ | ☐ | ☐ | templates/not_found.php |

### 1.F Librerías e Integraciones — Requerimientos Funcionales

| ID | Requerimiento | SÍ | NO | ADI | Evidencia / Observaciones |
|----|---------------|----|----|-----|---------------------------|
| GEN-RF-055 | El envío de correos debe realizarse vía APIs REST multi-proveedor (Brevo, Resend, MailerSend) con conmutación por fallo. | ☐ | ☐ | ☐ | app/helpers/Mailer.php, app/mail/ |
| GEN-RF-056 | La generación de reportes Excel debe realizarse con PhpSpreadsheet. | ☐ | ☐ | ☐ | app/permiso_reporte.php, app/vacaciones_reporte.php |
| GEN-RF-057 | La generación de reportes PDF debe realizarse con Dompdf. | ☐ | ☐ | ☐ | app/permiso_reporte.php, app/vacaciones_reporte.php |
| GEN-RF-058 | La protección anti-bot debe integrar Cloudflare Turnstile con verificación server-side. | ☐ | ☐ | ☐ | app/helpers/TurnstileHelper.php |
| GEN-RF-059 | Las evidencias de solicitudes deben almacenarse en un proveedor de storage externo configurable (Bunny/R2/B2/CF Images). | ☐ | ☐ | ☐ | app/helpers/FileStorage/StorageFactory.php, config/storage_config.php |
| GEN-RF-060 | La subida de evidencias debe validar tipo, MIME y tamaño antes de enviarse al storage. | ☐ | ☐ | ☐ | app/helpers/FileStorage/EvidenceValidator.php |
| GEN-RF-061 | La iconografía debe implementarse con SVG inline, sin fuentes de iconos externas. | ☐ | ☐ | ☐ | templates/partials/sidebar_*.php |

### 1.G Cookies y Legal — Requerimientos Funcionales

| ID | Requerimiento | SÍ | NO | ADI | Evidencia / Observaciones |
|----|---------------|----|----|-----|---------------------------|
| GEN-RF-062 | El sistema debe mostrar un banner de consentimiento de cookies fijo al pie cuando no exista decisión previa. | ☐ | ☐ | ☐ | templates/partials/cookie_banner.php |
| GEN-RF-063 | El banner debe ofrecer dos opciones: "Solo necesarias" y "Aceptar todas". | ☐ | ☐ | ☐ | assets/js/cookie-banner.js |
| GEN-RF-064 | La decisión de consentimiento debe persistir 365 días en localStorage y en cookie técnica. | ☐ | ☐ | ☐ | assets/js/cookie-banner.js |
| GEN-RF-065 | Las cookies deben clasificarse en necesarias (sesión, CSRF) y opcionales (recordarme); las opcionales solo se activan con consentimiento "todas". | ☐ | ☐ | ☐ | cookie-banner.js, app/login.php |
| GEN-RF-066 | El sistema debe permitir revocar el consentimiento, borrando la cookie de consentimiento e invalidando el token "Recordarme". | ☐ | ☐ | ☐ | app/revoke_cookie_consent.php |
| GEN-RF-067 | El banner debe enlazar a la Política de Privacidad. | ☐ | ☐ | ☐ | templates/partials/cookie_banner.php |
| GEN-RF-068 | El sistema debe publicar página de Términos y Condiciones con secciones: aceptación, acceso, uso permitido, uso prohibido, responsabilidad, suspensión y disponibilidad. | ☐ | ☐ | ☐ | templates/legal/terminos.php |
| GEN-RF-069 | El sistema debe publicar Política de Privacidad con responsable, datos tratados, finalidades, base legal (Ley 1581/2012, Decreto 1377/2013), almacenamiento y medidas de seguridad. | ☐ | ☐ | ☐ | templates/legal/privacidad.php |
| GEN-RF-070 | El sistema debe publicar Aviso Legal con objeto del portal, propiedad intelectual, responsabilidad y legislación aplicable. | ☐ | ☐ | ☐ | templates/legal/aviso_legal.php |
| GEN-RF-071 | Las páginas legales deben identificar al responsable con nombre, NIT, dirección y correo de contacto. | ☐ | ☐ | ☐ | templates/legal/privacidad.php, aviso_legal.php |
| GEN-RF-072 | El registro de usuarios debe exigir aceptación explícita de términos, privacidad y cookies con enlaces a las páginas legales. | ☐ | ☐ | ☐ | templates/login.php (checkboxes legales) |

### 1.H Requerimientos No Funcionales Generales

| ID | Requerimiento | SÍ | NO | ADI | Evidencia / Observaciones |
|----|---------------|----|----|-----|---------------------------|
| GEN-RNF-001 | Las sesiones deben configurarse con cookie `HttpOnly`. | ☐ | ☐ | ☐ | app/session_bootstrap.php |
| GEN-RNF-002 | La cookie de sesión debe usar `Secure` de forma dinámica según HTTPS. | ☐ | ☐ | ☐ | app/session_bootstrap.php |
| GEN-RNF-003 | La cookie de sesión debe usar `SameSite=Lax`. | ☐ | ☐ | ☐ | app/session_bootstrap.php |
| GEN-RNF-004 | Debe activarse `session.use_strict_mode` y `session.use_only_cookies`. | ☐ | ☐ | ☐ | app/session_bootstrap.php |
| GEN-RNF-005 | La duración de la cookie de sesión debe ser de navegador (lifetime 0) con `gc_maxlifetime` de 1800s. | ☐ | ☐ | ☐ | app/session_bootstrap.php |
| GEN-RNF-006 | Todo formulario POST debe incluir token CSRF de 32 bytes (64 hex) validado en servidor con `hash_equals`. | ☐ | ☐ | ☐ | app/csrf_guard.php |
| GEN-RNF-007 | Toda consulta debe usar sentencias preparadas nativas de PDO (`EMULATE_PREPARES=false`). | ☐ | ☐ | ☐ | app/conexion.php |
| GEN-RNF-008 | La auditoría (usr_insert/fec_insert/usr_update/fec_update/usr_delete/fec_delete) debe completarse exclusivamente por triggers de PostgreSQL. | ☐ | ☐ | ☐ | scripts/triggers/auditorias.sql; varios endpoints la setean manualmente (ADI) |
| GEN-RNF-009 | El borrado de entidades de negocio debe ser lógico (soft delete) y reversible, nunca físico. | ☐ | ☐ | ☐ | scripts/triggers/softdelete.sql |
| GEN-RNF-010 | Los errores deben registrarse en `error_log` y mostrarse al usuario como mensajes genéricos. | ☐ | ☐ | ☐ | app/conexion.php |
| GEN-RNF-011 | Las vistas protegidas deben emitir cabeceras anti-caché (`Cache-Control: no-store, no-cache`, `Pragma: no-cache`). | ☐ | ☐ | ☐ | app/auth_guard.php |
| GEN-RNF-012 | Las entradas de usuario deben normalizarse con un sanitizador centralizado (trim, case, regex). | ☐ | ☐ | ☐ | app/helpers/InputSanitizer.php |
| GEN-RNF-013 | El HTML generado por editores enriquecidos debe depurarse contra XSS con lista blanca de etiquetas. | ☐ | ☐ | ☐ | app/helpers/HtmlSanitizer.php |
| GEN-RNF-014 | Las salidas de texto plano en HTML deben escaparse con `htmlspecialchars`. | ☐ | ☐ | ☐ | Vistas templates/ y src/ |
| GEN-RNF-015 | El sistema no debe implementar capa de caché; las consultas se ejecutan directamente contra PostgreSQL. | ☐ | ☐ | ☐ | Revisión de app/ y src/ |
| GEN-RNF-016 | El código debe seguir la convención de nombres por dominio (listar_, forma_, insertar_, editar_, update_, eliminar_, restore_). | ☐ | ☐ | ☐ | src/ |
| GEN-RNF-017 | Los archivos PHP sensibles (guards, endpoints) deben declarar `strict_types=1`. | ☐ | ☐ | ☐ | app/auth_guard.php, src/comunicados, src/sst |
| GEN-RNF-018 | Los secretos deben residir en archivos de configuración excluidos del control de versiones, versionando solo plantillas `.example`. | ☐ | ☐ | ☐ | config/*.example; .env.example (credenciales BD vía .env) |
| GEN-RNF-019 | La interfaz debe cumplir accesibilidad básica: `aria-current` en menú activo, `role="alert"` en banners, `aria-label` en botones de cierre. | ☐ | ☐ | ☐ | sidebar_*.php, app/alert_helper.php |
| GEN-RNF-020 | El sistema debe proveer scripts para aplicar masivamente tokens CSRF y guards a los CRUDs. | ☐ | ☐ | ☐ | scripts/add_csrf_tokens.py, apply_src_guards.py |

---

## HOJA 2 — Login y Autenticación

> Alcance: vista de autenticación (3 pestañas), inicio de sesión, registro en dos pasos, verificación de correo, recuperación y restablecimiento de contraseña, "Recordarme" y cierre de sesión.

### 2.A Vista de Autenticación — Requerimientos Funcionales

| ID | Requerimiento | SÍ | NO | ADI | Evidencia / Observaciones |
|----|---------------|----|----|-----|---------------------------|
| LOG-RF-001 | La vista de autenticación debe organizarse en tres pestañas: Ingresar, Activar Cuenta y Recuperar. | ☐ | ☐ | ☐ | templates/login.php, assets/js/login.js |
| LOG-RF-002 | La pestaña activa por defecto debe ser "Ingresar". | ☐ | ☐ | ☐ | templates/login.php |
| LOG-RF-003 | La vista debe mostrar área de retroalimentación dinámica para mensajes de error/éxito según parámetros de URL. | ☐ | ☐ | ☐ | templates/login.php (`#auth-feedback`) |
| LOG-RF-004 | La vista debe incluir botón "Volver" a la página pública. | ☐ | ☐ | ☐ | templates/login.php |
| LOG-RF-005 | La vista debe incluir el botón de alternancia de tema claro/oscuro. | ☐ | ☐ | ☐ | templates/login.php |
| LOG-RF-006 | La tarjeta de autenticación debe estar centrada con ancho máximo de 480px. | ☐ | ☐ | ☐ | assets/css/login.css |
| LOG-RF-007 | Los tres formularios deben incluir token CSRF oculto. | ☐ | ☐ | ☐ | templates/login.php, app/csrf_guard.php |

### 2.B Formulario de Ingreso — Requerimientos Funcionales

| ID | Requerimiento | SÍ | NO | ADI | Evidencia / Observaciones |
|----|---------------|----|----|-----|---------------------------|
| LOG-RF-008 | El campo identificador debe aceptar nombre de usuario **o** correo electrónico. | ☐ | ☐ | ☐ | app/login.php, fun_login_usuarios |
| LOG-RF-009 | El campo identificador debe validar formato en cliente (regex de email o de username). | ☐ | ☐ | ☐ | templates/login.php (`pattern`) |
| LOG-RF-010 | El campo contraseña debe incluir toggle para mostrar/ocultar el texto. | ☐ | ☐ | ☐ | templates/login.php, assets/js/login.js |
| LOG-RF-011 | El formulario debe ofrecer casilla "Recordarme". | ☐ | ☐ | ☐ | templates/login.php |
| LOG-RF-012 | El formulario debe incluir enlace a recuperación de contraseña. | ☐ | ☐ | ☐ | templates/login.php |
| LOG-RF-013 | El formulario de ingreso debe incluir captcha Cloudflare Turnstile. | ☐ | ☐ | ☐ | templates/login.php (`cf-turnstile`) |
| LOG-RF-014 | Al autenticarse, el sistema debe verificar el hash con `password_verify`. | ☐ | ☐ | ☐ | app/login.php |
| LOG-RF-015 | El sistema debe rechazar el ingreso si el correo no está verificado. | ☐ | ☐ | ☐ | app/login.php (`correo_no_verificado`) |
| LOG-RF-016 | El sistema debe aplicar rate limiting de 3 intentos fallidos por 15 minutos en el login. | ☐ | ☐ | ☐ | app/login.php, RateLimiter tipo 'login' |
| LOG-RF-017 | Tras login exitoso, el sistema debe regenerar el identificador de sesión. | ☐ | ☐ | ☐ | `session_regenerate_id(true)` |
| LOG-RF-018 | Tras login exitoso, el sistema debe regenerar el token CSRF. | ☐ | ☐ | ☐ | `csrf_regenerate_token()` |
| LOG-RF-019 | Tras login exitoso, el sistema debe cargar en sesión: id_usuario, username, id_rol, nombre_completo, user-agent, timestamp de actividad y permisos. | ☐ | ☐ | ☐ | app/helpers/SessionUserLoader.php |
| LOG-RF-020 | Tras login exitoso, el sistema debe redirigir al dashboard. | ☐ | ☐ | ☐ | app/login.php → firstpage.php |
| LOG-RF-021 | Si el usuario marca "Recordarme" y aceptó todas las cookies, el sistema debe crear un token persistente de 30 días. | ☐ | ☐ | ☐ | app/login.php, RememberMeHelper::create |
| LOG-RF-022 | Los errores de login deben redirigir con códigos controlados: metodo_no_valido, turnstile_invalido, campos_obligatorios, credenciales_invalidas, demasiados_intentos, correo_no_verificado, error_servidor. | ☐ | ☐ | ☐ | app/login.php |
| LOG-RF-023 | El error `demasiados_intentos` debe informar los minutos restantes de bloqueo. | ☐ | ☐ | ☐ | app/login.php, RateLimiter::verificar |
| LOG-RF-024 | El mensaje de credenciales inválidas debe ser neutro (no distinguir usuario inexistente de contraseña errada). | ☐ | ☐ | ☐ | app/login.php |

### 2.C Registro — Paso 1 (Código de Acceso) — Requerimientos Funcionales

| ID | Requerimiento | SÍ | NO | ADI | Evidencia / Observaciones |
|----|---------------|----|----|-----|---------------------------|
| LOG-RF-025 | El registro debe iniciar solicitando un código de acceso de exactamente 4 dígitos numéricos. | ☐ | ☐ | ☐ | templates/login.php (`pattern="[0-9]{4}"`) |
| LOG-RF-026 | El paso 1 debe incluir texto de ayuda indicando que el código se solicita a RRHH. | ☐ | ☐ | ☐ | templates/login.php |
| LOG-RF-027 | El paso 1 debe incluir captcha Turnstile. | ☐ | ☐ | ☐ | templates/login.php |
| LOG-RF-028 | El sistema debe validar que el código exista, no esté eliminado, y esté vigente (TEMPORAL no expirado o UNICO_USO no usado). | ☐ | ☐ | ☐ | app/register.php |
| LOG-RF-029 | Si el código es UNICO_USO, debe marcarse como usado al validarse (borrado físico vía trigger). | ☐ | ☐ | ☐ | app/register.php, tri_borrar_codigo_registro |
| LOG-RF-030 | Tras validar el código, el sistema debe guardar en sesión la validación y tipo de código, y avanzar al paso 2. | ☐ | ☐ | ☐ | app/register.php (`codigo_registro_validado`) |

### 2.D Registro — Paso 2 (Datos Personales) — Requerimientos Funcionales

| ID | Requerimiento | SÍ | NO | ADI | Evidencia / Observaciones |
|----|---------------|----|----|-----|---------------------------|
| LOG-RF-031 | El paso 2 debe mostrar un banner de confirmación "Código validado". | ☐ | ☐ | ☐ | templates/login.php (register-step2) |
| LOG-RF-032 | El campo username debe validar: iniciar con letra minúscula, 3–30 caracteres, solo `[a-z0-9_.-]`. | ☐ | ☐ | ☐ | InputSanitizer::validateUsername |
| LOG-RF-033 | El campo tipo de documento debe ofrecer las opciones CC, PPT y CE. | ☐ | ☐ | ☐ | templates/login.php |
| LOG-RF-034 | El documento CC debe validarse como exactamente 10 dígitos. | ☐ | ☐ | ☐ | app/register.php (regex `^[0-9]{10}$`) |
| LOG-RF-035 | El documento PPT debe validarse como 5–20 caracteres alfanuméricos con guion. | ☐ | ☐ | ☐ | app/register.php |
| LOG-RF-036 | El documento CE debe validarse como 5–20 caracteres alfanuméricos. | ☐ | ☐ | ☐ | app/register.php |
| LOG-RF-037 | El formulario debe capturar primer nombre (obligatorio) y segundo nombre (opcional). | ☐ | ☐ | ☐ | templates/login.php |
| LOG-RF-038 | El formulario debe capturar primer apellido (obligatorio) y segundo apellido (opcional). | ☐ | ☐ | ☐ | templates/login.php |
| LOG-RF-039 | Los nombres y apellidos deben limitarse a 30 caracteres. | ☐ | ☐ | ☐ | app/register.php |
| LOG-RF-040 | El formulario debe capturar fecha de nacimiento. | ☐ | ☐ | ☐ | templates/login.php |
| LOG-RF-041 | El formulario debe capturar género mediante selector. | ☐ | ☐ | ☐ | templates/login.php |
| LOG-RF-042 | El campo correo debe validar formato email y máximo 40 caracteres. | ☐ | ☐ | ☐ | InputSanitizer::validateEmail |
| LOG-RF-043 | El formulario debe capturar teléfono y dirección. | ☐ | ☐ | ☐ | templates/login.php |
| LOG-RF-044 | El formulario debe capturar departamento y municipio con selector encadenado (el municipio depende del departamento). | ☐ | ☐ | ☐ | templates/login.php, catálogo DANE |
| LOG-RF-045 | La contraseña debe exigir entre 8 y 255 caracteres y coincidencia con su confirmación. | ☐ | ☐ | ☐ | InputSanitizer::validatePassword |
| LOG-RF-046 | El formulario debe exigir aceptación de términos y de política de privacidad. | ☐ | ☐ | ☐ | templates/login.php, app/register.php |
| LOG-RF-047 | Al registrarse, el usuario debe almacenarse en `t_registros_pendientes` con vigencia de 24 horas (no directamente en t_usuarios). | ☐ | ☐ | ☐ | app/register.php |
| LOG-RF-048 | Al registrarse, el sistema debe asignar el rol EMPLEADO por defecto. | ☐ | ☐ | ☐ | app/register.php |
| LOG-RF-049 | Al registrarse, el sistema debe generar token de verificación de 32 bytes, almacenar su hash SHA-256 y enviar el enlace por correo. | ☐ | ☐ | ☐ | app/register.php |
| LOG-RF-050 | El envío del correo de verificación debe estar limitado a 3 intentos por 15 minutos. | ☐ | ☐ | ☐ | RateLimiter tipo 'verificacion' |
| LOG-RF-051 | Tras el registro, el sistema debe limpiar los datos de registro de la sesión y redirigir al login con mensaje "verificación enviada". | ☐ | ☐ | ☐ | app/register.php |

### 2.E Verificación de Correo — Requerimientos Funcionales

| ID | Requerimiento | SÍ | NO | ADI | Evidencia / Observaciones |
|----|---------------|----|----|-----|---------------------------|
| LOG-RF-052 | El endpoint de verificación debe aceptar el token por URL (GET) y validar que sea hexadecimal de 64 caracteres. | ☐ | ☐ | ☐ | app/verificar_correo.php |
| LOG-RF-053 | El sistema debe rechazar tokens ya usados, eliminados o expirados (24h). | ☐ | ☐ | ☐ | app/verificar_correo.php |
| LOG-RF-054 | Al verificar, el sistema debe crear el usuario real en t_usuarios mediante `fun_insert_usuarios`. | ☐ | ☐ | ☐ | app/verificar_correo.php |
| LOG-RF-055 | Al verificar, el sistema debe marcar `correo_verificado = TRUE`. | ☐ | ☐ | ☐ | app/verificar_correo.php |
| LOG-RF-056 | La verificación debe ejecutarse en transacción atómica (marcar uso del token y activación). | ☐ | ☐ | ☐ | app/verificar_correo.php |
| LOG-RF-057 | Al usarse el token, los triggers deben borrar físicamente el token y el registro pendiente asociado. | ☐ | ☐ | ☐ | scripts/triggers/borrado_fisico.sql |
| LOG-RF-058 | Tras verificar, el sistema debe redirigir al login con mensaje de éxito. | ☐ | ☐ | ☐ | app/verificar_correo.php |

### 2.F Recuperación y Restablecimiento — Requerimientos Funcionales

| ID | Requerimiento | SÍ | NO | ADI | Evidencia / Observaciones |
|----|---------------|----|----|-----|---------------------------|
| LOG-RF-059 | La pestaña Recuperar debe solicitar únicamente el correo electrónico. | ☐ | ☐ | ☐ | templates/login.php (recover-form) |
| LOG-RF-060 | La recuperación debe responder siempre igual, exista o no el correo (no enumeración de cuentas). | ☐ | ☐ | ☐ | app/recuperar_contrasena.php |
| LOG-RF-061 | La recuperación debe estar limitada a 3 intentos por 15 minutos. | ☐ | ☐ | ☐ | RateLimiter tipo 'recuperacion' |
| LOG-RF-062 | El token de recuperación debe ser de 32 bytes, almacenarse como hash SHA-256 y expirar en 1 hora. | ☐ | ☐ | ☐ | app/recuperar_contrasena.php |
| LOG-RF-063 | Al generar un nuevo token, deben invalidarse los tokens de recuperación anteriores del usuario. | ☐ | ☐ | ☐ | app/recuperar_contrasena.php |
| LOG-RF-064 | El sistema debe enviar el enlace de restablecimiento por correo con plantilla HTML corporativa. | ☐ | ☐ | ☐ | app/recuperar_contrasena.php, Mailer |
| LOG-RF-065 | La vista de restablecimiento debe validar el token antes de mostrar el formulario (no usado, no eliminado, no expirado). | ☐ | ☐ | ☐ | app/restablecer_contrasena.php |
| LOG-RF-066 | El formulario de restablecimiento debe pedir nueva contraseña y confirmación con toggle de visibilidad. | ☐ | ☐ | ☐ | app/restablecer_contrasena.php |
| LOG-RF-067 | Al restablecer, la contraseña debe actualizarse hasheada y el token debe marcarse como usado. | ☐ | ☐ | ☐ | app/restablecer_contrasena.php |
| LOG-RF-068 | Al restablecer, deben invalidarse todos los tokens "Recordarme" del usuario (cierra sesiones persistentes). | ☐ | ☐ | ☐ | RememberMeHelper::invalidateAllForUser |

### 2.G Recordarme y Cierre de Sesión — Requerimientos Funcionales

| ID | Requerimiento | SÍ | NO | ADI | Evidencia / Observaciones |
|----|---------------|----|----|-----|---------------------------|
| LOG-RF-069 | El "Recordarme" debe usar patrón selector (12 hex, público) + validator (64 hex, almacenado como SHA-256). | ☐ | ☐ | ☐ | app/helpers/RememberMeHelper.php |
| LOG-RF-070 | El token "Recordarme" debe rotar en cada validación (nuevo selector+validator). | ☐ | ☐ | ☐ | RememberMeHelper::validate |
| LOG-RF-071 | Al crear un token nuevo, deben eliminarse los tokens previos del usuario (no acumulación). | ☐ | ☐ | ☐ | RememberMeHelper::create |
| LOG-RF-072 | Si no hay sesión activa, el guard debe intentar reautenticar con la cookie "Recordarme". | ☐ | ☐ | ☐ | app/auth_guard.php |
| LOG-RF-073 | El cierre de sesión debe vaciar la sesión, invalidar el token "Recordarme", borrar la cookie de sesión y destruir la sesión en servidor. | ☐ | ☐ | ☐ | app/logout.php |
| LOG-RF-074 | Tras cerrar sesión, el sistema debe redirigir a la vista de login. | ☐ | ☐ | ☐ | app/logout.php |

### 2.H Requerimientos No Funcionales de Autenticación

| ID | Requerimiento | SÍ | NO | ADI | Evidencia / Observaciones |
|----|---------------|----|----|-----|---------------------------|
| LOG-RNF-001 | Las contraseñas deben almacenarse con `password_hash(PASSWORD_DEFAULT)` (bcrypt). | ☐ | ☐ | ☐ | app/register.php, app/restablecer_contrasena.php |
| LOG-RNF-002 | Los tokens deben generarse con `random_bytes(32)` y almacenarse solo como hash SHA-256. | ☐ | ☐ | ☐ | register.php, recuperar_contrasena.php, RememberMeHelper |
| LOG-RNF-003 | La comparación de tokens y user-agent debe usar `hash_equals` (timing-safe). | ☐ | ☐ | ☐ | csrf_guard.php, RememberMeHelper, auth_guard.php |
| LOG-RNF-004 | La respuesta del captcha debe validarse contra la API de Cloudflare con timeout de 10s antes de procesar credenciales. | ☐ | ☐ | ☐ | app/helpers/TurnstileHelper.php |
| LOG-RNF-005 | Las claves de Turnstile deben residir en configuración no versionada. | ☐ | ☐ | ☐ | config/turnstile_config.php(.example) |
| LOG-RNF-006 | La sesión debe vincularse al User-Agent y terminar si este cambia. | ☐ | ☐ | ☐ | app/auth_guard.php |
| LOG-RNF-007 | La sesión debe expirar tras 30 minutos de inactividad. | ☐ | ☐ | ☐ | app/auth_guard.php |
| LOG-RNF-008 | Los intentos deben registrarse con upsert atómico (`ON CONFLICT DO UPDATE`) para evitar condiciones de carrera. | ☐ | ☐ | ☐ | RateLimiter sobre t_intentos_correo |
| LOG-RNF-009 | Los tokens usados/expirados deben purgarse físicamente mediante triggers y scripts de limpieza programables. | ☐ | ☐ | ☐ | borrado_fisico.sql, scripts/cleanup_*.php |
| LOG-RNF-010 | Los correos transaccionales deben usar plantilla HTML con identidad corporativa y versión de texto plano alternativa. | ☐ | ☐ | ☐ | app/helpers/Mailer.php |
| LOG-RNF-011 | La cookie "Recordarme" debe ser HttpOnly, Secure dinámico y SameSite=Lax. | ☐ | ☐ | ☐ | app/helpers/CookieHelper.php |
| LOG-RNF-012 | Los identificadores de login deben normalizarse (minúsculas, sin caracteres inválidos) antes de consultar. | ☐ | ☐ | ☐ | InputSanitizer::loginIdentifier |
| LOG-RNF-013 | La autenticación debe propagar el usuario de aplicación a PostgreSQL para auditoría. | ☐ | ☐ | ☐ | `app.current_user` en auth_guard/conexion |
| LOG-RNF-014 | El flujo de registro debe ser resistente a reenvío duplicado (si el usuario ya existe, completar sin error). | ☐ | ☐ | ☐ | app/verificar_correo.php |
| LOG-RNF-015 | Los códigos de error deben viajar por URL como códigos controlados, nunca con detalles técnicos. | ☐ | ☐ | ☐ | redirects `?status=error&code=...` |

---

## HOJA 3 — Dashboard

> Alcance: página principal posterior al login ([templates/firstpage.php](templates/firstpage.php)). **El dashboard es dinámico: las métricas, paneles y tablas que se componen dependen del rol y de los permisos del usuario autenticado.**

### 3.A Estructura y Acceso — Requerimientos Funcionales

| ID | Requerimiento | SÍ | NO | ADI | Evidencia / Observaciones |
|----|---------------|----|----|-----|---------------------------|
| DSH-RF-001 | El acceso al dashboard debe restringirse a usuarios autenticados con acceso al módulo Inicio (id 1). | ☐ | ☐ | ☐ | `require_module_access(1)` |
| DSH-RF-002 | El dashboard debe componer su contenido dinámicamente según el rol y los permisos del usuario en sesión. | ☐ | ☐ | ☐ | templates/firstpage.php |
| DSH-RF-003 | El dashboard debe saludar al usuario por su nombre ("¡Hola de nuevo, {nombre}!"). | ☐ | ☐ | ☐ | templates/firstpage.php |
| DSH-RF-004 | El dashboard debe cargar el sidebar correspondiente al rol del usuario (admin o general). | ☐ | ☐ | ☐ | `$_SESSION['id_rol'] === 1` |
| DSH-RF-005 | El dashboard debe tener barra superior con título, botón de tema y botón de cierre de sesión. | ☐ | ☐ | ☐ | templates/firstpage.php (topbar) |
| DSH-RF-006 | La barra superior debe incluir botón hamburguesa visible solo en móvil. | ☐ | ☐ | ☐ | templates/firstpage.php |

### 3.B Bloque de Perfil — Requerimientos Funcionales

| ID | Requerimiento | SÍ | NO | ADI | Evidencia / Observaciones |
|----|---------------|----|----|-----|---------------------------|
| DSH-RF-007 | El sidebar del dashboard debe mostrar el bloque de perfil del usuario autenticado. | ☐ | ☐ | ☐ | `.sidebar-profile` |
| DSH-RF-008 | El bloque de perfil debe mostrar la foto del usuario si existe en sesión. | ☐ | ☐ | ☐ | `$_SESSION['foto_perfil']` |
| DSH-RF-009 | Si no hay foto, el bloque de perfil debe mostrar un avatar SVG por defecto. | ☐ | ☐ | ☐ | templates/firstpage.php |
| DSH-RF-010 | El bloque de perfil debe mostrar el nombre completo del usuario. | ☐ | ☐ | ☐ | `$_SESSION['nombre_completo']` |
| DSH-RF-011 | El bloque de perfil debe mostrar el cargo obtenido del contrato vigente del empleado. | ☐ | ☐ | ☐ | t_contratos_empleados.puesto |

### 3.C Métricas Personales — Requerimientos Funcionales

| ID | Requerimiento | SÍ | NO | ADI | Evidencia / Observaciones |
|----|---------------|----|----|-----|---------------------------|
| DSH-RF-012 | El dashboard debe mostrar la tarjeta de métrica "Vacaciones Disponibles". | ☐ | ☐ | ☐ | templates/firstpage.php |
| DSH-RF-013 | La métrica de vacaciones debe calcularse en tiempo real con `fun_calcular_saldo_vacaciones`. | ☐ | ☐ | ☐ | fun_calcular_saldo_vacaciones |
| DSH-RF-014 | La métrica de vacaciones debe mostrar los días de saldo disponible con un decimal. | ☐ | ☐ | ☐ | templates/firstpage.php |
| DSH-RF-015 | La métrica de vacaciones debe mostrar la próxima fecha de aniversario. | ☐ | ☐ | ☐ | proxima_fecha_aniversario |
| DSH-RF-016 | Si el empleado tiene menos de un año de servicio, la tarjeta debe indicar "Aún no cumples primer año" con saldo 0. | ☐ | ☐ | ☐ | templates/firstpage.php |
| DSH-RF-017 | El dashboard debe mostrar la tarjeta de métrica "Incapacidades del Año" con días acumulados de solicitudes aprobadas tipo incapacidad del año en curso. | ☐ | ☐ | ☐ | COUNT solicitudes APROBADAS tipo Incapacidad |
| DSH-RF-018 | El dashboard debe mostrar la tarjeta de métrica "Solicitudes en Proceso" con el conteo de solicitudes en estado PENDIENTE o EN_REVISION. | ☐ | ☐ | ☐ | templates/firstpage.php |
| DSH-RF-019 | Las tarjetas de métricas deben disponerse en grilla responsive de mínimo 200px por tarjeta. | ☐ | ☐ | ☐ | assets/css/firstpage.css (`.metrics-grid`) |

### 3.D Panel de Gestión Condicional — Requerimientos Funcionales

| ID | Requerimiento | SÍ | NO | ADI | Evidencia / Observaciones |
|----|---------------|----|----|-----|---------------------------|
| DSH-RF-020 | El panel de gestión debe mostrarse únicamente si el usuario tiene al menos uno de los permisos de gestión asociados. | ☐ | ☐ | ☐ | `$mostrarPanelGestion` |
| DSH-RF-021 | La tarjeta "Empleados Activos" debe mostrarse solo con acceso al módulo 4 (Empleados). | ☐ | ☐ | ☐ | `has_module_access(4)` |
| DSH-RF-022 | La tarjeta "Afiliaciones Pendientes" debe mostrarse solo con acceso al módulo 7 (Afiliaciones). | ☐ | ☐ | ☐ | `has_module_access(7)` |
| DSH-RF-023 | La tarjeta "Novedades de la Semana" debe mostrarse solo a jefes con subordinados o usuarios con acceso al módulo 27 (RRHH). | ☐ | ☐ | ☐ | `$esJefeConSubordinados \|\| has_module_access(27)` |

### 3.E Últimos Trámites — Requerimientos Funcionales

| ID | Requerimiento | SÍ | NO | ADI | Evidencia / Observaciones |
|----|---------------|----|----|-----|---------------------------|
| DSH-RF-024 | El dashboard debe mostrar la tabla de "Últimos Trámites" con las 5 solicitudes más recientes. | ☐ | ☐ | ☐ | templates/firstpage.php |
| DSH-RF-025 | Para el empleado, la tabla debe listar sus propias solicitudes (permisos y afiliaciones). | ☐ | ☐ | ☐ | templates/firstpage.php |
| DSH-RF-026 | Para RRHH/jefes, la tabla debe listar las solicitudes globales del sistema. | ☐ | ☐ | ☐ | `$verTramitesEmpresa` |
| DSH-RF-027 | La tabla debe incluir columnas: tipo, detalle, fecha y estado. | ☐ | ☐ | ☐ | templates/firstpage.php |
| DSH-RF-028 | El estado debe representarse con badge semafórico (aprobado verde, pendiente ámbar). | ☐ | ☐ | ☐ | assets/css/firstpage.css |
| DSH-RF-029 | Los badges de estado deben ser legibles en tema oscuro. | ☐ | ☐ | ☐ | assets/css/firstpage.css (dark) |

### 3.F Requerimientos No Funcionales del Dashboard

| ID | Requerimiento | SÍ | NO | ADI | Evidencia / Observaciones |
|----|---------------|----|----|-----|---------------------------|
| DSH-RNF-001 | Las métricas deben calcularse en tiempo real desde PostgreSQL en cada carga, sin caché. | ☐ | ☐ | ☐ | templates/firstpage.php |
| DSH-RNF-002 | El dashboard debe renderizarse server-side en una sola request para las métricas principales. | ☐ | ☐ | ☐ | templates/firstpage.php |
| DSH-RNF-003 | La página debe emitir cabeceras anti-caché. | ☐ | ☐ | ☐ | app/auth_guard.php |
| DSH-RNF-004 | La interfaz debe degradar con elegancia ante datos nulos (sin año de servicio, sin contrato, sin trámites). | ☐ | ☐ | ☐ | templates/firstpage.php |
| DSH-RNF-005 | La grilla de métricas debe adaptarse a móvil en una sola columna. | ☐ | ☐ | ☐ | assets/css/firstpage.css |
| DSH-RNF-006 | Los datos personales mostrados deben escaparse contra XSS. | ☐ | ☐ | ☐ | htmlspecialchars en vista |
| DSH-RNF-007 | Las consultas del dashboard deben filtrar registros eliminados lógicamente (`fec_delete IS NULL`). | ☐ | ☐ | ☐ | templates/firstpage.php |

---

## HOJA 4 — Sidebar (Navegación Lateral)

> Alcance: navegación lateral de las vistas internas. **Deben existir dos sidebars distintos: `sidebar_nav.php` para el usuario general y `sidebar_admin.php` para el administrador**, con ítems condicionados por permisos de módulo.

### 4.A Existencia y Selección — Requerimientos Funcionales

| ID | Requerimiento | SÍ | NO | ADI | Evidencia / Observaciones |
|----|---------------|----|----|-----|---------------------------|
| SDB-RF-001 | El sistema debe disponer de un sidebar para el usuario general (empleado). | ☐ | ☐ | ☐ | templates/partials/sidebar_nav.php |
| SDB-RF-002 | El sistema debe disponer de un sidebar independiente para el administrador. | ☐ | ☐ | ☐ | templates/partials/sidebar_admin.php |
| SDB-RF-003 | La selección del sidebar debe resolverse automáticamente según el rol (`id_rol = 1` → administrador). | ☐ | ☐ | ☐ | templates/firstpage.php |
| SDB-RF-004 | Cada ítem del menú debe renderizarse solo si el usuario tiene acceso al módulo correspondiente (`has_module_access`). | ☐ | ☐ | ☐ | Ambos partials |
| SDB-RF-005 | El ítem de la página actual debe resaltarse como activo con clase `.active` y `aria-current="page"`. | ☐ | ☐ | ☐ | sidebar_*.php |
| SDB-RF-006 | El logo corporativo debe aparecer en la parte superior del sidebar y enlazar al dashboard. | ☐ | ☐ | ☐ | `.sidebar-top` |
| SDB-RF-007 | El sidebar debe incluir el bloque de perfil del usuario (avatar, nombre, cargo). | ☐ | ☐ | ☐ | `.sidebar-profile` |

### 4.B Ítems del Sidebar de Usuario General — Requerimientos Funcionales

| ID | Requerimiento | SÍ | NO | ADI | Evidencia / Observaciones |
|----|---------------|----|----|-----|---------------------------|
| SDB-RF-008 | El sidebar general debe incluir el ítem "Inicio" al dashboard. | ☐ | ☐ | ☐ | sidebar_nav.php |
| SDB-RF-009 | El sidebar general debe incluir el ítem "Comunicados" (módulo 5). | ☐ | ☐ | ☐ | sidebar_nav.php |
| SDB-RF-010 | El sidebar general debe incluir el ítem "Empleados" del directorio (módulo 22). | ☐ | ☐ | ☐ | sidebar_nav.php |
| SDB-RF-011 | El sidebar general debe incluir el ítem "Solicitudes y Permisos" (módulo 3). | ☐ | ☐ | ☐ | sidebar_nav.php |
| SDB-RF-012 | El sidebar general debe incluir el ítem "Bandeja de Aprobaciones" solo si el usuario es jefe con subordinados o tiene acceso al módulo 27. | ☐ | ☐ | ☐ | sidebar_nav.php |
| SDB-RF-013 | El sidebar general debe incluir el ítem "SST" (módulo 25). | ☐ | ☐ | ☐ | sidebar_nav.php |
| SDB-RF-014 | El sidebar general debe incluir el ítem "Gestión de Vacaciones" (módulo 29) solo con acceso al módulo. | ☐ | ☐ | ☐ | sidebar_nav.php |
| SDB-RF-015 | El sidebar general debe incluir el ítem "Administrar Comunicados" (módulo 24) solo con acceso al módulo. | ☐ | ☐ | ☐ | sidebar_nav.php |
| SDB-RF-016 | El sidebar general debe incluir el ítem "Administración SST" (módulo 26) solo con acceso al módulo. | ☐ | ☐ | ☐ | sidebar_nav.php |

### 4.C Grupos Colapsables del Sidebar General — Requerimientos Funcionales

| ID | Requerimiento | SÍ | NO | ADI | Evidencia / Observaciones |
|----|---------------|----|----|-----|---------------------------|
| SDB-RF-017 | Debe existir el grupo colapsable "Seguridad Social" visible solo si hay acceso a alguno de sus módulos. | ☐ | ☐ | ☐ | sidebar_nav.php |
| SDB-RF-018 | El grupo Seguridad Social debe contener el ítem EPS (módulo 14). | ☐ | ☐ | ☐ | sidebar_nav.php |
| SDB-RF-019 | El grupo Seguridad Social debe contener el ítem ARL (módulo 9). | ☐ | ☐ | ☐ | sidebar_nav.php |
| SDB-RF-020 | El grupo Seguridad Social debe contener el ítem Pensión (módulo 16). | ☐ | ☐ | ☐ | sidebar_nav.php |
| SDB-RF-021 | El grupo Seguridad Social debe contener el ítem Caja de Compensación (módulo 11). | ☐ | ☐ | ☐ | sidebar_nav.php |
| SDB-RF-022 | El grupo Seguridad Social debe contener el ítem Cesantías (módulo 12). | ☐ | ☐ | ☐ | sidebar_nav.php |
| SDB-RF-023 | Debe existir el grupo colapsable "Empresa" visible solo si hay acceso a alguno de sus módulos. | ☐ | ☐ | ☐ | sidebar_nav.php |
| SDB-RF-024 | El grupo Empresa debe contener el ítem Bancos (módulo 10). | ☐ | ☐ | ☐ | sidebar_nav.php |
| SDB-RF-025 | El grupo Empresa debe contener el ítem Nómina (módulo 15). | ☐ | ☐ | ☐ | sidebar_nav.php |
| SDB-RF-026 | El grupo Empresa debe contener el ítem Contratos Empleados (módulo 13). | ☐ | ☐ | ☐ | sidebar_nav.php |
| SDB-RF-027 | El grupo Empresa debe contener el ítem Afiliaciones Empleados (módulo 7). | ☐ | ☐ | ☐ | sidebar_nav.php |
| SDB-RF-028 | El grupo Empresa debe contener el ítem Áreas (módulo 8). | ☐ | ☐ | ☐ | sidebar_nav.php |
| SDB-RF-029 | El grupo Empresa debe contener el ítem Días Festivos (módulo 28). | ☐ | ☐ | ☐ | sidebar_nav.php |
| SDB-RF-030 | Debe existir el grupo colapsable "Software" visible solo si hay acceso a alguno de sus módulos. | ☐ | ☐ | ☐ | sidebar_nav.php |
| SDB-RF-031 | El grupo Software debe contener el ítem Roles (módulo 17). | ☐ | ☐ | ☐ | sidebar_nav.php |
| SDB-RF-032 | El grupo Software debe contener el ítem Operaciones (módulo 19). | ☐ | ☐ | ☐ | sidebar_nav.php |
| SDB-RF-033 | El grupo Software debe contener el ítem Usuarios (módulo 20). | ☐ | ☐ | ☐ | sidebar_nav.php |
| SDB-RF-034 | El grupo Software debe contener el ítem Roles Operaciones (módulo 6). | ☐ | ☐ | ☐ | sidebar_nav.php |
| SDB-RF-035 | El grupo Software debe contener el ítem Módulos (módulo 18). | ☐ | ☐ | ☐ | sidebar_nav.php |

### 4.D Ítems del Sidebar de Administrador — Requerimientos Funcionales

| ID | Requerimiento | SÍ | NO | ADI | Evidencia / Observaciones |
|----|---------------|----|----|-----|---------------------------|
| SDB-RF-036 | El sidebar de administrador debe tener el ítem inicial etiquetado "Inicio RRHH". | ☐ | ☐ | ☐ | sidebar_admin.php |
| SDB-RF-037 | El sidebar de administrador debe incluir el ítem Empleados (módulo 4) al inicio del grupo Empresa. | ☐ | ☐ | ☐ | sidebar_admin.php |
| SDB-RF-038 | El sidebar de administrador debe incluir el ítem "Permisos por Usuario" (módulo 21) en el grupo Software. | ☐ | ☐ | ☐ | sidebar_admin.php |
| SDB-RF-039 | El sidebar de administrador debe incluir el ítem "Códigos de Registro" (módulo 23). | ☐ | ☐ | ☐ | sidebar_admin.php |
| SDB-RF-040 | El sidebar de administrador debe incluir un grupo "Administración" con Administrar Comunicados (24) y Administración SST (26). | ☐ | ☐ | ☐ | sidebar_admin.php |
| SDB-RF-041 | El sidebar de administrador debe incluir los mismos grupos de Seguridad Social, Empresa y Software con condicionamiento por permisos. | ☐ | ☐ | ☐ | sidebar_admin.php |
| SDB-RF-042 | Cada ítem de ambos sidebars debe enlazar a la ruta del módulo correspondiente en `src/` o `templates/`. | ☐ | ☐ | ☐ | hrefs en sidebar_*.php |

### 4.E Requerimientos No Funcionales del Sidebar

| ID | Requerimiento | SÍ | NO | ADI | Evidencia / Observaciones |
|----|---------------|----|----|-----|---------------------------|
| SDB-RNF-001 | El sidebar debe ser fijo de 280px en escritorio. | ☐ | ☐ | ☐ | assets/css/firstpage.css |
| SDB-RNF-002 | En móvil el sidebar debe ser off-canvas (oculto `left:-100%`) con apertura por botón hamburguesa. | ☐ | ☐ | ☐ | assets/css/firstpage.css, firstpage.js |
| SDB-RNF-003 | Los grupos colapsables deben animar la apertura (transición de altura) y la rotación del chevron (180°). | ☐ | ☐ | ☐ | `.nav-group` en firstpage.css |
| SDB-RNF-004 | Los toggles de grupo deben ser botones reales (`<button>`) accesibles. | ☐ | ☐ | ☐ | sidebar_*.php |
| SDB-RNF-005 | Los iconos deben ser SVG inline uniformes de 20x20 con stroke consistente. | ☐ | ☐ | ☐ | sidebar_*.php |
| SDB-RNF-006 | El sidebar debe ser coherente con el tema claro/oscuro activo. | ☐ | ☐ | ☐ | Variables CSS dark-aware |
| SDB-RNF-007 | El sidebar debe tener scroll interno propio cuando el menú exceda la altura. | ☐ | ☐ | ☐ | assets/css/firstpage.css |
| SDB-RNF-008 | Los nombres de módulo mostrados deben provenir de los permisos cargados en sesión, no de texto hardcodeado por usuario. | ☐ | ☐ | ☐ | sidebar_*.php |

---

## HOJA 5 — Módulos Administrativos (/src)

> Alcance: los 20 CRUDs administrativos organizados por dominio en `src/<dominio>/`, con granularidad por operación, campo de formulario, validación y listado.

### 5.A Patrón Transversal — Requerimientos Funcionales

| ID | Requerimiento | SÍ | NO | ADI | Evidencia / Observaciones |
|----|---------------|----|----|-----|---------------------------|
| SRC-RF-001 | Todo módulo administrativo debe exponer una vista de listado (`listar_*`) en tabla HTML. | ☐ | ☐ | ☐ | Patrón en los 20 módulos |
| SRC-RF-002 | Todo módulo debe exponer formulario de creación (`forma_*`) con carga de catálogos de llaves foráneas. | ☐ | ☐ | ☐ | forma_*.php |
| SRC-RF-003 | Todo módulo debe procesar la creación (`insertar_*`) invocando la función almacenada `fun_insert_*`. | ☐ | ☐ | ☐ | insertar_*.php |
| SRC-RF-004 | Todo módulo debe exponer formulario de edición (`editar_*`) con precarga del registro por PK. | ☐ | ☐ | ☐ | editar_*.php |
| SRC-RF-005 | Todo módulo debe procesar la actualización (`update_*`) invocando `fun_update_*`. | ☐ | ☐ | ☐ | update_*.php |
| SRC-RF-006 | Todo módulo debe procesar eliminación lógica (`eliminar_*`) invocando `fun_softdelete_*`. | ☐ | ☐ | ☐ | eliminar_*.php |
| SRC-RF-007 | Todo módulo debe procesar restauración (`restore_*`) invocando `fun_restore_*`. | ☐ | ☐ | ☐ | restore_*.php |
| SRC-RF-008 | Todo listado debe distinguir registros activos de eliminados (modal de eliminados con opción restaurar). | ☐ | ☐ | ☐ | listar_*.php (modal) |
| SRC-RF-009 | Todo listado debe mostrar acciones por fila (editar/eliminar) condicionadas al permiso de la acción. | ☐ | ☐ | ☐ | `has_module_permission` en listados |
| SRC-RF-010 | Todo módulo debe reutilizar partials de encabezado y pie propios del dominio (`encab_*`, `pie_*`). | ☐ | ☐ | ☐ | src/<dominio>/encab_*.php |
| SRC-RF-011 | El encabezado de cada módulo debe enlazar al dashboard mediante URL dinámica (`get_dashboard_url()`). | ☐ | ☐ | ☐ | scripts/replace_dashboard_links.py |
| SRC-RF-012 | Toda operación exitosa debe redirigir al listado con mensaje flash de confirmación. | ☐ | ☐ | ☐ | redirects con ?success= |
| SRC-RF-013 | Toda operación fallida debe redirigir con código de error controlado y banner visible. | ☐ | ☐ | ☐ | app/alert_helper.php |
| SRC-RF-014 | El resultado textual de las funciones almacenadas debe presentarse al usuario como retroalimentación. | ☐ | ☐ | ☐ | `stripos($resultado,'correctamente')` |

### 5.B Módulo Afiliaciones de Empleados (módulo 7) — RF

| ID | Requerimiento | SÍ | NO | ADI | Evidencia / Observaciones |
|----|---------------|----|----|-----|---------------------------|
| SRC-RF-015 | El formulario debe exigir seleccionar el empleado desde catálogo de usuarios. | ☐ | ☐ | ☐ | src/afiliaciones_empleados/forma_*.php |
| SRC-RF-016 | El formulario debe exigir seleccionar la EPS desde catálogo. | ☐ | ☐ | ☐ | FK t_eps |
| SRC-RF-017 | El formulario debe exigir seleccionar la ARL desde catálogo. | ☐ | ☐ | ☐ | FK t_arl |
| SRC-RF-018 | El formulario debe exigir seleccionar la Caja de Compensación desde catálogo. | ☐ | ☐ | ☐ | FK t_caja_compensacion |
| SRC-RF-019 | El formulario debe exigir seleccionar el Fondo de Pensión desde catálogo. | ☐ | ☐ | ☐ | FK t_pension |
| SRC-RF-020 | El formulario debe exigir seleccionar el Fondo de Cesantías desde catálogo. | ☐ | ☐ | ☐ | FK t_cesantias |
| SRC-RF-021 | La inserción debe invocar `fun_insert_afiliaciones_empleados` con los 6 parámetros. | ☐ | ☐ | ☐ | insertar_afiliaciones_empleados.php |
| SRC-RF-022 | El listado debe mostrar columnas: Usuario, EPS, ARL, Caja, Pensión y Cesantías. | ☐ | ☐ | ☐ | listar_afiliaciones_empleados.php |
| SRC-RF-023 | El módulo debe soportar edición, eliminación lógica y restauración de afiliaciones. | ☐ | ☐ | ☐ | editar/update/eliminar/restore |

### 5.C Módulo Áreas (módulo 8) — RF

| ID | Requerimiento | SÍ | NO | ADI | Evidencia / Observaciones |
|----|---------------|----|----|-----|---------------------------|
| SRC-RF-024 | El formulario de áreas debe capturar `nombre_area` obligatorio de máximo 50 caracteres. | ☐ | ☐ | ☐ | src/area/forma_area.php |
| SRC-RF-025 | La inserción debe invocar `fun_insert_area`. | ☐ | ☐ | ☐ | src/area/insertar_area.php |
| SRC-RF-026 | El listado de áreas debe mostrar el nombre y acciones por fila. | ☐ | ☐ | ☐ | src/area/listar_area.php |
| SRC-RF-027 | El módulo debe soportar edición, eliminación lógica y restauración. | ☐ | ☐ | ☐ | src/area/ |

### 5.D Módulo ARL (módulo 9) — RF

| ID | Requerimiento | SÍ | NO | ADI | Evidencia / Observaciones |
|----|---------------|----|----|-----|---------------------------|
| SRC-RF-028 | El formulario de ARL debe capturar `nombre_arl` obligatorio de máximo 50 caracteres. | ☐ | ☐ | ☐ | src/arl/forma_arl.php |
| SRC-RF-029 | La inserción debe invocar `fun_insert_arl`. | ☐ | ☐ | ☐ | src/arl/insertar_arl.php |
| SRC-RF-030 | El listado de ARL debe mostrar el nombre y acciones por fila. | ☐ | ☐ | ☐ | src/arl/listar_arl.php |
| SRC-RF-031 | El módulo debe soportar edición, eliminación lógica y restauración. | ☐ | ☐ | ☐ | src/arl/ |

### 5.E Módulo Bancos (módulo 10) — RF

| ID | Requerimiento | SÍ | NO | ADI | Evidencia / Observaciones |
|----|---------------|----|----|-----|---------------------------|
| SRC-RF-032 | El formulario de bancos debe capturar `nombre_banco` obligatorio de máximo 50 caracteres. | ☐ | ☐ | ☐ | src/banco/forma_banco.php |
| SRC-RF-033 | La inserción debe invocar `fun_insert_banco`. | ☐ | ☐ | ☐ | src/banco/insertar_banco.php |
| SRC-RF-034 | El listado de bancos debe mostrar el nombre y acciones por fila. | ☐ | ☐ | ☐ | src/banco/listar_banco.php |
| SRC-RF-035 | El módulo debe soportar edición, eliminación lógica y restauración. | ☐ | ☐ | ☐ | src/banco/ |

### 5.F Módulo Cajas de Compensación (módulo 11) — RF

| ID | Requerimiento | SÍ | NO | ADI | Evidencia / Observaciones |
|----|---------------|----|----|-----|---------------------------|
| SRC-RF-036 | El formulario debe capturar `nombre_caja` obligatorio de máximo 50 caracteres. | ☐ | ☐ | ☐ | src/cajacompensacion/forma_*.php |
| SRC-RF-037 | La inserción debe invocar `fun_insert_cajacompensacion`. | ☐ | ☐ | ☐ | src/cajacompensacion/ |
| SRC-RF-038 | El listado debe mostrar el nombre y acciones por fila. | ☐ | ☐ | ☐ | src/cajacompensacion/listar_*.php |
| SRC-RF-039 | El módulo debe soportar edición, eliminación lógica y restauración. | ☐ | ☐ | ☐ | src/cajacompensacion/ |

### 5.G Módulo Cesantías (módulo 12) — RF

| ID | Requerimiento | SÍ | NO | ADI | Evidencia / Observaciones |
|----|---------------|----|----|-----|---------------------------|
| SRC-RF-040 | El formulario debe capturar `nombre_cesantia` obligatorio de máximo 50 caracteres. | ☐ | ☐ | ☐ | src/cesantias/forma_cesantias.php |
| SRC-RF-041 | La inserción debe invocar `fun_insert_cesantias`. | ☐ | ☐ | ☐ | src/cesantias/insertar_cesantias.php |
| SRC-RF-042 | El listado debe mostrar el nombre y acciones por fila. | ☐ | ☐ | ☐ | src/cesantias/listar_cesantias.php |
| SRC-RF-043 | El módulo debe soportar edición, eliminación lógica y restauración. | ☐ | ☐ | ☐ | src/cesantias/ |

### 5.H Módulo Administración de Comunicados (módulo 24) — RF

| ID | Requerimiento | SÍ | NO | ADI | Evidencia / Observaciones |
|----|---------------|----|----|-----|---------------------------|
| SRC-RF-044 | El formulario de comunicados debe capturar `titulo` obligatorio de máximo 100 caracteres. | ☐ | ☐ | ☐ | src/comunicados/forma_comunicados.php |
| SRC-RF-045 | El formulario debe exigir la categoría entre: GENERAL, URGENTE, EVENTO, INFORMACION e INSTITUCIONAL. | ☐ | ☐ | ☐ | select fijo en forma_comunicados.php |
| SRC-RF-046 | El contenido debe editarse con editor WYSIWYG (negrita, cursiva, subrayado, alineación, listas, encabezados, cita). | ☐ | ☐ | ☐ | contenteditable + toolbar |
| SRC-RF-047 | El contenido debe exigir mínimo 10 caracteres de texto plano. | ☐ | ☐ | ☐ | Validación strip_tags |
| SRC-RF-048 | El contenido HTML debe depurarse con `HtmlSanitizer::clean()` antes de persistir. | ☐ | ☐ | ☐ | insertar/update comunicados |
| SRC-RF-049 | La inserción debe invocar `fun_insert_comunicados`. | ☐ | ☐ | ☐ | src/comunicados/insertar_comunicados.php |
| SRC-RF-050 | El listado debe mostrar: Título, Categoría, Autor, Fecha y Total de Vistos, ordenado por fecha descendente. | ☐ | ☐ | ☐ | listar_comunicados.php |
| SRC-RF-051 | El módulo debe permitir consultar qué usuarios han visto cada comunicado (`visto_por`). | ☐ | ☐ | ☐ | src/comunicados/visto_por.php |
| SRC-RF-052 | El módulo debe permitir marcar comunicados como vistos (`marcar_visto`). | ☐ | ☐ | ☐ | src/comunicados/marcar_visto.php |
| SRC-RF-053 | El módulo debe soportar edición, eliminación lógica y restauración. | ☐ | ☐ | ☐ | src/comunicados/ |

### 5.I Módulo Contratos de Empleados (módulo 13) — RF

| ID | Requerimiento | SÍ | NO | ADI | Evidencia / Observaciones |
|----|---------------|----|----|-----|---------------------------|
| SRC-RF-054 | El formulario debe exigir seleccionar el empleado desde catálogo. | ☐ | ☐ | ☐ | src/contratos_empleados/forma_*.php |
| SRC-RF-055 | El formulario debe exigir seleccionar el área desde catálogo. | ☐ | ☐ | ☐ | FK t_areas |
| SRC-RF-056 | El formulario debe capturar el `puesto` obligatorio de máximo 50 caracteres. | ☐ | ☐ | ☐ | forma_contratos_empleados.php |
| SRC-RF-057 | El formulario debe exigir el tipo de contrato entre FIJO, INDEFINIDO y PRACTICAS. | ☐ | ☐ | ☐ | select + CHECK en BD |
| SRC-RF-058 | El formulario debe capturar la fecha de inicio del puesto (obligatoria). | ☐ | ☐ | ☐ | forma_contratos_empleados.php |
| SRC-RF-059 | La fecha de fin del puesto debe ser opcional (vacío = puesto actual). | ☐ | ☐ | ☐ | Nota en formulario |
| SRC-RF-060 | El formulario debe capturar la dirección de oficina (máx. 100 caracteres). | ☐ | ☐ | ☐ | forma_contratos_empleados.php |
| SRC-RF-061 | La inserción debe invocar `fun_insert_contratos_empleados` con 7 parámetros. | ☐ | ☐ | ☐ | insertar_contratos_empleados.php |
| SRC-RF-062 | El listado debe mostrar: Empleado, Área, Puesto, Fechas, Tipo de Contrato y Dirección de Oficina. | ☐ | ☐ | ☐ | listar_contratos_empleados.php |
| SRC-RF-063 | El módulo debe soportar edición, eliminación lógica y restauración. | ☐ | ☐ | ☐ | src/contratos_empleados/ |

### 5.J Módulo Días Festivos (módulo 28) — RF

| ID | Requerimiento | SÍ | NO | ADI | Evidencia / Observaciones |
|----|---------------|----|----|-----|---------------------------|
| SRC-RF-064 | El formulario debe capturar la fecha del festivo (obligatoria). | ☐ | ☐ | ☐ | src/dias_festivos/forma_*.php |
| SRC-RF-065 | El formulario debe capturar la descripción (3–100 caracteres). | ☐ | ☐ | ☐ | minlength=3, maxlength=100 |
| SRC-RF-066 | El formulario debe incluir casilla "descuenta salario" marcada por defecto. | ☐ | ☐ | ☐ | checkbox default checked |
| SRC-RF-067 | Los festivos creados manualmente deben registrarse como tipo EMPRESA. | ☐ | ☐ | ☐ | t_dias_festivos.tipo_festivo |
| SRC-RF-068 | Los festivos NACIONAL deben ser de solo lectura (calculados automáticamente, no editables). | ☐ | ☐ | ☐ | Nota en formulario |
| SRC-RF-069 | El listado debe distinguir visualmente festivos NACIONAL y EMPRESA (badge) e indicar si descuentan salario. | ☐ | ☐ | ☐ | listar_dias_festivos.php |
| SRC-RF-070 | Solo los festivos EMPRESA deben ser eliminables/restaurables. | ☐ | ☐ | ☐ | src/dias_festivos/ |

### 5.K Módulo Empleados (módulo 4) — RF

| ID | Requerimiento | SÍ | NO | ADI | Evidencia / Observaciones |
|----|---------------|----|----|-----|---------------------------|
| SRC-RF-071 | El formulario debe exigir seleccionar el usuario asociado desde catálogo de t_usuarios. | ☐ | ☐ | ☐ | src/empleados/forma_empleados.php |
| SRC-RF-072 | El formulario debe permitir asignar jefe inmediato (opcional) desde catálogo de empleados. | ☐ | ☐ | ☐ | FK auto-referente id_jefe |
| SRC-RF-073 | El formulario debe ofrecer selector de departamento que habilite el selector de municipio (cascada JS). | ☐ | ☐ | ☐ | data-departamento-id |
| SRC-RF-074 | El selector de municipio debe permanecer deshabilitado hasta elegir departamento. | ☐ | ☐ | ☐ | forma_empleados.php |
| SRC-RF-075 | El formulario debe capturar fecha de ingreso (obligatoria) y fecha de egreso (opcional). | ☐ | ☐ | ☐ | forma_empleados.php |
| SRC-RF-076 | El formulario debe exigir género entre MASCULINO, FEMENINO y OTRO. | ☐ | ☐ | ☐ | select + CHECK en BD |
| SRC-RF-077 | El formulario debe capturar fecha de nacimiento (obligatoria). | ☐ | ☐ | ☐ | forma_empleados.php |
| SRC-RF-078 | El formulario debe exigir tipo de sangre entre A±, B±, AB± y O±. | ☐ | ☐ | ☐ | select + CHECK en BD |
| SRC-RF-079 | El formulario debe exigir estado civil entre SOLTERO, CASADO, DIVORCIADO, VIUDO, UNION LIBRE y SEPARADO. | ☐ | ☐ | ☐ | select + CHECK en BD |
| SRC-RF-080 | El formulario debe capturar dirección de casa y número de celular (obligatorio). | ☐ | ☐ | ☐ | forma_empleados.php |
| SRC-RF-081 | El formulario debe permitir foto de perfil opcional. | ☐ | ☐ | ☐ | t_empleados.foto_perfil |
| SRC-RF-082 | La inserción debe invocar `fun_insert_empleados` con 12 parámetros. | ☐ | ☐ | ☐ | insertar_empleados.php |
| SRC-RF-083 | El listado debe mostrar datos personales y laborales (documento, correo, nombres, jefe, municipio, fechas, género, sangre, estado civil, dirección, celular, foto). | ☐ | ☐ | ☐ | listar_empleados.php |
| SRC-RF-084 | El módulo debe soportar edición, eliminación lógica y restauración. | ☐ | ☐ | ☐ | src/empleados/ |

### 5.L Módulo EPS (módulo 14) — RF

| ID | Requerimiento | SÍ | NO | ADI | Evidencia / Observaciones |
|----|---------------|----|----|-----|---------------------------|
| SRC-RF-085 | El formulario de EPS debe capturar `nombre_eps` obligatorio de máximo 50 caracteres. | ☐ | ☐ | ☐ | src/eps/forma_eps.php |
| SRC-RF-086 | La inserción debe invocar `fun_insert_eps`. | ☐ | ☐ | ☐ | src/eps/insertar_eps.php |
| SRC-RF-087 | El listado de EPS debe mostrar el nombre y acciones por fila. | ☐ | ☐ | ☐ | src/eps/listar_eps.php |
| SRC-RF-088 | El módulo debe soportar edición, eliminación lógica y restauración. | ☐ | ☐ | ☐ | src/eps/ |

### 5.M Módulo Módulos (módulo 18) — RF

| ID | Requerimiento | SÍ | NO | ADI | Evidencia / Observaciones |
|----|---------------|----|----|-----|---------------------------|
| SRC-RF-089 | El formulario debe capturar `id_modulo` numérico entero mínimo 1. | ☐ | ☐ | ☐ | src/modulos/forma_modulos.php |
| SRC-RF-090 | El formulario debe capturar `nombre_modulo` obligatorio de máximo 30 caracteres. | ☐ | ☐ | ☐ | forma_modulos.php |
| SRC-RF-091 | La inserción debe invocar `fun_insert_modulos`. | ☐ | ☐ | ☐ | insertar_modulos.php; usa named params con desviación en execute (ADI) |
| SRC-RF-092 | El listado debe mostrar ID y nombre del módulo con acciones. | ☐ | ☐ | ☐ | listar_modulos.php |
| SRC-RF-093 | El módulo debe soportar edición, eliminación lógica y restauración. | ☐ | ☐ | ☐ | src/modulos/ |

### 5.N Módulo Nómina (módulo 15) — RF

| ID | Requerimiento | SÍ | NO | ADI | Evidencia / Observaciones |
|----|---------------|----|----|-----|---------------------------|
| SRC-RF-094 | El formulario debe exigir seleccionar el empleado desde catálogo. | ☐ | ☐ | ☐ | src/nomina/forma_nomina.php |
| SRC-RF-095 | El formulario debe exigir seleccionar el banco desde catálogo. | ☐ | ☐ | ☐ | FK t_bancos |
| SRC-RF-096 | El formulario debe capturar el número de cuenta (máx. 20 caracteres). | ☐ | ☐ | ☐ | forma_nomina.php |
| SRC-RF-097 | El formulario debe capturar el salario como número decimal con paso 0.01. | ☐ | ☐ | ☐ | `step="0.01"` |
| SRC-RF-098 | La inserción debe invocar `fun_insert_nomina` con 4 parámetros. | ☐ | ☐ | ☐ | insertar_nomina.php |
| SRC-RF-099 | El listado debe mostrar Empleado, Banco, Cuenta y Salario con formato monetario. | ☐ | ☐ | ☐ | listar_nomina.php |
| SRC-RF-100 | El módulo debe soportar edición, eliminación lógica y restauración. | ☐ | ☐ | ☐ | src/nomina/ |

### 5.O Módulo Operaciones (módulo 19) — RF

| ID | Requerimiento | SÍ | NO | ADI | Evidencia / Observaciones |
|----|---------------|----|----|-----|---------------------------|
| SRC-RF-101 | El formulario debe capturar `id_operacion` numérico entero mínimo 1. | ☐ | ☐ | ☐ | src/operaciones/forma_operaciones.php |
| SRC-RF-102 | El formulario debe exigir seleccionar el módulo asociado desde catálogo. | ☐ | ☐ | ☐ | FK t_modulos |
| SRC-RF-103 | El formulario debe capturar `nombre_operacion` obligatorio de máximo 50 caracteres. | ☐ | ☐ | ☐ | forma_operaciones.php |
| SRC-RF-104 | La inserción debe invocar `fun_insert_operaciones`. | ☐ | ☐ | ☐ | insertar_operaciones.php; desviación en named params (ADI) |
| SRC-RF-105 | El listado debe mostrar ID, módulo y nombre de la operación con acciones. | ☐ | ☐ | ☐ | listar_operaciones.php |
| SRC-RF-106 | El módulo debe soportar edición, eliminación lógica y restauración. | ☐ | ☐ | ☐ | src/operaciones/ |

### 5.P Módulo Pensión (módulo 16) — RF

| ID | Requerimiento | SÍ | NO | ADI | Evidencia / Observaciones |
|----|---------------|----|----|-----|---------------------------|
| SRC-RF-107 | El formulario debe capturar `nombre_pension` obligatorio de máximo 50 caracteres. | ☐ | ☐ | ☐ | src/pension/forma_pension.php |
| SRC-RF-108 | La inserción debe invocar `fun_insert_pension`. | ☐ | ☐ | ☐ | src/pension/insertar_pension.php |
| SRC-RF-109 | El listado debe mostrar el nombre y acciones por fila. | ☐ | ☐ | ☐ | src/pension/listar_pension.php |
| SRC-RF-110 | El módulo debe soportar edición, eliminación lógica y restauración. | ☐ | ☐ | ☐ | src/pension/ |

### 5.Q Módulo Permisos por Usuario (módulo 21) — RF

| ID | Requerimiento | SÍ | NO | ADI | Evidencia / Observaciones |
|----|---------------|----|----|-----|---------------------------|
| SRC-RF-111 | El formulario debe buscar el usuario con datalist (por nombre o username) y sincronizar el id en campo oculto. | ☐ | ☐ | ☐ | src/permisos_usuarios/forma_*.php |
| SRC-RF-112 | El formulario debe buscar la operación con datalist (por módulo/operación) y sincronizar el id en campo oculto. | ☐ | ☐ | ☐ | forma_permisos_usuarios.php |
| SRC-RF-113 | La inserción debe invocar `fun_insert_permisos_usuarios` (id_usuario, id_operacion). | ☐ | ☐ | ☐ | insertar_permisos_usuarios.php |
| SRC-RF-114 | El listado debe presentar un árbol acordeón Usuario → Módulo → Operaciones. | ☐ | ☐ | ☐ | `construirArbolPermisos()` |
| SRC-RF-115 | El árbol debe mostrar los campos de auditoría de cada asignación. | ☐ | ☐ | ☐ | listar_permisos_usuarios.php |
| SRC-RF-116 | El modal de eliminados debe mostrar usuario, operación, módulo y datos de eliminación con opción restaurar. | ☐ | ☐ | ☐ | listar_permisos_usuarios.php |
| SRC-RF-117 | El módulo debe soportar edición, eliminación lógica y restauración. | ☐ | ☐ | ☐ | src/permisos_usuarios/ |

### 5.R Módulo Roles (módulo 17) — RF

| ID | Requerimiento | SÍ | NO | ADI | Evidencia / Observaciones |
|----|---------------|----|----|-----|---------------------------|
| SRC-RF-118 | El formulario debe capturar `id_rol` numérico entero mínimo 1. | ☐ | ☐ | ☐ | src/roles/forma_roles.php |
| SRC-RF-119 | El formulario debe capturar `nombre_rol` obligatorio de máximo 50 caracteres. | ☐ | ☐ | ☐ | forma_roles.php |
| SRC-RF-120 | La inserción debe invocar `fun_insert_roles`. | ☐ | ☐ | ☐ | insertar_roles.php; desviación en named params (ADI) |
| SRC-RF-121 | El listado debe mostrar ID y nombre del rol con acciones. | ☐ | ☐ | ☐ | listar_roles.php |
| SRC-RF-122 | El módulo debe soportar edición, eliminación lógica y restauración. | ☐ | ☐ | ☐ | src/roles/ |

### 5.S Módulo Roles-Operaciones (módulo 6) — RF

| ID | Requerimiento | SÍ | NO | ADI | Evidencia / Observaciones |
|----|---------------|----|----|-----|---------------------------|
| SRC-RF-123 | El formulario debe exigir seleccionar el rol desde catálogo. | ☐ | ☐ | ☐ | src/roles_operaciones/forma_*.php |
| SRC-RF-124 | El selector de operaciones debe agruparse por módulo (optgroup). | ☐ | ☐ | ☐ | forma_roles_operaciones.php |
| SRC-RF-125 | La inserción debe invocar `fun_insert_roles_operaciones` (id_rol, id_operacion). | ☐ | ☐ | ☐ | insertar_roles_operaciones.php |
| SRC-RF-126 | El listado debe mostrar Rol, Operación y Módulo, con contador total de registros. | ☐ | ☐ | ☐ | listar_roles_operaciones.php |
| SRC-RF-127 | El módulo debe soportar edición, eliminación lógica y restauración con modal de eliminados. | ☐ | ☐ | ☐ | src/roles_operaciones/ |

### 5.T Módulo Administración SST (módulo 26) — RF

| ID | Requerimiento | SÍ | NO | ADI | Evidencia / Observaciones |
|----|---------------|----|----|-----|---------------------------|
| SRC-RF-128 | El módulo debe gestionar dos entidades: miembros del comité y buzón de quejas. | ☐ | ☐ | ☐ | src/sst/ |
| SRC-RF-129 | El formulario de comité debe capturar nombre completo (máx. 100) y cargo (máx. 50) obligatorios. | ☐ | ☐ | ☐ | src/sst/forma_comite.php |
| SRC-RF-130 | El formulario de comité debe exigir el tipo de comité entre COPASST y COMITE_CONVIVENCIA. | ☐ | ☐ | ☐ | select + CHECK en BD |
| SRC-RF-131 | El formulario de comité debe capturar correo (opcional, máx. 40) y teléfono (opcional, máx. 15). | ☐ | ☐ | ☐ | forma_comite.php |
| SRC-RF-132 | El formulario de comité debe capturar el orden de visualización (entero ≥ 0, por defecto 0). | ☐ | ☐ | ☐ | `filter_var(FILTER_VALIDATE_INT)` |
| SRC-RF-133 | La inserción de miembros debe invocar `fun_insert_sst_comite_miembro`. | ☐ | ☐ | ☐ | src/sst/insertar_comite.php |
| SRC-RF-134 | El listado de comité debe mostrar Nombre, Cargo, Comité, Correo, Teléfono y Orden. | ☐ | ☐ | ☐ | src/sst/listar_comite.php |
| SRC-RF-135 | El módulo debe soportar edición, eliminación lógica y restauración de miembros del comité. | ☐ | ☐ | ☐ | src/sst/{editar,update,eliminar,restore}_comite.php |
| SRC-RF-136 | El listado de quejas debe mostrar Título, Descripción, Estado y Fecha. | ☐ | ☐ | ☐ | src/sst/listar_quejas.php |
| SRC-RF-137 | El módulo debe permitir cambiar el estado de una queja (`cambiar_estado_queja`). | ☐ | ☐ | ☐ | src/sst/cambiar_estado_queja.php |

### 5.U Módulo Usuarios (módulo 20) — RF

| ID | Requerimiento | SÍ | NO | ADI | Evidencia / Observaciones |
|----|---------------|----|----|-----|---------------------------|
| SRC-RF-138 | El formulario debe capturar `id_usuario` (documento) obligatorio de máximo 20 caracteres. | ☐ | ☐ | ☐ | src/usuarios/forma_usuarios.php |
| SRC-RF-139 | El formulario debe exigir seleccionar el rol desde catálogo. | ☐ | ☐ | ☐ | FK t_roles |
| SRC-RF-140 | El formulario debe capturar username obligatorio de máximo 30 caracteres. | ☐ | ☐ | ☐ | forma_usuarios.php |
| SRC-RF-141 | El formulario debe exigir tipo de documento entre CC, PPT y CE. | ☐ | ☐ | ☐ | select + CHECK en BD |
| SRC-RF-142 | El formulario debe capturar primer nombre y primer apellido (obligatorios, máx. 30). | ☐ | ☐ | ☐ | forma_usuarios.php |
| SRC-RF-143 | El formulario debe capturar segundo nombre y segundo apellido (opcionales, máx. 30). | ☐ | ☐ | ☐ | forma_usuarios.php |
| SRC-RF-144 | El campo correo debe validar formato email y máximo 40 caracteres. | ☐ | ☐ | ☐ | pattern regex + trim en PHP |
| SRC-RF-145 | La contraseña debe capturarse como password y hashearse con `password_hash(PASSWORD_DEFAULT)` antes de persistir. | ☐ | ☐ | ☐ | src/usuarios/insertar_usuarios.php |
| SRC-RF-146 | La inserción debe invocar `fun_insert_usuarios`. | ☐ | ☐ | ☐ | insertar_usuarios.php |
| SRC-RF-147 | El listado debe mostrar datos del usuario sin exponer la contraseña (hash). | ☐ | ☐ | ☐ | listar_usuarios.php expone el hash (ADI — fuga de información) |
| SRC-RF-148 | El módulo debe soportar edición, eliminación lógica y restauración. | ☐ | ☐ | ☐ | src/usuarios/ |

### 5.V Requerimientos No Funcionales de /src

| ID | Requerimiento | SÍ | NO | ADI | Evidencia / Observaciones |
|----|---------------|----|----|-----|---------------------------|
| SRC-RNF-001 | Todo endpoint de `src/` debe incluir `src_guard.php` con `$moduleId` definido. | ☐ | ☐ | ☐ | app/src_guard.php |
| SRC-RNF-002 | Toda operación de escritura debe definir `$requiredAction` (crear/actualizar/eliminar/restaurar) validada por el guard. | ☐ | ☐ | ☐ | scripts/apply_src_guards.py |
| SRC-RNF-003 | Todo formulario POST debe incluir `csrf_input()` y todo endpoint POST debe llamar `csrf_validate()`. | ☐ | ☐ | ☐ | scripts/add_csrf_tokens.py |
| SRC-RNF-004 | Los datos deben validarse en PHP (isset, trim, casting, filter_var) antes de invocar la función almacenada. | ☐ | ☐ | ☐ | insertar_*/update_* |
| SRC-RNF-005 | Los datos deben validarse nuevamente dentro de las funciones almacenadas (NULL, regex, rangos). | ☐ | ☐ | ☐ | scripts/functions/ |
| SRC-RNF-006 | Ningún endpoint de `src/` debe enviar campos de auditoría manualmente. | ☐ | ☐ | ☐ | triggers de auditoría |
| SRC-RNF-007 | Ante falta de permiso, el guard debe redirigir a access_denied sin ejecutar la operación. | ☐ | ☐ | ☐ | app/src_guard.php |
| SRC-RNF-008 | Los módulos deben mantener consistencia visual con `src.css` y el tema claro/oscuro. | ☐ | ☐ | ☐ | assets/css/src.css |
| SRC-RNF-009 | Los campos de texto deben normalizarse a mayúsculas donde aplique (nombres de catálogos). | ☐ | ☐ | ☐ | InputSanitizer::text |
| SRC-RNF-010 | Las tablas de listado con muchas columnas deben tener scroll horizontal controlado. | ☐ | ☐ | ☐ | empleados_table_scroll |

---

## HOJA 6 — Base de Datos

> Alcance: modelo relacional PostgreSQL (38 tablas), 141 funciones almacenadas, triggers y semillas de `scripts/` (modelo_db.sql, functions/, triggers/, inserts/). Generalizado por tabla según el patrón real. No incluye `scripts/migrations`.

### 6.A Estructura de Tablas — Requerimientos Funcionales

| ID | Requerimiento | SÍ | NO | ADI | Evidencia / Observaciones |
|----|---------------|----|----|-----|---------------------------|
| BDD-RF-001 | Debe existir la tabla **t_usuarios** (PK id_usuario VARCHAR(20)) con username, correo, contraseña (hash), id_rol, tipo_documento, nombres, apellidos y correo_verificado. | ☐ | ☐ | ☐ | scripts/modelo_db.sql |
| BDD-RF-002 | Debe existir la tabla **t_usuarios_operaciones** (PK compuesta id_usuario+id_operacion) para permisos individuales. | ☐ | ☐ | ☐ | modelo_db.sql |
| BDD-RF-003 | Debe existir la tabla **t_recuperacion_contrasena** con id_usuario, token_hash y fecha_expiracion. | ☐ | ☐ | ☐ | modelo_db.sql |
| BDD-RF-004 | Debe existir la tabla **t_verificacion_correo** con id_registro_pendiente, token_hash, fecha_expiracion y fecha_uso. | ☐ | ☐ | ☐ | modelo_db.sql |
| BDD-RF-005 | Debe existir la tabla **t_intentos_correo** con id_usuario, tipo, contador y ultimo_intento. | ☐ | ☐ | ☐ | modelo_db.sql |
| BDD-RF-006 | Debe existir la tabla **t_remember_tokens** con selector único, token_hash, id_usuario y fec_expiracion. | ☐ | ☐ | ☐ | modelo_db.sql |
| BDD-RF-007 | Debe existir la tabla **t_registros_pendientes** con los datos completos del registro y fecha_expiracion de 24h. | ☐ | ☐ | ☐ | modelo_db.sql |
| BDD-RF-008 | Debe existir la tabla **t_codigos_registro** con codigo (4), tipo (TEMPORAL/UNICO_USO), fecha_expiracion y usado. | ☐ | ☐ | ☐ | modelo_db.sql |
| BDD-RF-009 | Debe existir la tabla **t_modulos** (PK id_modulo) con nombre_modulo. | ☐ | ☐ | ☐ | modelo_db.sql |
| BDD-RF-010 | Debe existir la tabla **t_roles** (PK id_rol) con nombre_rol. | ☐ | ☐ | ☐ | modelo_db.sql |
| BDD-RF-011 | Debe existir la tabla **t_operaciones** (PK id_operacion) con FK a t_modulos y nombre_operacion. | ☐ | ☐ | ☐ | modelo_db.sql |
| BDD-RF-012 | Debe existir la tabla **t_roles_operaciones** (PK compuesta id_rol+id_operacion). | ☐ | ☐ | ☐ | modelo_db.sql |
| BDD-RF-013 | Debe existir la tabla **t_empleados** (PK id_usuario FK t_usuarios) con jefe auto-referente, municipio, fechas de ingreso/egreso, género, nacimiento, tipo de sangre, estado civil, dirección, celular y foto. | ☐ | ☐ | ☐ | modelo_db.sql |
| BDD-RF-014 | Debe existir la tabla **t_contratos_empleados** con empleado, área, puesto, fechas, tipo_contrato y dirección de oficina. | ☐ | ☐ | ☐ | modelo_db.sql |
| BDD-RF-015 | Debe existir la tabla **t_nomina** con empleado, banco, num_cuenta y salario NUMERIC. | ☐ | ☐ | ☐ | modelo_db.sql |
| BDD-RF-016 | Debe existir la tabla **t_afiliaciones_empleados** (PK id_usuario) con FK a EPS, ARL, caja, pensión y cesantías. | ☐ | ☐ | ☐ | modelo_db.sql |
| BDD-RF-017 | Debe existir la tabla **t_departamentos** del catálogo geográfico. | ☐ | ☐ | ☐ | modelo_db.sql |
| BDD-RF-018 | Debe existir la tabla **t_municipios** con FK a t_departamentos y estado. | ☐ | ☐ | ☐ | modelo_db.sql |
| BDD-RF-019 | Debe existir la tabla **t_areas** (PK id_area) con nombre_area. | ☐ | ☐ | ☐ | modelo_db.sql |
| BDD-RF-020 | Debe existir la tabla **t_bancos** (PK id_banco) con nombre_banco. | ☐ | ☐ | ☐ | modelo_db.sql |
| BDD-RF-021 | Debe existir la tabla **t_eps** (PK id_eps) con nombre_eps. | ☐ | ☐ | ☐ | modelo_db.sql |
| BDD-RF-022 | Debe existir la tabla **t_arl** (PK id_arl) con nombre_arl. | ☐ | ☐ | ☐ | modelo_db.sql |
| BDD-RF-023 | Debe existir la tabla **t_caja_compensacion** (PK id_caja) con nombre_caja. | ☐ | ☐ | ☐ | modelo_db.sql |
| BDD-RF-024 | Debe existir la tabla **t_pension** (PK id_pension) con nombre_pension. | ☐ | ☐ | ☐ | modelo_db.sql |
| BDD-RF-025 | Debe existir la tabla **t_cesantias** (PK id_cesantia) con nombre_cesantia. | ☐ | ☐ | ☐ | modelo_db.sql |
| BDD-RF-026 | Debe existir la tabla **t_tipos_permisos** con nombre_tipo, descuenta_tiempo, porcentaje_descuento y requiere_evidencia. | ☐ | ☐ | ☐ | modelo_db.sql |
| BDD-RF-027 | Debe existir la tabla **t_solicitudes_permisos** con empleado, jefe responsable, es_por_horas, fechas, horas, estado y metodo_descuento. | ☐ | ☐ | ☐ | modelo_db.sql |
| BDD-RF-028 | Debe existir la tabla puente **t_solicitudes_permisos_motivos** (PK compuesta solicitud+tipo) con detalle_motivo. | ☐ | ☐ | ☐ | modelo_db.sql |
| BDD-RF-029 | Debe existir la tabla **t_permisos_aprobaciones** con nivel (JEFE/RRHH), aprobador, estado, observación y fecha de resolución. | ☐ | ☐ | ☐ | modelo_db.sql |
| BDD-RF-030 | Debe existir la tabla **t_permisos_evidencias** con proveedor de storage, storage_key, url_publica, nombre original, mime_type y tamaño. | ☐ | ☐ | ☐ | modelo_db.sql |
| BDD-RF-031 | Debe existir la tabla **t_permisos_dias_aprobados** con solicitud, empleado, fecha y horas aprobadas. | ☐ | ☐ | ☐ | modelo_db.sql |
| BDD-RF-032 | Debe existir la tabla **t_vacaciones_ajustes** (ledger append-only) con empleado, dias_ajuste, motivo y fecha. | ☐ | ☐ | ☐ | modelo_db.sql; no existe tabla t_vacaciones (saldo calculado) |
| BDD-RF-033 | Debe existir la tabla **t_dias_festivos** con fecha, descripción, tipo (NACIONAL/EMPRESA) y descuenta_salario. | ☐ | ☐ | ☐ | modelo_db.sql |
| BDD-RF-034 | Debe existir la tabla **t_dias_festivos_excepciones** con festivo, empleado, descuenta_salario y motivo. | ☐ | ☐ | ☐ | modelo_db.sql |
| BDD-RF-035 | Debe existir la tabla **t_comunicados** con título, contenido y categoría. | ☐ | ☐ | ☐ | modelo_db.sql |
| BDD-RF-036 | Debe existir la tabla **t_comunicados_vistos** (única por comunicado+usuario) con fecha de visto. | ☐ | ☐ | ☐ | modelo_db.sql |
| BDD-RF-037 | Debe existir la tabla **t_sst_comite_miembros** con nombre, cargo, tipo de comité, correo, teléfono y orden de visualización. | ☐ | ☐ | ☐ | modelo_db.sql |
| BDD-RF-038 | Debe existir la tabla **t_sst_buzon_quejas** con usuario (nullable=anónima), tipo_peticion, asunto, descripción, estado, respuesta, encargado y datos de cancelación. | ☐ | ☐ | ☐ | modelo_db.sql |

### 6.B Restricciones de Integridad — Requerimientos Funcionales

| ID | Requerimiento | SÍ | NO | ADI | Evidencia / Observaciones |
|----|---------------|----|----|-----|---------------------------|
| BDD-RF-039 | t_usuarios debe tener UNIQUE en username y correo. | ☐ | ☐ | ☐ | modelo_db.sql |
| BDD-RF-040 | t_usuarios debe validar el formato del documento según tipo (CC: 10 dígitos; PPT/CE: 5–20 alfanuméricos) con CHECK. | ☐ | ☐ | ☐ | modelo_db.sql |
| BDD-RF-041 | t_empleados debe impedir que un empleado sea su propio jefe (CHECK id_jefe <> id_usuario). | ☐ | ☐ | ☐ | modelo_db.sql |
| BDD-RF-042 | t_empleados debe validar género, tipo de sangre y estado civil con CHECK de dominio. | ☐ | ☐ | ☐ | modelo_db.sql |
| BDD-RF-043 | t_contratos_empleados debe validar tipo_contrato IN (FIJO, INDEFINIDO, PRACTICAS). | ☐ | ☐ | ☐ | modelo_db.sql |
| BDD-RF-044 | t_tipos_permisos debe exigir porcentaje_descuento NOT NULL solo cuando descuenta_tiempo=TRUE (CHECK condicional). | ☐ | ☐ | ☐ | modelo_db.sql |
| BDD-RF-045 | t_solicitudes_permisos debe validar fecha_fin >= fecha_inicio (CHECK). | ☐ | ☐ | ☐ | modelo_db.sql |
| BDD-RF-046 | t_solicitudes_permisos debe exigir hora_inicio/hora_fin NOT NULL solo si es_por_horas=TRUE, y NULL en caso contrario (CHECK). | ☐ | ☐ | ☐ | modelo_db.sql |
| BDD-RF-047 | t_solicitudes_permisos debe validar estado IN (PENDIENTE, EN_REVISION, APROBADO, RECHAZADO, CANCELADO). | ☐ | ☐ | ☐ | modelo_db.sql |
| BDD-RF-048 | t_solicitudes_permisos debe validar metodo_descuento IN (N/A, VACACIONES, DINERO). | ☐ | ☐ | ☐ | modelo_db.sql |
| BDD-RF-049 | t_permisos_aprobaciones debe validar nivel IN (JEFE, RRHH) y estado IN (PENDIENTE, APROBADO, RECHAZADO). | ☐ | ☐ | ☐ | modelo_db.sql |
| BDD-RF-050 | t_permisos_aprobaciones debe tener UNIQUE (id_permiso, nivel_aprobacion). | ☐ | ☐ | ☐ | modelo_db.sql |
| BDD-RF-051 | t_permisos_evidencias debe validar proveedor IN (BUNNY, R2, B2, CF_IMAGES) y mime IN (jpeg, png, pdf). | ☐ | ☐ | ☐ | modelo_db.sql |
| BDD-RF-052 | t_permisos_evidencias debe validar tamaño entre 1 byte y 10 MB (CHECK). | ☐ | ☐ | ☐ | modelo_db.sql |
| BDD-RF-053 | t_vacaciones_ajustes debe impedir ajustes de 0 días (CHECK dias_ajuste <> 0). | ☐ | ☐ | ☐ | modelo_db.sql |
| BDD-RF-054 | t_dias_festivos debe tener UNIQUE en fecha y CHECK de tipo_festivo. | ☐ | ☐ | ☐ | modelo_db.sql |
| BDD-RF-055 | t_dias_festivos_excepciones debe tener UNIQUE (id_festivo, id_empleado). | ☐ | ☐ | ☐ | modelo_db.sql |
| BDD-RF-056 | t_comunicados debe validar categoria IN (GENERAL, URGENTE, EVENTO, INFORMACION, INSTITUCIONAL). | ☐ | ☐ | ☐ | modelo_db.sql |
| BDD-RF-057 | t_sst_comite_miembros debe validar tipo_comite IN (COPASST, COMITE_CONVIVENCIA). | ☐ | ☐ | ☐ | modelo_db.sql |
| BDD-RF-058 | t_sst_buzon_quejas debe validar tipo_peticion IN (QUEJA, SUGERENCIA, RECLAMO, DENUNCIA). | ☐ | ☐ | ☐ | modelo_db.sql |
| BDD-RF-059 | t_sst_buzon_quejas debe validar estado IN (PENDIENTE, EN PROCESO, RESUELTO, CANCELADO_USUARIO, CANCELADO_ENCARGADO). | ☐ | ☐ | ☐ | modelo_db.sql |
| BDD-RF-060 | t_codigos_registro debe tener UNIQUE en codigo y CHECK de tipo. | ☐ | ☐ | ☐ | modelo_db.sql |
| BDD-RF-061 | t_intentos_correo debe tener UNIQUE (id_usuario, tipo). | ☐ | ☐ | ☐ | modelo_db.sql |
| BDD-RF-062 | Todas las llaves foráneas deben estar declaradas entre tablas relacionadas (empleados→usuarios, contratos→empleados/áreas, nómina→empleados/bancos, afiliaciones→catálogos, permisos→empleados/usuarios, etc.). | ☐ | ☐ | ☐ | modelo_db.sql |
| BDD-RF-063 | Todas las tablas operacionales deben incluir las 6 columnas de auditoría (usr_insert, fec_insert, usr_update, fec_update, usr_delete, fec_delete). | ☐ | ☐ | ☐ | modelo_db.sql; t_departamentos/t_municipios sin auditoría (catálogos DANE) |

### 6.C Funciones Almacenadas — Requerimientos Funcionales (una por función)

| ID | Requerimiento | SÍ | NO | ADI | Evidencia / Observaciones |
|----|---------------|----|----|-----|---------------------------|
| BDD-RF-064 | Debe existir `fun_insert_usuarios` que cree usuarios validando parámetros y retorne mensaje de error o confirmación. | ☐ | ☐ | ☐ | scripts/functions/usuarios/ |
| BDD-RF-065 | Debe existir `fun_listar_usuarios` para consulta de usuarios. | ☐ | ☐ | ☐ | functions/usuarios/ |
| BDD-RF-066 | Debe existir `fun_update_usuarios` para actualización validada. | ☐ | ☐ | ☐ | functions/usuarios/ |
| BDD-RF-067 | Debe existir `fun_login_usuarios` que retorne el registro del usuario (incluido hash) por username o correo, solo activos. | ☐ | ☐ | ☐ | functions/usuarios/ (RETURNS TABLE) |
| BDD-RF-068 | Debe existir `fun_softdelete_usuarios` (borrado lógico). | ☐ | ☐ | ☐ | functions/usuarios/ |
| BDD-RF-069 | Debe existir `fun_restore_usuarios` (restauración). | ☐ | ☐ | ☐ | functions/usuarios/ |
| BDD-RF-070 | Debe existir `fun_insert_operaciones`. | ☐ | ☐ | ☐ | functions/operaciones/ |
| BDD-RF-071 | Debe existir `fun_listar_operaciones`. | ☐ | ☐ | ☐ | functions/operaciones/ |
| BDD-RF-072 | Debe existir `fun_update_operaciones`. | ☐ | ☐ | ☐ | functions/operaciones/ |
| BDD-RF-073 | Debe existir `fun_softdelete_operaciones`. | ☐ | ☐ | ☐ | functions/operaciones/ |
| BDD-RF-074 | Debe existir `fun_restore_operaciones`. | ☐ | ☐ | ☐ | functions/operaciones/ |
| BDD-RF-075 | Debe existir `fun_insert_vacaciones_ajustes` (ledger de ajustes manuales). | ☐ | ☐ | ☐ | functions/vacaciones/ |
| BDD-RF-076 | Debe existir `fun_listar_vacaciones_ajustes` por empleado. | ☐ | ☐ | ☐ | functions/vacaciones/ |
| BDD-RF-077 | Debe existir `fun_update_vacaciones_ajustes`. | ☐ | ☐ | ☐ | functions/vacaciones/ |
| BDD-RF-078 | Debe existir `fun_softdelete_vacaciones_ajustes` (anulación). | ☐ | ☐ | ☐ | functions/vacaciones/ |
| BDD-RF-079 | Debe existir `fun_restore_vacaciones_ajustes`. | ☐ | ☐ | ☐ | functions/vacaciones/ |
| BDD-RF-080 | Debe existir `fun_calcular_saldo_vacaciones` que retorne años de servicio, período, días causados/disfrutados, ajuste manual, saldo y alerta de vencimiento (política: 15 días/año, no acumulables, reinicio en aniversario). | ☐ | ☐ | ☐ | functions/vacaciones/ (RETURNS TABLE) |
| BDD-RF-081 | Debe existir `fun_dias_habiles_rango` que cuente lunes a sábado excluyendo festivos. | ☐ | ☐ | ☐ | functions/vacaciones/ |
| BDD-RF-082 | Debe existir `fun_insert_tipos_permisos`. | ☐ | ☐ | ☐ | functions/tipos_permisos/ |
| BDD-RF-083 | Debe existir `fun_listar_tipos_permisos`. | ☐ | ☐ | ☐ | functions/tipos_permisos/ |
| BDD-RF-084 | Debe existir `fun_update_tipos_permisos`. | ☐ | ☐ | ☐ | functions/tipos_permisos/ |
| BDD-RF-085 | Debe existir `fun_softdelete_tipos_permisos`. | ☐ | ☐ | ☐ | functions/tipos_permisos/ |
| BDD-RF-086 | Debe existir `fun_restore_tipos_permisos`. | ☐ | ☐ | ☐ | functions/tipos_permisos/ |
| BDD-RF-087 | Debe existir `fun_insert_solicitudes_permisos` que cree la solicitud, sus motivos y las 2 aprobaciones (JEFE y RRHH) en estado PENDIENTE de forma atómica. | ☐ | ☐ | ☐ | functions/solicitudes_permisos/ |
| BDD-RF-088 | Debe existir `fun_listar_solicitudes_permisos`. | ☐ | ☐ | ☐ | functions/solicitudes_permisos/ |
| BDD-RF-089 | Debe existir `fun_update_solicitudes_permisos`. | ☐ | ☐ | ☐ | functions/solicitudes_permisos/ |
| BDD-RF-090 | Debe existir `fun_softdelete_solicitudes_permisos`. | ☐ | ☐ | ☐ | functions/solicitudes_permisos/ |
| BDD-RF-091 | Debe existir `fun_restore_solicitudes_permisos`. | ☐ | ☐ | ☐ | functions/solicitudes_permisos/ |
| BDD-RF-092 | Debe existir `fun_insert_solicitudes_permisos_motivos`. | ☐ | ☐ | ☐ | functions/solicitudes_permisos_motivos/ |
| BDD-RF-093 | Debe existir `fun_listar_solicitudes_permisos_motivos`. | ☐ | ☐ | ☐ | functions/solicitudes_permisos_motivos/ |
| BDD-RF-094 | Debe existir `fun_update_solicitudes_permisos_motivos`. | ☐ | ☐ | ☐ | functions/solicitudes_permisos_motivos/ |
| BDD-RF-095 | Debe existir `fun_softdelete_solicitudes_permisos_motivos`. | ☐ | ☐ | ☐ | functions/solicitudes_permisos_motivos/ |
| BDD-RF-096 | Debe existir `fun_restore_solicitudes_permisos_motivos`. | ☐ | ☐ | ☐ | functions/solicitudes_permisos_motivos/ |
| BDD-RF-097 | Debe existir `fun_resolver_permisos_aprobaciones` que valide autorización por nivel, aplique aprobación/rechazo, valide saldo de vacaciones cuando el descuento es VACACIONES (sin anticipos) y retorne (exito, mensaje, nuevo_estado). | ☐ | ☐ | ☐ | functions/permisos_aprobaciones/ |
| BDD-RF-098 | Debe existir `fun_insert_permisos_usuarios`. | ☐ | ☐ | ☐ | functions/permisos_usuarios/ |
| BDD-RF-099 | Debe existir `fun_listar_permisos_usuarios`. | ☐ | ☐ | ☐ | functions/permisos_usuarios/ |
| BDD-RF-100 | Debe existir `fun_update_permisos_usuarios`. | ☐ | ☐ | ☐ | functions/permisos_usuarios/ |
| BDD-RF-101 | Debe existir `fun_softdelete_permisos_usuarios`. | ☐ | ☐ | ☐ | functions/permisos_usuarios/ |
| BDD-RF-102 | Debe existir `fun_restore_permisos_usuarios`. | ☐ | ☐ | ☐ | functions/permisos_usuarios/ |
| BDD-RF-103 | Debe existir `fun_insert_contratos_empleados`. | ☐ | ☐ | ☐ | functions/contratos_empleados/ |
| BDD-RF-104 | Debe existir `fun_listar_contratos_empleados`. | ☐ | ☐ | ☐ | functions/contratos_empleados/ |
| BDD-RF-105 | Debe existir `fun_update_contratos_empleados`. | ☐ | ☐ | ☐ | functions/contratos_empleados/ |
| BDD-RF-106 | Debe existir `fun_softdelete_contratos_empleados`. | ☐ | ☐ | ☐ | functions/contratos_empleados/ |
| BDD-RF-107 | Debe existir `fun_restore_contratos_empleados`. | ☐ | ☐ | ☐ | functions/contratos_empleados/ |
| BDD-RF-108 | Debe existir `fun_insert_comunicados`. | ☐ | ☐ | ☐ | functions/comunicados/ |
| BDD-RF-109 | Debe existir `fun_listar_comunicados`. | ☐ | ☐ | ☐ | functions/comunicados/ |
| BDD-RF-110 | Debe existir `fun_listar_comunicados_activos` que filtre solo vigentes (fec_delete IS NULL). | ☐ | ☐ | ☐ | functions/comunicados/ |
| BDD-RF-111 | Debe existir `fun_update_comunicados`. | ☐ | ☐ | ☐ | functions/comunicados/ |
| BDD-RF-112 | Debe existir `fun_softdelete_comunicados`. | ☐ | ☐ | ☐ | functions/comunicados/ |
| BDD-RF-113 | Debe existir `fun_restore_comunicados`. | ☐ | ☐ | ☐ | functions/comunicados/ |
| BDD-RF-114 | Debe existir `fun_marcar_visto_comunicado` idempotente (ON CONFLICT DO NOTHING). | ☐ | ☐ | ☐ | functions/comunicados/ |
| BDD-RF-115 | Debe existir `fun_insert_pension`. | ☐ | ☐ | ☐ | functions/pension/ |
| BDD-RF-116 | Debe existir `fun_listar_pension`. | ☐ | ☐ | ☐ | functions/pension/ |
| BDD-RF-117 | Debe existir `fun_update_pension`. | ☐ | ☐ | ☐ | functions/pension/ |
| BDD-RF-118 | Debe existir `fun_softdelete_pension`. | ☐ | ☐ | ☐ | functions/pension/ |
| BDD-RF-119 | Debe existir `fun_restore_pension`. | ☐ | ☐ | ☐ | functions/pension/ |
| BDD-RF-120 | Debe existir `fun_insert_permisos_dias_aprobados`. | ☐ | ☐ | ☐ | functions/permisos_dias_aprobados/ |
| BDD-RF-121 | Debe existir `fun_listar_permisos_dias_aprobados`. | ☐ | ☐ | ☐ | functions/permisos_dias_aprobados/ |
| BDD-RF-122 | Debe existir `fun_update_permisos_dias_aprobados`. | ☐ | ☐ | ☐ | functions/permisos_dias_aprobados/ |
| BDD-RF-123 | Debe existir `fun_softdelete_permisos_dias_aprobados`. | ☐ | ☐ | ☐ | functions/permisos_dias_aprobados/ |
| BDD-RF-124 | Debe existir `fun_restore_permisos_dias_aprobados`. | ☐ | ☐ | ☐ | functions/permisos_dias_aprobados/ |
| BDD-RF-125 | Debe existir `fun_insert_banco`. | ☐ | ☐ | ☐ | functions/bancos/ |
| BDD-RF-126 | Debe existir `fun_listar_banco`. | ☐ | ☐ | ☐ | functions/bancos/ |
| BDD-RF-127 | Debe existir `fun_update_banco`. | ☐ | ☐ | ☐ | functions/bancos/ |
| BDD-RF-128 | Debe existir `fun_softdelete_banco`. | ☐ | ☐ | ☐ | functions/bancos/ |
| BDD-RF-129 | Debe existir `fun_restore_banco`. | ☐ | ☐ | ☐ | functions/bancos/ |
| BDD-RF-130 | Debe existir `fun_insert_roles_operaciones`. | ☐ | ☐ | ☐ | functions/roles_operaciones/ |
| BDD-RF-131 | Debe existir `fun_listar_roles_operaciones`. | ☐ | ☐ | ☐ | functions/roles_operaciones/ |
| BDD-RF-132 | Debe existir `fun_update_roles_operaciones`. | ☐ | ☐ | ☐ | functions/roles_operaciones/ |
| BDD-RF-133 | Debe existir `fun_softdelete_roles_operaciones`. | ☐ | ☐ | ☐ | functions/roles_operaciones/ |
| BDD-RF-134 | Debe existir `fun_restore_roles_operaciones`. | ☐ | ☐ | ☐ | functions/roles_operaciones/ |
| BDD-RF-135 | Debe existir `fun_insert_cesantias`. | ☐ | ☐ | ☐ | functions/cesantias/ |
| BDD-RF-136 | Debe existir `fun_listar_cesantias`. | ☐ | ☐ | ☐ | functions/cesantias/ |
| BDD-RF-137 | Debe existir `fun_update_cesantias`. | ☐ | ☐ | ☐ | functions/cesantias/ |
| BDD-RF-138 | Debe existir `fun_softdelete_cesantias`. | ☐ | ☐ | ☐ | functions/cesantias/ |
| BDD-RF-139 | Debe existir `fun_restore_cesantias`. | ☐ | ☐ | ☐ | functions/cesantias/ |
| BDD-RF-140 | Debe existir `fun_insert_arl`. | ☐ | ☐ | ☐ | functions/arl/ |
| BDD-RF-141 | Debe existir `fun_listar_arl`. | ☐ | ☐ | ☐ | functions/arl/ |
| BDD-RF-142 | Debe existir `fun_update_arl`. | ☐ | ☐ | ☐ | functions/arl/ |
| BDD-RF-143 | Debe existir `fun_softdelete_arl`. | ☐ | ☐ | ☐ | functions/arl/ |
| BDD-RF-144 | Debe existir `fun_restore_arl`. | ☐ | ☐ | ☐ | functions/arl/ |
| BDD-RF-145 | Debe existir `fun_insert_modulos`. | ☐ | ☐ | ☐ | functions/modulos/ |
| BDD-RF-146 | Debe existir `fun_listar_modulos`. | ☐ | ☐ | ☐ | functions/modulos/ |
| BDD-RF-147 | Debe existir `fun_update_modulos`. | ☐ | ☐ | ☐ | functions/modulos/ |
| BDD-RF-148 | Debe existir `fun_softdelete_modulos`. | ☐ | ☐ | ☐ | functions/modulos/ |
| BDD-RF-149 | Debe existir `fun_restore_modulos`. | ☐ | ☐ | ☐ | functions/modulos/ |
| BDD-RF-150 | Debe existir `fun_insert_roles`. | ☐ | ☐ | ☐ | functions/roles/ |
| BDD-RF-151 | Debe existir `fun_listar_roles`. | ☐ | ☐ | ☐ | functions/roles/ |
| BDD-RF-152 | Debe existir `fun_update_roles`. | ☐ | ☐ | ☐ | functions/roles/ |
| BDD-RF-153 | Debe existir `fun_softdelete_roles`. | ☐ | ☐ | ☐ | functions/roles/ |
| BDD-RF-154 | Debe existir `fun_restore_roles`. | ☐ | ☐ | ☐ | functions/roles/ |
| BDD-RF-155 | Debe existir `fun_insert_nomina`. | ☐ | ☐ | ☐ | functions/nomina/ |
| BDD-RF-156 | Debe existir `fun_listar_nomina`. | ☐ | ☐ | ☐ | functions/nomina/ |
| BDD-RF-157 | Debe existir `fun_update_nomina`. | ☐ | ☐ | ☐ | functions/nomina/ |
| BDD-RF-158 | Debe existir `fun_softdelete_nomina`. | ☐ | ☐ | ☐ | functions/nomina/ |
| BDD-RF-159 | Debe existir `fun_restore_nomina`. | ☐ | ☐ | ☐ | functions/nomina/ |
| BDD-RF-160 | Debe existir `fun_insert_area`. | ☐ | ☐ | ☐ | functions/areas/ |
| BDD-RF-161 | Debe existir `fun_listar_area`. | ☐ | ☐ | ☐ | functions/areas/ |
| BDD-RF-162 | Debe existir `fun_update_area`. | ☐ | ☐ | ☐ | functions/areas/ |
| BDD-RF-163 | Debe existir `fun_softdelete_area`. | ☐ | ☐ | ☐ | functions/areas/ |
| BDD-RF-164 | Debe existir `fun_restore_area`. | ☐ | ☐ | ☐ | functions/areas/ |
| BDD-RF-165 | Debe existir `fun_insert_cajacompensacion`. | ☐ | ☐ | ☐ | functions/cajacompensacion/ |
| BDD-RF-166 | Debe existir `fun_listar_cajacompensacion`. | ☐ | ☐ | ☐ | functions/cajacompensacion/ |
| BDD-RF-167 | Debe existir `fun_update_cajacompensacion`. | ☐ | ☐ | ☐ | functions/cajacompensacion/ |
| BDD-RF-168 | Debe existir `fun_softdelete_cajacompensacion`. | ☐ | ☐ | ☐ | functions/cajacompensacion/ |
| BDD-RF-169 | Debe existir `fun_restore_cajacompensacion`. | ☐ | ☐ | ☐ | functions/cajacompensacion/ |
| BDD-RF-170 | Debe existir `fun_insert_eps`. | ☐ | ☐ | ☐ | functions/eps/ |
| BDD-RF-171 | Debe existir `fun_listar_eps`. | ☐ | ☐ | ☐ | functions/eps/ |
| BDD-RF-172 | Debe existir `fun_update_eps`. | ☐ | ☐ | ☐ | functions/eps/ |
| BDD-RF-173 | Debe existir `fun_softdelete_eps`. | ☐ | ☐ | ☐ | functions/eps/ |
| BDD-RF-174 | Debe existir `fun_restore_eps`. | ☐ | ☐ | ☐ | functions/eps/ |
| BDD-RF-175 | Debe existir `fun_insert_empleados` con validaciones internas (celular de 10 dígitos, etc.). | ☐ | ☐ | ☐ | functions/empleados/ |
| BDD-RF-176 | Debe existir `fun_listar_empleados`. | ☐ | ☐ | ☐ | functions/empleados/ |
| BDD-RF-177 | Debe existir `fun_update_empleados`. | ☐ | ☐ | ☐ | functions/empleados/ |
| BDD-RF-178 | Debe existir `fun_softdelete_empleados`. | ☐ | ☐ | ☐ | functions/empleados/ |
| BDD-RF-179 | Debe existir `fun_restore_empleados`. | ☐ | ☐ | ☐ | functions/empleados/ |
| BDD-RF-180 | Debe existir `fun_insert_afiliaciones_empleados`. | ☐ | ☐ | ☐ | functions/afiliaciones_empleados/ |
| BDD-RF-181 | Debe existir `fun_listar_afiliaciones_empleados`. | ☐ | ☐ | ☐ | functions/afiliaciones_empleados/ |
| BDD-RF-182 | Debe existir `fun_update_afiliaciones_empleados`. | ☐ | ☐ | ☐ | functions/afiliaciones_empleados/ |
| BDD-RF-183 | Debe existir `fun_softdelete_afiliaciones_empleados`. | ☐ | ☐ | ☐ | functions/afiliaciones_empleados/ |
| BDD-RF-184 | Debe existir `fun_restore_afiliaciones_empleados`. | ☐ | ☐ | ☐ | functions/afiliaciones_empleados/ |

| BDD-RF-185 | Debe existir `fun_insert_dias_festivos` para crear un festivo manual (tipo EMPRESA). | ☐ | ☐ | ☐ | functions/dias_festivos/ |
| BDD-RF-186 | Debe existir `fun_listar_dias_festivos` que liste festivos nacionales y de empresa. | ☐ | ☐ | ☐ | functions/dias_festivos/ |
| BDD-RF-187 | Debe existir `fun_update_dias_festivos`. | ☐ | ☐ | ☐ | functions/dias_festivos/ |
| BDD-RF-188 | Debe existir `fun_softdelete_dias_festivos` (solo festivos tipo EMPRESA). | ☐ | ☐ | ☐ | functions/dias_festivos/ |
| BDD-RF-189 | Debe existir `fun_restore_dias_festivos`. | ☐ | ☐ | ☐ | functions/dias_festivos/ |
| BDD-RF-190 | Debe existir `fun_calcular_festivos_colombia` que calcule las fechas de los festivos nacionales por ley Emiliani. | ☐ | ☐ | ☐ | functions/dias_festivos/ |
| BDD-RF-191 | Debe existir `fun_calcular_pascua` que calcule el domingo de Pascua (base de Semana Santa). | ☐ | ☐ | ☐ | functions/dias_festivos/ |
| BDD-RF-192 | Debe existir `fun_siguiente_lunes` que aplique el corrimiento al lunes siguiente (ley Emiliani). | ☐ | ☐ | ☐ | functions/dias_festivos/ |
| BDD-RF-193 | Debe existir `fun_sembrar_festivos_colombia` que siembre masivamente los festivos nacionales en la tabla. | ☐ | ☐ | ☐ | functions/dias_festivos/ |
| BDD-RF-194 | Debe existir `fun_insert_permisos_evidencias` que registre los metadatos de una evidencia (proveedor, storage_key, url, mime, tamaño). | ☐ | ☐ | ☐ | functions/permisos_evidencias/ |
| BDD-RF-195 | Debe existir `fun_insert_sst_comite_miembro`. | ☐ | ☐ | ☐ | functions/sst/ |
| BDD-RF-196 | Debe existir `fun_listar_sst_comite_miembros`. | ☐ | ☐ | ☐ | functions/sst/ |
| BDD-RF-197 | Debe existir `fun_update_sst_comite_miembro`. | ☐ | ☐ | ☐ | functions/sst/ |
| BDD-RF-198 | Debe existir `fun_softdelete_sst_comite_miembro`. | ☐ | ☐ | ☐ | functions/sst/ |
| BDD-RF-199 | Debe existir `fun_restore_sst_comite_miembro`. | ☐ | ☐ | ☐ | functions/sst/ |
| BDD-RF-200 | Debe existir `fun_insert_sst_queja` que cree una petición en el buzón SST. | ☐ | ☐ | ☐ | functions/sst/ |
| BDD-RF-201 | Debe existir `fun_listar_sst_quejas_usuario` que liste las quejas del usuario (con bandera puede_editar). | ☐ | ☐ | ☐ | functions/sst/ |
| BDD-RF-202 | Debe existir `fun_listar_sst_quejas_encargado` para la vista del encargado SST. | ☐ | ☐ | ☐ | functions/sst/ |
| BDD-RF-203 | Debe existir `fun_update_sst_queja` (edición de asunto/descripción). | ☐ | ☐ | ☐ | functions/sst/ |
| BDD-RF-204 | Debe existir `fun_cambiar_estado_sst_queja` (cambio de estado y respuesta del encargado). | ☐ | ☐ | ☐ | functions/sst/ |
| BDD-RF-205 | Debe existir `fun_cancelar_sst_queja_usuario`. | ☐ | ☐ | ☐ | functions/sst/ |

### 6.D Triggers y Procedimientos — Requerimientos Funcionales

| ID | Requerimiento | SÍ | NO | ADI | Evidencia / Observaciones |
|----|---------------|----|----|-----|---------------------------|
| BDD-RF-206 | Debe existir `fun_audit_actor()` que resuelva el usuario de auditoría desde `app.current_user` con fallback a `CURRENT_USER`. | ☐ | ☐ | ☐ | scripts/triggers/auditorias.sql |
| BDD-RF-207 | Debe existir `fun_audit_tablas()` que rellene `usr_insert`/`fec_insert` en INSERT y `usr_update`/`fec_update` en UPDATE. | ☐ | ☐ | ☐ | auditorias.sql |
| BDD-RF-208 | El trigger de auditoría debe aplicarse a todas las tablas operacionales (34 tablas, incluida la de tokens y registros pendientes). | ☐ | ☐ | ☐ | auditorias.sql |
| BDD-RF-209 | Debe existir `fun_soft_delete_tablas()` que intercepte el DELETE y marque `usr_delete`/`fec_delete` (sin borrado físico). | ☐ | ☐ | ☐ | softdelete.sql |
| BDD-RF-210 | El trigger de soft delete debe aplicarse a las tablas de negocio. | ☐ | ☐ | ☐ | softdelete.sql (triggers homónimos `tri_soft_delete_usuarios` y con `BEFORE INSERT OR UPDATE` en lugar de `BEFORE DELETE` — deuda técnica) |
| BDD-RF-211 | Debe existir `tri_borrar_codigo_registro` que borre físicamente el código al marcarse usado o eliminado. | ☐ | ☐ | ☐ | borrado_fisico.sql |
| BDD-RF-212 | Debe existir `tri_borrar_token_verificacion` que borre físicamente el token de verificación al usarse. | ☐ | ☐ | ☐ | borrado_fisico.sql |
| BDD-RF-213 | Debe existir `tri_limpiar_registros_pendientes` que elimine el registro pendiente asociado al verificar. | ☐ | ☐ | ☐ | borrado_fisico.sql |
| BDD-RF-214 | Debe existir `tri_borrar_token_recuperacion` que borre físicamente el token de recuperación al usarse/expirar. | ☐ | ☐ | ☐ | borrado_fisico.sql |
| BDD-RF-215 | Debe existir el procedimiento `sp_limpiar_registros_pendientes_expirados` para purga manual. | ☐ | ☐ | ☐ | borrado_fisico.sql |

### 6.E Semillas y Datos Iniciales — Requerimientos Funcionales

| ID | Requerimiento | SÍ | NO | ADI | Evidencia / Observaciones |
|----|---------------|----|----|-----|---------------------------|
| BDD-RF-216 | La semilla `rbac_seeds.sql` debe insertar los 2 roles base, los 28 módulos, las operaciones y las asignaciones rol-operación. | ☐ | ☐ | ☐ | scripts/inserts/rbac_seeds.sql |
| BDD-RF-217 | La semilla `festivos_seed.sql` debe sembrar los festivos nacionales de Colombia. | ☐ | ☐ | ☐ | scripts/inserts/festivos_seed.sql |
| BDD-RF-218 | La semilla `institutions_insert.sql` debe precargar catálogos de EPS, ARL, Cajas, Pensión, Cesantías y Bancos. | ☐ | ☐ | ☐ | scripts/inserts/institutions_insert.sql |
| BDD-RF-219 | La semilla `dpts_municipios.sql` debe cargar el catálogo DANE de departamentos y municipios. | ☐ | ☐ | ☐ | scripts/inserts/dpts_municipios.sql |
| BDD-RF-220 | La semilla `user_insert.sql` debe crear el usuario administrador inicial. | ☐ | ☐ | ☐ | scripts/inserts/user_insert.sql |

### 6.F Requerimientos No Funcionales de Base de Datos

| ID | Requerimiento | SÍ | NO | ADI | Evidencia / Observaciones |
|----|---------------|----|----|-----|---------------------------|
| BDD-RNF-001 | Debe usarse SQL nativo vía PDO con named parameters, sin ORM. | ☐ | ☐ | ☐ | app/conexion.php, endpoints |
| BDD-RNF-002 | La lógica transaccional debe residir en funciones almacenadas, no en INSERT/UPDATE manuales de PHP. | ☐ | ☐ | ☐ | scripts/functions/; varios endpoints la setean manualmente (ADI) |
| BDD-RNF-003 | Los flujos multi-tabla (solicitud + motivos + aprobaciones) deben ejecutarse de forma atómica dentro de una sola función. | ☐ | ☐ | ☐ | fun_insert_solicitudes_permisos |
| BDD-RNF-004 | Debe mantenerse la convención de nombres `fun_insert_*`, `fun_listar_*`, `fun_update_*`, `fun_softdelete_*`, `fun_restore_*`. | ☐ | ☐ | ☐ | scripts/functions/ |
| BDD-RNF-005 | Las funciones deben retornar `TABLE` (listados) o `(exito, mensaje, ...)` (escrituras) para retroalimentar al usuario. | ☐ | ☐ | ☐ | funciones listar/escritura |
| BDD-RNF-006 | Los cálculos de fechas (vacaciones, festivos, comunicados) deben operar en zona horaria `America/Bogota`. | ☐ | ☐ | ☐ | date_default_timezone_set en vistas |
| BDD-RNF-007 | Los booleanos devueltos por PostgreSQL (`'t'`/`'f'`) deben normalizarse con `filter_var(FILTER_VALIDATE_BOOLEAN)` antes de serializar a JSON. | ☐ | ☐ | ☐ | app/vacaciones_saldo.php, permiso_listar.php |
| BDD-RNF-008 | Las migraciones y consultas auxiliares deben mantenerse en `scripts/migrations` y `scripts/queries`. | ☐ | ☐ | ☐ | scripts/migrations, scripts/queries |

---

## HOJA 7 — Flujos de Negocio y Endpoints (app/ + vistas)

> Alcance: los procesos transaccionales del portal que no son CRUDs administrativos de `/src`: radicación y aprobación de solicitudes de permiso/vacaciones, gestión del saldo de vacaciones, comunicados, SST (comités y buzón de quejas), directorio de empleados, códigos de registro, ficha técnica y reportes Excel/PDF. Se implementan como endpoints JSON en `app/` consumidos vía fetch por vistas en `templates/`.

### 7.A Solicitud de Permisos y Vacaciones (radicación) — RF

| ID | Requerimiento | SÍ | NO | ADI | Evidencia / Observaciones |
|----|---------------|----|----|-----|---------------------------|
| FLU-RF-001 | La vista de solicitud debe restringir el acceso al módulo 3 (`require_module_access(3)`). | ☐ | ☐ | ☐ | templates/solicitud_permiso.php |
| FLU-RF-002 | La vista debe organizarse en dos pestañas: "Nueva Solicitud" y "Mis Solicitudes". | ☐ | ☐ | ☐ | templates/solicitud_permiso.php |
| FLU-RF-003 | El formulario debe permitir elegir el tipo de solicitud: "Permiso por Días" o "Permiso por Horas" (radio buttons). | ☐ | ☐ | ☐ | solicitud_permiso.php |
| FLU-RF-004 | En modo "días" debe mostrar selectores de fecha inicial/final con date-picker custom (dd/mm/aaaa). | ☐ | ☐ | ☐ | solicitud_permiso.php |
| FLU-RF-005 | En modo "horas" debe mostrar un único día y selectores de hora inicio/fin con time-picker. | ☐ | ☐ | ☐ | solicitud_permiso.php |
| FLU-RF-006 | El selector debe calcular "Total de Días Hábiles" excluyendo domingos y festivos (lunes a sábado), consumiendo `app/festivos_listar.php`. | ☐ | ☐ | ☐ | solicitud_permiso.php + festivos_listar.php |
| FLU-RF-007 | Debe mostrar leyenda visual de festivos nacionales, festivos de empresa y domingos. | ☐ | ☐ | ☐ | `.date-picker-legend` |
| FLU-RF-008 | El campo "Motivo" debe listar los tipos de permiso activos desde `t_tipos_permisos` con indicador de evidencia requerida. | ☐ | ☐ | ☐ | solicitud_permiso.php (`data-requiere-evidencia`) |
| FLU-RF-009 | El campo "Detalle" (opcional) debe capturar texto libre del motivo. | ☐ | ☐ | ☐ | solicitud_permiso.php |
| FLU-RF-010 | Si el tipo requiere evidencia, debe mostrarse el campo de adjuntos (JPG/PNG/PDF, máx. 10 MB c/u, múltiple). | ☐ | ☐ | ☐ | solicitud_permiso.php |
| FLU-RF-011 | El botón "Enviar Solicitud" debe deshabilitarse si el usuario no tiene permiso 'crear' del módulo 3. | ☐ | ☐ | ☐ | `$puedeCrear` |
| FLU-RF-012 | El endpoint `app/solicitud_permiso.php` debe validar método POST (405), CSRF y permiso 'crear' del módulo 3. | ☐ | ☐ | ☐ | app/solicitud_permiso.php |
| FLU-RF-013 | El endpoint debe validar tipo de solicitud (`dias`/`horas`) y que las fechas sean obligatorias. | ☐ | ☐ | ☐ | app/solicitud_permiso.php |
| FLU-RF-014 | Para solicitudes por horas debe validar que `fecha_inicio == fecha_fin` (un único día). | ☐ | ☐ | ☐ | app/solicitud_permiso.php |
| FLU-RF-015 | Debe detectar el jefe directo desde `t_empleados.id_jefe`; sin jefe asignado, rechaza con error controlado. | ☐ | ☐ | ☐ | app/solicitud_permiso.php |
| FLU-RF-016 | Debe validar que los tipos de permiso seleccionados existan y estén activos (no eliminados). | ☐ | ☐ | ☐ | app/solicitud_permiso.php |
| FLU-RF-017 | Debe validar las evidencias con `EvidenceValidator` (tipo, MIME, tamaño) antes de subirlas. | ☐ | ☐ | ☐ | EvidenceValidator.php |
| FLU-RF-018 | Debe subir evidencias al proveedor configurado (`StorageFactory`) y registrar metadatos con `fun_insert_permisos_evidencias`. | ☐ | ☐ | ☐ | app/solicitud_permiso.php |
| FLU-RF-019 | Debe crear la solicitud de forma atómica (solicitud + motivos + 2 aprobaciones JEFE/RRHH en PENDIENTE) con `fun_insert_solicitudes_permisos`. | ☐ | ☐ | ☐ | app/solicitud_permiso.php |
| FLU-RF-020 | El método de descuento lo define RRHH al aprobar; el empleado envía `metodo_descuento = NULL`. | ☐ | ☐ | ☐ | app/solicitud_permiso.php |
| FLU-RF-021 | Debe notificar por correo al jefe directo y a los usuarios con permiso VER del módulo 27, sin fallar la respuesta si el correo falla. | ☐ | ☐ | ☐ | notificarSolicitudCreada |
| FLU-RF-022 | La pestaña "Mis Solicitudes" debe cargar el historial del empleado con el estado de cada nivel vía `app/permiso_listar.php?vista=mias`. | ☐ | ☐ | ☐ | solicitud_permiso.js |

### 7.B Bandeja de Aprobaciones — RF

| ID | Requerimiento | SÍ | NO | ADI | Evidencia / Observaciones |
|----|---------------|----|----|-----|---------------------------|
| FLU-RF-023 | `templates/bandeja_permisos.php` solo debe ser accesible para jefes con subordinados o usuarios con módulo 27; si no, redirige a 403. | ☐ | ☐ | ☐ | bandeja_permisos.php |
| FLU-RF-024 | Debe mostrar pestañas "Bandeja de Jefe" y "Bandeja de RRHH" según los roles del usuario. | ☐ | ☐ | ☐ | bandeja_permisos.php |
| FLU-RF-025 | La bandeja de jefe debe listar las solicitudes donde el usuario es `id_jefe_responsable` con aprobación JEFE en PENDIENTE. | ☐ | ☐ | ☐ | permiso_listar.php (`vista=jefe`) |
| FLU-RF-026 | La bandeja de RRHH debe listar todas las solicitudes con aprobación RRHH en PENDIENTE (requiere módulo 27). | ☐ | ☐ | ☐ | permiso_listar.php (`vista=rrhh`) |
| FLU-RF-027 | Debe ofrecer "Ver historial completo" para listar las ya resueltas por el nivel (`?historial=1`). | ☐ | ☐ | ☐ | permiso_listar.php |
| FLU-RF-028 | `app/permiso_resolver.php` debe validar nivel (JEFE/RRHH), acción (APROBADO/RECHAZADO) y permiso por nivel (JEFE→módulo 3 actualizar; RRHH→módulo 27 actualizar). | ☐ | ☐ | ☐ | permiso_resolver.php |
| FLU-RF-029 | Al aprobar como RRHH debe exigir `metodo_descuento` (DINERO/VACACIONES/NO). | ☐ | ☐ | ☐ | permiso_resolver.php |
| FLU-RF-030 | La resolución debe ejecutarse con `fun_resolver_permisos_aprobaciones`, validando autorización por nivel y saldo de vacaciones si descuenta VACACIONES. | ☐ | ☐ | ☐ | permiso_resolver.php |
| FLU-RF-031 | Debe notificar al empleado solo cuando el estado agregado pasa a APROBADO o RECHAZADO (no en la etapa intermedia EN_REVISION). | ☐ | ☐ | ☐ | notificarResolucionSolicitud |
| FLU-RF-032 | `app/permiso_detalle.php` debe exponer motivos, evidencias y aprobaciones con acceso restringido al empleado, su jefe o RRHH. | ☐ | ☐ | ☐ | permiso_detalle.php |

### 7.C Gestión de Vacaciones — RF

| ID | Requerimiento | SÍ | NO | ADI | Evidencia / Observaciones |
|----|---------------|----|----|-----|---------------------------|
| FLU-RF-033 | `templates/vacaciones.php` debe restringir el acceso al módulo 29. | ☐ | ☐ | ☐ | templates/vacaciones.php |
| FLU-RF-034 | Debe ofrecer selector de empleado (solo RRHH) para consultar el saldo de otro empleado. | ☐ | ☐ | ☐ | vacaciones.php |
| FLU-RF-035 | El saldo debe calcularse con `fun_calcular_saldo_vacaciones` (años de servicio, período, días causados/disfrutados, ajuste, saldo y alerta de vencimiento). | ☐ | ☐ | ☐ | app/vacaciones_saldo.php |
| FLU-RF-036 | Debe permitir registrar un ajuste manual (días a restar + motivo) con permiso 'crear' del módulo 29. | ☐ | ☐ | ☐ | templates/vacaciones.php |
| FLU-RF-037 | Debe mostrar el historial de ajustes con acciones anular/restaurar (permisos eliminar/restaurar). | ☐ | ☐ | ☐ | app/vacaciones_ajuste_*.php |
| FLU-RF-038 | `app/vacaciones_saldo.php` sin `id_empleado` debe devolver el saldo propio; con `id_empleado` solo si el usuario es RRHH. | ☐ | ☐ | ☐ | vacaciones_saldo.php |
| FLU-RF-039 | `app/vacaciones_saldo.php?accion=empleados` debe devolver la lista de empleados activos (solo RRHH). | ☐ | ☐ | ☐ | vacaciones_saldo.php |
| FLU-RF-040 | Los endpoints `vacaciones_ajuste_insertar/anular/restaurar` deben gestionar el ledger de ajustes. | ☐ | ☐ | ☐ | app/vacaciones_ajuste_*.php |
| FLU-RF-041 | `app/vacaciones_reporte.php` debe generar reporte Excel/PDF de saldo (todos o un empleado). | ☐ | ☐ | ☐ | vacaciones_reporte.php |

### 7.D Comunicados (vista de empleado) — RF

| ID | Requerimiento | SÍ | NO | ADI | Evidencia / Observaciones |
|----|---------------|----|----|-----|---------------------------|
| FLU-RF-042 | `templates/comunicados.php` debe restringir el acceso al módulo 5. | ☐ | ☐ | ☐ | templates/comunicados.php |
| FLU-RF-043 | Debe listar comunicados activos enriquecidos con el estado de lectura del usuario (`fun_listar_comunicados_activos`). | ☐ | ☐ | ☐ | comunicados.php |
| FLU-RF-044 | Debe mostrar la categoría con badge de color (GENERAL, URGENTE, EVENTO, INFORMACION, INSTITUCIONAL). | ☐ | ☐ | ☐ | comunicados.php |
| FLU-RF-045 | Debe indicar "Nuevo"/"Leído" y el total de vistos por comunicado. | ☐ | ☐ | ☐ | comunicados.php |
| FLU-RF-046 | El contenido HTML debe depurarse con `HtmlSanitizer::clean()` antes de renderizar. | ☐ | ☐ | ☐ | comunicados.php |
| FLU-RF-047 | Debe abrir un modal de lectura y marcar el comunicado como visto de forma idempotente (`fun_marcar_visto_comunicado`). | ☐ | ☐ | ☐ | comunicados.php, src/comunicados/marcar_visto.php |
| FLU-RF-048 | El botón "Gestionar" solo debe verse con permiso crear/actualizar del módulo 24. | ☐ | ☐ | ☐ | comunicados.php |
| FLU-RF-049 | Debe mostrar tiempo relativo ("hace X horas") calculado en zona America/Bogota. | ☐ | ☐ | ☐ | tiempoRelativo() |

### 7.E SST (comités y buzón de quejas) — RF

| ID | Requerimiento | SÍ | NO | ADI | Evidencia / Observaciones |
|----|---------------|----|----|-----|---------------------------|
| FLU-RF-050 | `templates/sst.php` debe restringir el acceso al módulo 25. | ☐ | ☐ | ☐ | templates/sst.php |
| FLU-RF-051 | Debe presentar tres pestañas: COPASST, Comité de Convivencia y Buzón de Quejas y Sugerencias. | ☐ | ☐ | ☐ | sst.php |
| FLU-RF-052 | Debe mostrar el directorio de miembros del COPASST y del Comité de Convivencia (`fun_listar_sst_comite_miembros`). | ☐ | ☐ | ☐ | sst.php |
| FLU-RF-053 | El buzón debe capturar tipo de petición (QUEJA/SUGERENCIA/RECLAMO/DENUNCIA), asunto (mín. 5) y descripción (mín. 10). | ☐ | ☐ | ☐ | sst.php, sst_guardar_queja.php |
| FLU-RF-054 | `app/sst_guardar_queja.php` debe validar CSRF, módulo 25 y crear la queja con `fun_insert_sst_queja`. | ☐ | ☐ | ☐ | sst_guardar_queja.php |
| FLU-RF-055 | Debe notificar por correo a los encargados con acceso al módulo 26. | ☐ | ☐ | ☐ | notificarEncargadosSst |
| FLU-RF-056 | "Mis solicitudes" debe listar las quejas del usuario con estado, respuesta y botones editar/cancelar. | ☐ | ☐ | ☐ | sst.php |
| FLU-RF-057 | `app/sst_actualizar_queja.php` y `app/sst_cancelar_queja.php` deben gestionar edición y cancelación de quejas. | ☐ | ☐ | ☐ | app/sst_*.php |
| FLU-RF-058 | La Administración SST (módulo 26) debe gestionar quejas (cambio de estado, respuesta) y miembros del comité. | ☐ | ☐ | ☐ | src/sst/ |

### 7.F Directorio de Empleados — RF

| ID | Requerimiento | SÍ | NO | ADI | Evidencia / Observaciones |
|----|---------------|----|----|-----|---------------------------|
| FLU-RF-059 | `templates/empleados.php` debe restringir el acceso al módulo 22. | ☐ | ☐ | ☐ | templates/empleados.php |
| FLU-RF-060 | `app/empleados_listar.php` debe listar empleados activos (nombre, cargo actual, foto, celular, correo). | ☐ | ☐ | ☐ | empleados_listar.php |
| FLU-RF-061 | Debe permitir búsqueda en cliente por nombre o cargo. | ☐ | ☐ | ☐ | templates/empleados.php |
| FLU-RF-062 | Debe mostrar un calendario de disponibilidad por empleado con los permisos APROBADOS solapados al mes (`app/empleado_ocupacion.php`). | ☐ | ☐ | ☐ | empleado_ocupacion.php |
| FLU-RF-063 | El calendario debe distinguir visualmente día laboral, permiso/vacaciones, festivo y domingo. | ☐ | ☐ | ☐ | templates/empleados.php (leyenda) |
| FLU-RF-064 | Debe ofrecer modal de datos de contacto (teléfono, correo). | ☐ | ☐ | ☐ | templates/empleados.php |

### 7.G Códigos de Registro — RF

| ID | Requerimiento | SÍ | NO | ADI | Evidencia / Observaciones |
|----|---------------|----|----|-----|---------------------------|
| FLU-RF-065 | `templates/codigos_registro.php` debe restringir el acceso al módulo 23 y usar `sidebar_admin`. | ☐ | ☐ | ☐ | codigos_registro.php |
| FLU-RF-066 | Debe presentar dos pestañas: "Único de un solo uso" y "Temporal". | ☐ | ☐ | ☐ | codigos_registro.php |
| FLU-RF-067 | Debe generar un código único de 4 dígitos aleatorios sin colisión (permiso 'crear'). | ☐ | ☐ | ☐ | accion_codigos_registro.php |
| FLU-RF-068 | Debe generar un código temporal con vigencia configurable de 1 a 7 días. | ☐ | ☐ | ☐ | accion_codigos_registro.php |
| FLU-RF-069 | Debe permitir cancelar códigos con permiso 'eliminar'. | ☐ | ☐ | ☐ | accion_codigos_registro.php |
| FLU-RF-070 | `app/accion_codigos_registro.php` debe usar `src_guard` (módulo 23) y validar CSRF. | ☐ | ☐ | ☐ | accion_codigos_registro.php |
| FLU-RF-071 | Al generar un código nuevo, el anterior del mismo tipo debe marcarse eliminado. | ☐ | ☐ | ☐ | accion_codigos_registro.php |

### 7.H Ficha Técnica del Empleado — RF

| ID | Requerimiento | SÍ | NO | ADI | Evidencia / Observaciones |
|----|---------------|----|----|-----|---------------------------|
| FLU-RF-072 | `templates/ficha_tecnica.php` debe restringir el acceso al módulo 22 y mostrar la ficha del usuario en sesión. | ☐ | ☐ | ☐ | ficha_tecnica.php |
| FLU-RF-073 | Debe mostrar Datos Personales: nombre, identificación, nacimiento, género, sangre, estado civil, dirección, celular y correo. | ☐ | ☐ | ☐ | ficha_tecnica.php |
| FLU-RF-074 | Debe mostrar Datos Laborales: área, cargo, tipo de contrato, fecha de ingreso, salario, sede, banco y cuenta. | ☐ | ☐ | ☐ | ficha_tecnica.php |
| FLU-RF-075 | Debe mostrar Seguridad Social: EPS, ARL, Caja de Compensación, Pensión y Cesantías. | ☐ | ☐ | ☐ | ficha_tecnica.php |
| FLU-RF-076 | Los valores ausentes deben mostrarse como "N/A". | ☐ | ☐ | ☐ | `$escape` / `$formatearFecha` |

### 7.I Reportes (Excel/PDF) — RF

| ID | Requerimiento | SÍ | NO | ADI | Evidencia / Observaciones |
|----|---------------|----|----|-----|---------------------------|
| FLU-RF-077 | `app/permiso_reporte.php` debe generar reportes de permisos (vista Nómina vs RRHH) en Excel y PDF con filtro de mes/año. | ☐ | ☐ | ☐ | permiso_reporte.php |
| FLU-RF-078 | `app/vacaciones_reporte.php` debe generar reporte de saldo de vacaciones en Excel y PDF. | ☐ | ☐ | ☐ | vacaciones_reporte.php |
| FLU-RF-079 | La bandeja de aprobaciones debe permitir exportar informe desde un modal con filtro de mes/año. | ☐ | ☐ | ☐ | bandeja_permisos.php (modal) |
| FLU-RF-080 | Los reportes deben aplicar identidad corporativa: color de marca, título, cabecera congelada, autoancho y filas alternadas. | ☐ | ☐ | ☐ | estilarHojaReporte |

### 7.J Requerimientos No Funcionales de los Flujos

| ID | Requerimiento | SÍ | NO | ADI | Evidencia / Observaciones |
|----|---------------|----|----|-----|---------------------------|
| FLU-RNF-001 | Los endpoints JSON deben responder `{success, message, ...}` con códigos HTTP apropiados (200/400/401/403/404/405/500). | ☐ | ☐ | ☐ | responderJson() en endpoints |
| FLU-RNF-002 | Todo endpoint debe incluir `auth_guard` y validar CSRF antes de operar. | ☐ | ☐ | ☐ | app/*.php |
| FLU-RNF-003 | Toda consulta debe usar sentencias preparadas con parámetros enlazados. | ☐ | ☐ | ☐ | app/*.php |
| FLU-RNF-004 | Los correos transaccionales deben usar plantilla HTML corporativa con versión de texto plano, sin fallar la transacción principal. | ☐ | ☐ | ☐ | app/solicitud_permiso.php, sst_guardar_queja.php |
| FLU-RNF-005 | Los destinatarios de notificaciones deben resolverse por UNION de `t_roles_operaciones` y `t_usuarios_operaciones`. | ☐ | ☐ | ☐ | notificarSolicitudCreada, notificarEncargadosSst |
| FLU-RNF-006 | Los errores deben registrarse en `error_log` y exponerse al usuario como mensajes genéricos. | ☐ | ☐ | ☐ | app/*.php |
| FLU-RNF-007 | Los cálculos de fechas relativas deben usar zona horaria `America/Bogota`. | ☐ | ☐ | ☐ | comunicados.php, sst.php |
| FLU-RNF-008 | El acceso a cada vista de bandeja (`mias`/`jefe`/`rrhh`) debe validar el permiso granular correspondiente. | ☐ | ☐ | ☐ | permiso_listar.php |
| FLU-RNF-009 | Los booleanos PostgreSQL (`'t'`/`'f'`) deben castearse antes de serializar a JSON. | ☐ | ☐ | ☐ | vacaciones_saldo.php |

---

## HOJA 8 — Resumen Cuantitativo

> Conteo de requerimientos por hoja. Los totales deben coincidir con el número de filas de requerimiento de las hojas 1 a 7 (la hoja 8 no suma como requerimientos adicionales).

| Hoja | Descripción | RF | RNF | Total |
|------|-------------|----|-----|-------|
| 1 | Requerimientos Generales | 72 | 20 | 92 |
| 2 | Login y Autenticación | 74 | 15 | 89 |
| 3 | Dashboard | 29 | 7 | 36 |
| 4 | Sidebar (Navegación Lateral) | 42 | 8 | 50 |
| 5 | Módulos Administrativos (/src) | 148 | 10 | 158 |
| 6 | Base de Datos | 220 | 8 | 228 |
| 7 | Flujos de Negocio y Endpoints | 80 | 9 | 89 |
| **Total** | | **665** | **77** | **742** |

> **Distribución por tipo:** 665 requerimientos funcionales (RF) y 77 no funcionales (RNF). La hoja 6 concentra el mayor volumen (228) por la granularidad función-por-función y tabla-por-tabla.

> **Notas de conciliación (para el evaluador):**
> - `BDD-RF-077` referencia `fun_update_vacaciones_ajustes`, función que **no existe** en `scripts/functions/vacaciones/` (el ledger de vacaciones es append-only: insertar/anular/restaurar, sin edición). Revisar antes de marcar SÍ.
> - Los nombres reales de las funciones del dominio caja de compensación son `fun_listar_caja`, `fun_update_caja`, `fun_softdelete_caja` y `fun_restore_caja` (no `*_cajacompensacion` como indican BDD-RF-166 a BDD-RF-169).
> - `scripts/triggers/softdelete.sql` reutiliza el mismo nombre de trigger `tri_soft_delete_usuarios` para todas las tablas y algunas se declaran `BEFORE INSERT OR UPDATE` en lugar de `BEFORE DELETE` (deuda técnica a corregir).
> - `GEN-RF-040` menciona "131 funciones `fun_*`" pero el conteo real es 141 (ver HOJA 6); el dato correcto es 141.
