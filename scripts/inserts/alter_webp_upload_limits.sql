-- ============================================================
-- alter_webp_upload_limits.sql
-- Migración idempotente:
--   A) Habilita image/webp en evidencias de permisos y versiones
--      del gestor de documentación.
--   B) Crea t_intentos_subida: rate limiting de subidas de archivos
--      (foto de perfil, evidencias, gestor y documentación).
-- ============================================================

-- A1) Evidencias de solicitudes de permiso
ALTER TABLE t_permisos_evidencias
    MODIFY COLUMN mime_type VARCHAR(100) NOT NULL
    CHECK (mime_type IN ('image/jpeg', 'image/png', 'image/webp', 'application/pdf'));

-- A2) Versiones del gestor de documentación
ALTER TABLE t_documentos_versiones
    MODIFY COLUMN mime_type VARCHAR(100) NOT NULL
    CHECK (mime_type IN ('application/pdf', 'image/jpeg', 'image/png', 'image/webp'));

-- B) Rate limiting de subidas
CREATE TABLE IF NOT EXISTS t_intentos_subida (
    id_intento     INT AUTO_INCREMENT,
    id_usuario     VARCHAR(20) NOT NULL,
    tipo           VARCHAR(40) NOT NULL CHECK (tipo IN ('foto_perfil', 'evidencias', 'gestor_subida', 'documento_subida')),
    contador       INT NOT NULL DEFAULT 1,
    ultimo_intento DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    usr_insert     VARCHAR(255) NOT NULL DEFAULT 'sistema',
    fec_insert     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    usr_update     VARCHAR(255),
    fec_update     DATETIME,
    PRIMARY KEY (id_intento),
    UNIQUE KEY uq_intentos_subida_usuario_tipo (id_usuario, tipo)
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE INDEX IF NOT EXISTS idx_intentos_subida_ultimo ON t_intentos_subida(ultimo_intento);
