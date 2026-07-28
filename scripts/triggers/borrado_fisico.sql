/*------------------------------------------------------------*/
/* GRUPO 3 - BORRADO FÍSICO                                   */
/* Elimina físicamente registros temporales una vez que son   */
/* usados o marcados como eliminados (no son soft delete).    */
/*------------------------------------------------------------*/

-- t_codigos_registro: se borra físicamente al usar o cancelar código
CREATE OR REPLACE FUNCTION fun_borrar_codigo_registro()
RETURNS TRIGGER AS
$$
BEGIN
    DELETE FROM t_codigos_registro WHERE id_codigo = OLD.id_codigo;
    RETURN NULL;
END;
$$
LANGUAGE PLPGSQL;

CREATE OR REPLACE TRIGGER tri_borrar_codigo_registro
AFTER UPDATE OF usado, fec_delete ON t_codigos_registro
FOR EACH ROW
WHEN (NEW.usado = TRUE OR NEW.fec_delete IS NOT NULL)
EXECUTE FUNCTION fun_borrar_codigo_registro();

-- t_verificacion_correo: se borra físicamente al usar o marcar token
CREATE OR REPLACE FUNCTION fun_borrar_token_verificacion()
RETURNS TRIGGER AS
$$
BEGIN
    DELETE FROM t_verificacion_correo WHERE id_verificacion = OLD.id_verificacion;
    RETURN NULL;
END;
$$
LANGUAGE PLPGSQL;

CREATE OR REPLACE TRIGGER tri_borrar_token_verificacion
AFTER UPDATE OF fecha_uso, fec_delete ON t_verificacion_correo
FOR EACH ROW
WHEN (NEW.fecha_uso IS NOT NULL OR NEW.fec_delete IS NOT NULL)
EXECUTE FUNCTION fun_borrar_token_verificacion();

-- t_registros_pendientes: se limpian al verificar o marcar token de verificación
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
        -- ON DELETE CASCADE de t_verificacion_correo hace el resto.
        DELETE FROM t_registros_pendientes
        WHERE id_registro_pendiente = v_id_registro_pendiente;
    END IF;

    RETURN NULL;
END;
$$
LANGUAGE PLPGSQL;

CREATE OR REPLACE TRIGGER tri_limpiar_registros_pendientes
AFTER UPDATE OF fecha_uso, fec_delete ON t_verificacion_correo
FOR EACH ROW
WHEN (NEW.fecha_uso IS NOT NULL OR NEW.fec_delete IS NOT NULL)
EXECUTE FUNCTION fun_limpiar_registros_pendientes();

-- t_recuperacion_contrasena: se borra físicamente al usar o expirar token
CREATE OR REPLACE FUNCTION fun_borrar_token_recuperacion()
RETURNS TRIGGER AS
$$
BEGIN
    DELETE FROM t_recuperacion_contrasena WHERE id_recuperacion = OLD.id_recuperacion;
    RETURN NULL;
END;
$$ LANGUAGE plpgsql;

CREATE OR REPLACE TRIGGER tri_borrar_token_recuperacion
AFTER UPDATE OF fecha_uso, fec_delete ON t_recuperacion_contrasena
FOR EACH ROW
WHEN (NEW.fecha_uso IS NOT NULL OR NEW.fec_delete IS NOT NULL)
EXECUTE FUNCTION fun_borrar_token_recuperacion();

-- Procedimiento opcional: limpiar registros pendientes expirados manualmente
CREATE OR REPLACE PROCEDURE sp_limpiar_registros_pendientes_expirados()
LANGUAGE SQL
AS $$
    DELETE FROM t_registros_pendientes
    WHERE fecha_expiracion < CURRENT_TIMESTAMP
       OR fec_delete IS NOT NULL;
$$;
