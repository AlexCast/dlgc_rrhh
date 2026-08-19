-- ============================================================
-- Migración incremental: Módulo SST
-- ============================================================
-- Aplica en bases de datos vivas sin perder datos existentes.
-- 1. Crea tablas del módulo SST.
-- 2. Crea funciones almacenadas.
-- 3. Actualiza semillas RBAC (módulos 25 y 26).
--
-- Ejecutar como un solo bloque en PostgreSQL.
-- ============================================================

-- --------------------------------------------------------
-- 1. Tablas
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS t_sst_comite_miembros (
    id_miembro          SERIAL,
    nombre_completo     VARCHAR(100) NOT NULL,
    cargo               VARCHAR(50) NOT NULL,
    tipo_comite         VARCHAR(30) NOT NULL CHECK(tipo_comite IN ('COPASST', 'COMITE_CONVIVENCIA')),
    correo              VARCHAR(40),
    telefono            VARCHAR(15),
    orden_visualizacion INT NOT NULL DEFAULT 0,
    usr_insert          VARCHAR NOT NULL,
    fec_insert          TIMESTAMP WITHOUT TIME ZONE NOT NULL,
    usr_update          VARCHAR,
    fec_update          TIMESTAMP WITHOUT TIME ZONE,
    usr_delete          VARCHAR,
    fec_delete          TIMESTAMP WITHOUT TIME ZONE,
    PRIMARY KEY (id_miembro)
);

CREATE INDEX IF NOT EXISTS idx_sst_comite_tipo_orden
    ON t_sst_comite_miembros(tipo_comite, orden_visualizacion);

CREATE TABLE IF NOT EXISTS t_sst_buzon_quejas (
    id_queja            SERIAL,
    id_usuario          VARCHAR(20) NOT NULL,
    tipo_peticion       VARCHAR(30) NOT NULL CHECK(tipo_peticion IN ('QUEJA', 'SUGERENCIA', 'RECLAMO', 'DENUNCIA')),
    asunto              VARCHAR(150) NOT NULL,
    descripcion         TEXT NOT NULL,
    estado              VARCHAR(20) NOT NULL DEFAULT 'PENDIENTE' CHECK(estado IN ('PENDIENTE', 'EN PROCESO', 'RESUELTO', 'CANCELADO_USUARIO', 'CANCELADO_ENCARGADO')),
    respuesta           TEXT,
    id_encargado        VARCHAR(20),
    fec_respuesta       TIMESTAMP WITHOUT TIME ZONE,
    cancelado_por       VARCHAR(20),
    fec_cancelacion     TIMESTAMP WITHOUT TIME ZONE,
    usr_insert          VARCHAR NOT NULL,
    fec_insert          TIMESTAMP WITHOUT TIME ZONE NOT NULL,
    usr_update          VARCHAR,
    fec_update          TIMESTAMP WITHOUT TIME ZONE,
    usr_delete          VARCHAR,
    fec_delete          TIMESTAMP WITHOUT TIME ZONE,
    PRIMARY KEY (id_queja),
    FOREIGN KEY (id_usuario) REFERENCES t_usuarios(id_usuario),
    FOREIGN KEY (id_encargado) REFERENCES t_usuarios(id_usuario),
    FOREIGN KEY (cancelado_por) REFERENCES t_usuarios(id_usuario)
);

CREATE INDEX IF NOT EXISTS idx_sst_quejas_usuario
    ON t_sst_buzon_quejas(id_usuario, fec_insert DESC);

CREATE INDEX IF NOT EXISTS idx_sst_quejas_estado
    ON t_sst_buzon_quejas(estado, fec_insert DESC);

-- --------------------------------------------------------
-- 2. Triggers de auditoría
-- --------------------------------------------------------
CREATE OR REPLACE TRIGGER tri_audit_usuarios BEFORE INSERT OR UPDATE ON t_sst_comite_miembros
FOR EACH ROW EXECUTE PROCEDURE fun_audit_tablas();

CREATE OR REPLACE TRIGGER tri_audit_usuarios BEFORE INSERT OR UPDATE ON t_sst_buzon_quejas
FOR EACH ROW EXECUTE PROCEDURE fun_audit_tablas();

-- --------------------------------------------------------
-- 3. Funciones
-- --------------------------------------------------------
\ir ../functions/sst/fun_insert_sst_comite_miembro.sql
\ir ../functions/sst/fun_update_sst_comite_miembro.sql
\ir ../functions/sst/fun_listar_sst_comite_miembros.sql
\ir ../functions/sst/fun_softdelete_sst_comite_miembro.sql
\ir ../functions/sst/fun_restore_sst_comite_miembro.sql
\ir ../functions/sst/fun_insert_sst_queja.sql
\ir ../functions/sst/fun_update_sst_queja.sql
\ir ../functions/sst/fun_listar_sst_quejas_usuario.sql
\ir ../functions/sst/fun_listar_sst_quejas_encargado.sql
\ir ../functions/sst/fun_cancelar_sst_queja_usuario.sql
\ir ../functions/sst/fun_cambiar_estado_sst_queja.sql

-- --------------------------------------------------------
-- 4. RBAC
-- --------------------------------------------------------
INSERT INTO t_modulos (id_modulo, nombre_modulo, usr_insert, fec_insert)
VALUES
	    (25, 'SST', 'seed', CURRENT_TIMESTAMP),
    (26, 'Administración SST', 'seed', CURRENT_TIMESTAMP)
ON CONFLICT (id_modulo) DO UPDATE SET nombre_modulo = EXCLUDED.nombre_modulo,
                                      fec_delete    = NULL,
                                      usr_delete    = NULL;

INSERT INTO t_operaciones (id_operacion, id_modulo, nombre_operacion, usr_insert, fec_insert)
VALUES
    (251, 25, 'VER',        'seed', CURRENT_TIMESTAMP),
    (252, 25, 'CREAR',      'seed', CURRENT_TIMESTAMP),
    (253, 25, 'ACTUALIZAR', 'seed', CURRENT_TIMESTAMP),
    (254, 25, 'ELIMINAR',   'seed', CURRENT_TIMESTAMP),
    (255, 25, 'RESTAURAR',  'seed', CURRENT_TIMESTAMP),
    (261, 26, 'VER',        'seed', CURRENT_TIMESTAMP),
    (262, 26, 'CREAR',      'seed', CURRENT_TIMESTAMP),
    (263, 26, 'ACTUALIZAR', 'seed', CURRENT_TIMESTAMP),
    (264, 26, 'ELIMINAR',   'seed', CURRENT_TIMESTAMP),
    (265, 26, 'RESTAURAR',  'seed', CURRENT_TIMESTAMP)
ON CONFLICT (id_operacion) DO UPDATE SET id_modulo        = EXCLUDED.id_modulo,
                                          nombre_operacion = EXCLUDED.nombre_operacion,
                                          fec_delete       = NULL,
                                          usr_delete       = NULL;

-- ADMINISTRADOR: todo (incluye módulos 25 y 26)
INSERT INTO t_roles_operaciones (id_rol, id_operacion, usr_insert, fec_insert)
SELECT 1, id_operacion, 'seed', CURRENT_TIMESTAMP
FROM t_operaciones
WHERE fec_delete IS NULL
ON CONFLICT (id_rol, id_operacion) DO UPDATE SET fec_delete = NULL,
                                                   usr_delete = NULL;

-- EMPLEADO: acceso de visualización al módulo SST público
INSERT INTO t_roles_operaciones (id_rol, id_operacion, usr_insert, fec_insert)
VALUES (2, 251, 'seed', CURRENT_TIMESTAMP)
ON CONFLICT (id_rol, id_operacion) DO UPDATE SET fec_delete = NULL,
                                                   usr_delete = NULL;
