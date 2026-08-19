-- Migración: verificación de correo electrónico para login/activación de cuentas.
-- Ejecutar sobre una base de datos existente de DLGC_RRHH.
-- IMPORTANTE: Esta migración asume que no hay registros pendientes previos.
-- Si ya existen usuarios no verificados, considérese limpiarlos manualmente antes.

-- 1. Agregar columna de verificación a t_usuarios (si no existe).
DO $$
BEGIN
    IF NOT EXISTS (
        SELECT 1
        FROM information_schema.columns
        WHERE table_schema = 'public'
          AND table_name = 't_usuarios'
          AND column_name = 'correo_verificado'
    ) THEN
        ALTER TABLE t_usuarios
        ADD COLUMN correo_verificado BOOLEAN NOT NULL DEFAULT FALSE;
    END IF;
END $$;

-- 2. Los usuarios existentes quedan verificados para no bloquear el acceso.
UPDATE t_usuarios
SET correo_verificado = TRUE
WHERE correo_verificado IS NULL OR correo_verificado = FALSE;

-- 3. Tabla de rate limiting para envío de correos.
DROP TABLE IF EXISTS t_intentos_correo;

CREATE TABLE IF NOT EXISTS t_intentos_correo (
    id_intento SERIAL PRIMARY KEY,
    id_usuario VARCHAR(20) NOT NULL,
    tipo VARCHAR(30) NOT NULL CHECK (tipo IN ('verificacion', 'recuperacion')),
    contador INT NOT NULL DEFAULT 1,
    ultimo_intento TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP,
    usr_insert VARCHAR NOT NULL DEFAULT 'sistema',
    fec_insert TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP,
    usr_update VARCHAR,
    fec_update TIMESTAMP WITHOUT TIME ZONE,
    CONSTRAINT uq_intentos_usuario_tipo UNIQUE (id_usuario, tipo)
);

CREATE INDEX IF NOT EXISTS idx_intentos_usuario_tipo ON t_intentos_correo(id_usuario, tipo);
CREATE INDEX IF NOT EXISTS idx_intentos_ultimo ON t_intentos_correo(ultimo_intento);

-- 4. Tabla de registros pendientes de verificación.DROP TABLE IF EXISTS t_registros_pendientes;
CREATE TABLE IF NOT EXISTS t_registros_pendientes (
    id_registro_pendiente SERIAL PRIMARY KEY,
    id_usuario VARCHAR(20) NOT NULL UNIQUE,
    id_rol INT NOT NULL,
    username VARCHAR(30) NOT NULL UNIQUE,
    tipo_documento VARCHAR(20) NOT NULL CHECK (tipo_documento IN ('CC', 'PPT', 'CE')),
    primer_nombre VARCHAR(30) NOT NULL,
    segundo_nombre VARCHAR(30),
    primer_apellido VARCHAR(30) NOT NULL,
    segundo_apellido VARCHAR(30),
    correo VARCHAR(40) NOT NULL UNIQUE,
    contrasena VARCHAR(255) NOT NULL,
    fecha_expiracion TIMESTAMP WITHOUT TIME ZONE NOT NULL,
    usr_insert VARCHAR NOT NULL,
    fec_insert TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP,
    usr_update VARCHAR,
    fec_update TIMESTAMP WITHOUT TIME ZONE,
    usr_delete VARCHAR,
    fec_delete TIMESTAMP WITHOUT TIME ZONE
);

CREATE INDEX IF NOT EXISTS idx_registros_pendientes_expiracion ON t_registros_pendientes(fecha_expiracion);

-- 5. Tabla de tokens de verificación de correo.
-- Se elimina y recrea para asegurar la estructura correcta con id_registro_pendiente.
DROP TABLE IF EXISTS t_verificacion_correo;

CREATE TABLE IF NOT EXISTS t_verificacion_correo (
    id_verificacion SERIAL PRIMARY KEY,
    id_registro_pendiente INT NOT NULL REFERENCES t_registros_pendientes(id_registro_pendiente) ON DELETE CASCADE,
    token_hash VARCHAR(255) NOT NULL,
    fecha_expiracion TIMESTAMP WITHOUT TIME ZONE NOT NULL,
    fecha_uso TIMESTAMP WITHOUT TIME ZONE,
    usr_insert VARCHAR NOT NULL,
    fec_insert TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP,
    usr_update VARCHAR,
    fec_update TIMESTAMP WITHOUT TIME ZONE,
    usr_delete VARCHAR,
    fec_delete TIMESTAMP WITHOUT TIME ZONE
);

CREATE INDEX IF NOT EXISTS idx_verificacion_token ON t_verificacion_correo(token_hash);
CREATE INDEX IF NOT EXISTS idx_verificacion_registro ON t_verificacion_correo(id_registro_pendiente);

-- 6. Actualizar función de login para devolver estado de verificación.
DROP FUNCTION IF EXISTS fun_login_usuarios(VARCHAR);

CREATE OR REPLACE FUNCTION fun_login_usuarios(widentificador VARCHAR)
RETURNS TABLE (
    id_usuario        t_usuarios.id_usuario%TYPE,
    username          t_usuarios.username%TYPE,
    id_rol            t_usuarios.id_rol%TYPE,
    primer_nombre     t_usuarios.primer_nombre%TYPE,
    primer_apellido   t_usuarios.primer_apellido%TYPE,
    correo            t_usuarios.correo%TYPE,
    correo_verificado t_usuarios.correo_verificado%TYPE,
    contrasena        t_usuarios.contrasena%TYPE
) AS
$$
BEGIN
    RETURN QUERY
    SELECT u.id_usuario, u.username, u.id_rol, u.primer_nombre, u.primer_apellido, u.correo, u.correo_verificado, u.contrasena
    FROM t_usuarios u
    WHERE (u.username = widentificador OR u.correo = widentificador)
        AND u.fec_delete IS NULL;
END;
$$
LANGUAGE PLPGSQL;

-- 7. Trigger opcional: borrar físicamente los tokens de verificación usados o marcados como eliminados.
CREATE OR REPLACE FUNCTION fun_borrar_token_verificacion()
RETURNS TRIGGER AS
$$
BEGIN
    DELETE FROM t_verificacion_correo WHERE id_verificacion = OLD.id_verificacion;
    RETURN NULL;
END;
$$
LANGUAGE PLPGSQL;

DROP TRIGGER IF EXISTS tri_borrar_token_verificacion ON t_verificacion_correo;
CREATE TRIGGER tri_borrar_token_verificacion
AFTER UPDATE OF fecha_uso, fec_delete ON t_verificacion_correo
FOR EACH ROW
WHEN (NEW.fecha_uso IS NOT NULL OR NEW.fec_delete IS NOT NULL)
EXECUTE FUNCTION fun_borrar_token_verificacion();

-- 8. Trigger para limpiar registros pendientes al verificar o invalidar token
CREATE OR REPLACE FUNCTION fun_limpiar_registros_pendientes()
RETURNS TRIGGER AS
$$
DECLARE
    v_id_registro_pendiente INT;
BEGIN
    SELECT id_registro_pendiente INTO v_id_registro_pendiente
    FROM t_verificacion_correo
    WHERE id_verificacion = OLD.id_verificacion;

    IF v_id_registro_pendiente IS NOT NULL THEN
        DELETE FROM t_registros_pendientes
        WHERE id_registro_pendiente = v_id_registro_pendiente;
    END IF;

    RETURN NULL;
END;
$$
LANGUAGE PLPGSQL;

DROP TRIGGER IF EXISTS tri_limpiar_registros_pendientes ON t_verificacion_correo;
CREATE TRIGGER tri_limpiar_registros_pendientes
AFTER UPDATE OF fecha_uso, fec_delete ON t_verificacion_correo
FOR EACH ROW
WHEN (NEW.fecha_uso IS NOT NULL OR NEW.fec_delete IS NOT NULL)
EXECUTE FUNCTION fun_limpiar_registros_pendientes();

-- 9. Procedimiento para limpiar registros pendientes expirados
CREATE OR REPLACE PROCEDURE sp_limpiar_registros_pendientes_expirados()
LANGUAGE SQL
AS $$
    DELETE FROM t_registros_pendientes
    WHERE fecha_expiracion < CURRENT_TIMESTAMP
       OR fec_delete IS NOT NULL;
$$;

-- Limpieza inicial de registros pendientes expirados si los hubiera.
CALL sp_limpiar_registros_pendientes_expirados();
