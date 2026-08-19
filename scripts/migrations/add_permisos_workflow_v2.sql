-- ============================================================
-- Migración: Rediseño del módulo de Solicitudes y Permisos
-- (doble aprobación jefe+RRHH, evidencias externas, flexibilidad
--  en tipos de permiso, festivos de empresa con excepciones).
-- Aplicar sobre una base de datos VIVA que ya tiene datos.
-- No recrea tablas existentes: solo ALTER + CREATE de lo nuevo.
-- Ejecutar dentro de una transacción y revisar los pasos marcados
-- con [REVISAR] antes de correr en producción.
-- ============================================================

BEGIN;

-- --------------------------------------------------------
-- 1. t_tipos_permisos: flexibilidad de descuento y evidencia
-- --------------------------------------------------------
ALTER TABLE t_tipos_permisos
    ADD COLUMN IF NOT EXISTS porcentaje_descuento NUMERIC(5,2),
    ADD COLUMN IF NOT EXISTS requiere_evidencia BOOLEAN NOT NULL DEFAULT FALSE;

-- [REVISAR] si ya existen tipos con descuenta_tiempo = TRUE, deben tener un % antes de aplicar el CHECK.
UPDATE t_tipos_permisos
   SET porcentaje_descuento = 100
 WHERE descuenta_tiempo = TRUE
   AND porcentaje_descuento IS NULL;

ALTER TABLE t_tipos_permisos
    DROP CONSTRAINT IF EXISTS chk_tipos_permisos_descuento,
    ADD CONSTRAINT chk_tipos_permisos_descuento CHECK (
        (descuenta_tiempo = FALSE AND porcentaje_descuento IS NULL) OR
        (descuenta_tiempo = TRUE AND porcentaje_descuento IS NOT NULL)
    ),
    ADD CONSTRAINT chk_tipos_permisos_porcentaje CHECK (
        porcentaje_descuento IS NULL OR (porcentaje_descuento >= 0 AND porcentaje_descuento <= 100)
    );

-- --------------------------------------------------------
-- 2. t_dias_festivos: tipo (nacional/empresa) + descuento por defecto
-- --------------------------------------------------------
ALTER TABLE t_dias_festivos
    ADD COLUMN IF NOT EXISTS tipo_festivo VARCHAR(20) NOT NULL DEFAULT 'NACIONAL',
    ADD COLUMN IF NOT EXISTS descuenta_salario BOOLEAN NOT NULL DEFAULT TRUE;

ALTER TABLE t_dias_festivos
    DROP CONSTRAINT IF EXISTS chk_dias_festivos_tipo,
    ADD CONSTRAINT chk_dias_festivos_tipo CHECK (tipo_festivo IN ('NACIONAL', 'EMPRESA'));

CREATE TABLE IF NOT EXISTS t_dias_festivos_excepciones (
    id_excepcion        SERIAL,
    id_festivo          INT NOT NULL,
    id_empleado         VARCHAR(20) NOT NULL,
    descuenta_salario   BOOLEAN NOT NULL,
    motivo              VARCHAR(255),
    usr_insert          VARCHAR NOT NULL,
    fec_insert          TIMESTAMP WITHOUT TIME ZONE NOT NULL,
    usr_update          VARCHAR,
    fec_update          TIMESTAMP WITHOUT TIME ZONE,
    usr_delete          VARCHAR,
    fec_delete          TIMESTAMP WITHOUT TIME ZONE,
    PRIMARY KEY (id_excepcion),
    CONSTRAINT uq_festivo_excepcion_empleado UNIQUE (id_festivo, id_empleado),
    FOREIGN KEY (id_festivo) REFERENCES t_dias_festivos(id_festivo),
    FOREIGN KEY (id_empleado) REFERENCES t_empleados(id_usuario)
);

-- --------------------------------------------------------
-- 3. t_solicitudes_permisos: jefe responsable (snapshot) + estado agregado
-- --------------------------------------------------------
ALTER TABLE t_solicitudes_permisos
    ADD COLUMN IF NOT EXISTS id_jefe_responsable VARCHAR(20);

-- [REVISAR] backfill: toma el jefe directo ACTUAL de t_empleados para las solicitudes existentes.
-- Si un empleado no tiene jefe asignado, quedará en NULL y debe resolverse manualmente antes del paso 4.
UPDATE t_solicitudes_permisos sp
   SET id_jefe_responsable = e.id_jefe
  FROM t_empleados e
 WHERE sp.id_empleado = e.id_usuario
   AND sp.id_jefe_responsable IS NULL
   AND e.id_jefe IS NOT NULL;

-- [REVISAR] Ejecutar manualmente antes de continuar si la siguiente consulta devuelve filas:
-- SELECT id_permiso, id_empleado FROM t_solicitudes_permisos WHERE id_jefe_responsable IS NULL;

ALTER TABLE t_solicitudes_permisos
    ALTER COLUMN id_jefe_responsable SET NOT NULL,
    ADD CONSTRAINT fk_solicitudes_permisos_jefe FOREIGN KEY (id_jefe_responsable) REFERENCES t_usuarios(id_usuario);

ALTER TABLE t_solicitudes_permisos
    DROP CONSTRAINT IF EXISTS t_solicitudes_permisos_estado_check,
    ADD CONSTRAINT t_solicitudes_permisos_estado_check CHECK (
        estado IN ('PENDIENTE', 'EN_REVISION', 'APROBADO', 'RECHAZADO', 'CANCELADO')
    );

-- --------------------------------------------------------
-- 4. Doble aprobación (JEFE + RRHH)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS t_permisos_aprobaciones (
    id_aprobacion           SERIAL,
    id_permiso              INT NOT NULL,
    nivel_aprobacion        VARCHAR(10) NOT NULL CHECK (nivel_aprobacion IN ('JEFE', 'RRHH')),
    id_aprobador            VARCHAR(20),
    estado                  VARCHAR(20) NOT NULL DEFAULT 'PENDIENTE' CHECK (estado IN ('PENDIENTE', 'APROBADO', 'RECHAZADO')),
    observacion             VARCHAR(255),
    fec_resolucion          TIMESTAMP WITHOUT TIME ZONE,
    usr_insert              VARCHAR NOT NULL,
    fec_insert              TIMESTAMP WITHOUT TIME ZONE NOT NULL,
    usr_update              VARCHAR,
    fec_update              TIMESTAMP WITHOUT TIME ZONE,
    usr_delete              VARCHAR,
    fec_delete              TIMESTAMP WITHOUT TIME ZONE,
    PRIMARY KEY (id_aprobacion),
    CONSTRAINT uq_permiso_nivel_aprobacion UNIQUE (id_permiso, nivel_aprobacion),
    FOREIGN KEY (id_permiso) REFERENCES t_solicitudes_permisos(id_permiso),
    FOREIGN KEY (id_aprobador) REFERENCES t_usuarios(id_usuario)
);

CREATE INDEX IF NOT EXISTS idx_permisos_aprobaciones_aprobador ON t_permisos_aprobaciones(id_aprobador, estado);

-- [REVISAR] Si t_solicitudes_permisos ya tenía id_aprobador/observacion_aprobador con datos reales,
-- migrarlos aquí ANTES de soltar las columnas (se asume que el flujo anterior de un solo aprobador
-- corresponde al nivel RRHH; el nivel JEFE queda PENDIENTE para que se resuelva bajo el nuevo flujo).
-- Descomentar y ajustar si aplica:
-- INSERT INTO t_permisos_aprobaciones (id_permiso, nivel_aprobacion, id_aprobador, estado, observacion, fec_resolucion, usr_insert, fec_insert)
-- SELECT id_permiso, 'RRHH', id_aprobador, estado, observacion_aprobador,
--        CASE WHEN estado IN ('APROBADO','RECHAZADO') THEN fec_update ELSE NULL END,
--        'migracion', CURRENT_TIMESTAMP
--   FROM t_solicitudes_permisos
--  WHERE id_aprobador IS NOT NULL
-- ON CONFLICT (id_permiso, nivel_aprobacion) DO NOTHING;

ALTER TABLE t_solicitudes_permisos
    DROP COLUMN IF EXISTS id_aprobador,
    DROP COLUMN IF EXISTS observacion_aprobador,
    DROP COLUMN IF EXISTS url_evidencia;

-- --------------------------------------------------------
-- 5. Evidencias en servicio externo (Bunny.net / Cloudflare / B2)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS t_permisos_evidencias (
    id_evidencia            SERIAL,
    id_permiso              INT NOT NULL,
    proveedor               VARCHAR(20) NOT NULL DEFAULT 'BUNNY' CHECK (proveedor IN ('BUNNY', 'R2', 'B2', 'CLOUDFLARE_IMAGES')),
    storage_key             VARCHAR(255) NOT NULL,
    url_publica              VARCHAR(500),
    nombre_original         VARCHAR(255) NOT NULL,
    mime_type               VARCHAR(100) NOT NULL CHECK (mime_type IN ('image/jpeg', 'image/png', 'application/pdf')),
    tamano_bytes            INT NOT NULL CHECK (tamano_bytes > 0 AND tamano_bytes <= 10485760),
    usr_insert              VARCHAR NOT NULL,
    fec_insert              TIMESTAMP WITHOUT TIME ZONE NOT NULL,
    usr_update              VARCHAR,
    fec_update              TIMESTAMP WITHOUT TIME ZONE,
    usr_delete              VARCHAR,
    fec_delete              TIMESTAMP WITHOUT TIME ZONE,
    PRIMARY KEY (id_evidencia),
    FOREIGN KEY (id_permiso) REFERENCES t_solicitudes_permisos(id_permiso)
);

CREATE INDEX IF NOT EXISTS idx_permisos_evidencias_permiso ON t_permisos_evidencias(id_permiso);

-- --------------------------------------------------------
-- 6. Triggers de auditoría para las tablas nuevas
-- --------------------------------------------------------
CREATE OR REPLACE TRIGGER tri_audit_usuarios BEFORE INSERT OR UPDATE ON t_permisos_aprobaciones
FOR EACH ROW EXECUTE PROCEDURE fun_audit_tablas();

CREATE OR REPLACE TRIGGER tri_audit_usuarios BEFORE INSERT OR UPDATE ON t_permisos_evidencias
FOR EACH ROW EXECUTE PROCEDURE fun_audit_tablas();

CREATE OR REPLACE TRIGGER tri_audit_usuarios BEFORE INSERT OR UPDATE ON t_dias_festivos_excepciones
FOR EACH ROW EXECUTE PROCEDURE fun_audit_tablas();

COMMIT;
