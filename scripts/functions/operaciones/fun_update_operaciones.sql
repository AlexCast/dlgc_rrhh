CREATE OR REPLACE FUNCTION fun_update_operaciones(
    wid_operacion t_operaciones.id_operacion%TYPE,
    wid_modulo t_operaciones.id_modulo%TYPE,
    wnombre_operacion t_operaciones.nombre_operacion%TYPE
) RETURNS VARCHAR AS
$$
BEGIN
    IF wid_operacion IS NULL OR wid_operacion <= 0 THEN
        RETURN 'El ID de la operación no es válido.';
    END IF;

    IF wid_modulo IS NULL OR wid_modulo <= 0 THEN
        RETURN 'El ID del módulo no es válido.';
    END IF;

    IF wnombre_operacion IS NULL OR LENGTH(TRIM(wnombre_operacion)) < 3 THEN
        RETURN 'El nombre de la operación debe tener al menos 3 caracteres.';
    END IF;

    UPDATE t_operaciones
    SET id_modulo = wid_modulo,
        nombre_operacion = TRIM(wnombre_operacion),
        usr_update = COALESCE(NULLIF(current_setting('app.current_user', true), ''), CURRENT_USER),
        fec_update = CURRENT_TIMESTAMP
    WHERE id_operacion = wid_operacion
      AND fec_delete IS NULL;

    IF FOUND THEN
        RETURN 'Operación actualizada correctamente.';
    END IF;

    RETURN 'No se encontró la operación activa para actualizar.';

EXCEPTION
    WHEN SQLSTATE '23503' THEN
        RETURN 'El módulo indicado no existe.';
    WHEN OTHERS THEN
        RAISE NOTICE 'Error: %', SQLERRM;
        RETURN 'Ocurrió un error al actualizar la operación.';
END;
$$
LANGUAGE PLPGSQL;