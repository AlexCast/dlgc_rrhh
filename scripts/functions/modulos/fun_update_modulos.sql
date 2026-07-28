CREATE OR REPLACE FUNCTION fun_update_modulos(
    wid_modulo t_modulos.id_modulo%TYPE,
    wnombre_modulo t_modulos.nombre_modulo%TYPE
) RETURNS VARCHAR AS
$$
BEGIN
    IF wid_modulo IS NULL OR wid_modulo <= 0 THEN
        RETURN 'El ID del módulo no es válido.';
    END IF;

    IF wnombre_modulo IS NULL OR LENGTH(TRIM(wnombre_modulo)) < 3 THEN
        RETURN 'El nombre del módulo debe tener al menos 3 caracteres.';
    END IF;

    UPDATE t_modulos
    SET nombre_modulo = TRIM(wnombre_modulo),
        usr_update = COALESCE(NULLIF(current_setting('app.current_user', true), ''), CURRENT_USER),
        fec_update = CURRENT_TIMESTAMP
    WHERE id_modulo = wid_modulo
      AND fec_delete IS NULL;

    IF FOUND THEN
        RETURN 'Módulo actualizado correctamente.';
    END IF;

    RETURN 'No se encontró el módulo activo para actualizar.';

EXCEPTION
    WHEN SQLSTATE '23505' THEN
        RETURN 'Ya existe un módulo con ese nombre.';
    WHEN OTHERS THEN
        RAISE NOTICE 'Error: %', SQLERRM;
        RETURN 'Ocurrió un error al actualizar el módulo.';
END;
$$
LANGUAGE PLPGSQL;