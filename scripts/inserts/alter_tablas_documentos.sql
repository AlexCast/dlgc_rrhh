-- ============================================================
-- alter_tablas_documentos.sql
-- Aplicación rápida de los cambios de esquema de los módulos
-- Documentación (31) y Gestor de Archivos (30) sobre una base
-- EXISTENTE, sin reconstruir el modelo completo.
--
-- Contenido:
--   A) Corrección de collation (arregla el error 1267 que rompe
--      el login y otras rutinas con parámetros VARCHAR).
--   B) Tablas de documentos (CREATE TABLE IF NOT EXISTS).
--   C) UNIQUE (id_contrato, id_usuario) en t_contratos_empleados
--      (requisito de la FK compuesta de t_documentos_rrhh).
--   D) proveedor='LOCAL' en t_documentos_versiones.
--
-- DESPUÉS de ejecutar este archivo:
--   1) PowerShell: .\scripts\rebuild_functions.ps1
--      (recrea TODAS las fun_/proc_ con la collation correcta;
--       sin esto proc_login_usuarios sigue fallando).
--   2) source scripts/inserts/rbac_seeds_mariadb.sql
--      (módulos 30/31 + operaciones + roles; idempotente).
--   3) source scripts/inserts/documentos_seeds.sql
--      (catálogo de tipos de documento; idempotente).
-- ============================================================

-- --------------------------------------------------------
-- A) COLLATION
--    Si la base se creó con el default de MariaDB 13
--    (utf8mb4_uca1400_ai_ci) y las tablas del proyecto son
--    utf8mb4_unicode_ci, los parámetros VARCHAR de las rutinas
--    heredan la collation de la BD al momento de crearlas y
--    comparar parámetro contra columna lanza:
--      ERROR 1267 (HY000): Illegal mix of collations
--    (afecta a proc_login_usuarios => login con "error_servidor").
--
--    Sin nombre de base de datos: aplica a la BD seleccionada.
-- --------------------------------------------------------
ALTER DATABASE CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- B) TABLAS DE DOCUMENTOS (si aún no existen)
-- --------------------------------------------------------

-- Catálogo de tipos de documento (reemplaza el texto libre)
CREATE TABLE IF NOT EXISTS t_tipos_documento_rrhh (
    id_tipo_documento   INT AUTO_INCREMENT,
    codigo              VARCHAR(30) NOT NULL UNIQUE,
    nombre              VARCHAR(80) NOT NULL,
    usr_insert          VARCHAR(255) NOT NULL,
    fec_insert          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    usr_update          VARCHAR(255),
    fec_update          DATETIME,
    usr_delete          VARCHAR(255),
    fec_delete          DATETIME,
    PRIMARY KEY (id_tipo_documento)
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Tabla lógica: el "contenedor" del documento
CREATE TABLE IF NOT EXISTS t_documentos_rrhh (
    id_documento        INT AUTO_INCREMENT,
    id_usuario          VARCHAR(20) NOT NULL,
    id_tipo_documento   INT NOT NULL,
    id_contrato         INT NULL,
    titulo              VARCHAR(150) NOT NULL,
    estado              VARCHAR(20) NOT NULL DEFAULT 'ACTIVO' CHECK (estado IN ('ACTIVO', 'INACTIVO', 'ARCHIVADO')),
    usr_insert          VARCHAR(255) NOT NULL,
    fec_insert          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    usr_update          VARCHAR(255),
    fec_update          DATETIME,
    usr_delete          VARCHAR(255),
    fec_delete          DATETIME,
    PRIMARY KEY (id_documento),
    FOREIGN KEY (id_usuario) REFERENCES t_empleados(id_usuario),
    FOREIGN KEY (id_tipo_documento) REFERENCES t_tipos_documento_rrhh(id_tipo_documento),
    CONSTRAINT fk_documentos_contrato_usuario
        FOREIGN KEY (id_contrato, id_usuario) REFERENCES t_contratos_empleados(id_contrato, id_usuario)
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE INDEX IF NOT EXISTS idx_documentos_usuario_tipo ON t_documentos_rrhh(id_usuario, id_tipo_documento);
CREATE INDEX IF NOT EXISTS idx_documentos_tipo ON t_documentos_rrhh(id_tipo_documento);

-- Tabla física: archivos subidos
CREATE TABLE IF NOT EXISTS t_documentos_versiones (
    id_version          INT AUTO_INCREMENT,
    id_documento        INT NOT NULL,
    numero_version      INT NULL CHECK (numero_version IS NULL OR numero_version > 0),
    proveedor           VARCHAR(20) NOT NULL DEFAULT 'LOCAL' CHECK (proveedor IN ('LOCAL', 'R2')),
    storage_key         VARCHAR(255) NOT NULL UNIQUE,
    nombre_original     VARCHAR(255) NOT NULL,
    mime_type           VARCHAR(100) NOT NULL CHECK (mime_type IN ('application/pdf', 'image/jpeg', 'image/png', 'image/webp')),
    tamano_bytes        INT NOT NULL CHECK (tamano_bytes > 0 AND tamano_bytes <= 10485760),
    hash_sha256         CHAR(64) NOT NULL,
    es_actual           BOOLEAN NOT NULL DEFAULT FALSE,
    observacion         VARCHAR(255),
    estado_aprobacion   VARCHAR(20) NOT NULL DEFAULT 'PENDIENTE' CHECK (estado_aprobacion IN ('PENDIENTE', 'APROBADO', 'RECHAZADO')),
    motivo_rechazo      VARCHAR(255),
    id_aprobador        VARCHAR(20),
    fec_resolucion      DATETIME,
    usr_insert          VARCHAR(255) NOT NULL,
    fec_insert          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    usr_update          VARCHAR(255),
    fec_update          DATETIME,
    usr_delete          VARCHAR(255),
    fec_delete          DATETIME,
    actual_unico        INT AS (IF(es_actual = 1 AND fec_delete IS NULL, id_documento, NULL)) VIRTUAL,
    PRIMARY KEY (id_version),
    CONSTRAINT uq_doc_version   UNIQUE (id_documento, numero_version),
    CONSTRAINT uq_version_actual UNIQUE (actual_unico),
    CONSTRAINT chk_versiones_estado CHECK (
        (estado_aprobacion = 'PENDIENTE'
            AND numero_version IS NULL AND es_actual = 0
            AND id_aprobador IS NULL AND fec_resolucion IS NULL AND motivo_rechazo IS NULL)
        OR
        (estado_aprobacion = 'APROBADO'
            AND numero_version IS NOT NULL
            AND id_aprobador IS NOT NULL AND fec_resolucion IS NOT NULL AND motivo_rechazo IS NULL)
        OR
        (estado_aprobacion = 'RECHAZADO'
            AND numero_version IS NULL AND es_actual = 0
            AND id_aprobador IS NOT NULL AND fec_resolucion IS NOT NULL AND motivo_rechazo IS NOT NULL)
    ),
    FOREIGN KEY (id_documento) REFERENCES t_documentos_rrhh(id_documento),
    CONSTRAINT fk_versiones_aprobador FOREIGN KEY (id_aprobador) REFERENCES t_usuarios(id_usuario)
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE INDEX IF NOT EXISTS idx_versiones_documento_actual ON t_documentos_versiones(id_documento, es_actual);
CREATE INDEX IF NOT EXISTS idx_versiones_pendientes ON t_documentos_versiones(estado_aprobacion, fec_insert);
CREATE INDEX IF NOT EXISTS idx_versiones_aprobador ON t_documentos_versiones(id_aprobador);

-- --------------------------------------------------------
-- C) UNIQUE (id_contrato, id_usuario) en t_contratos_empleados
--    (guardián: solo si el constraint no existe)
-- --------------------------------------------------------
SET @uq_sql = (
    SELECT IF(COUNT(*) = 0,
        'ALTER TABLE t_contratos_empleados ADD CONSTRAINT uq_contrato_usuario UNIQUE (id_contrato, id_usuario)',
        'SELECT 1')
    FROM information_schema.TABLE_CONSTRAINTS
    WHERE CONSTRAINT_SCHEMA = DATABASE()
      AND TABLE_NAME = 't_contratos_empleados'
      AND CONSTRAINT_NAME = 'uq_contrato_usuario'
);
PREPARE uq_stmt FROM @uq_sql;
EXECUTE uq_stmt;
DEALLOCATE PREPARE uq_stmt;

-- --------------------------------------------------------
-- D) PROVEEDOR LOCAL en t_documentos_versiones
--    (para tablas creadas antes de ampliar el CHECK)
-- --------------------------------------------------------
ALTER TABLE t_documentos_versiones
    MODIFY COLUMN proveedor VARCHAR(20) NOT NULL DEFAULT 'LOCAL'
    CHECK (proveedor IN ('LOCAL', 'R2'));
