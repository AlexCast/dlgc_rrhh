CREATE OR REPLACE FUNCTION fun_update_tipos_permisos(
    wid_tipo_permiso t_tipos_permisos.id_tipo_permiso%TYPE,
    wnombre_tipo t_tipos_permisos.nombre_tipo%TYPE,
    wdescuenta_tiempo t_tipos_permisos.descuenta_tiempo%TYPE
) RETURNS VARCHAR AS
$$
BEGIN
    IF wid_tipo_permiso IS NULL OR wid_tipo_permiso <= 0 THEN
        RETURN 'El ID del tipo de permiso no es válido.';
    END IF;

    IF wnombre_tipo IS NULL OR LENGTH(TRIM(wnombre_tipo)) < 3 THEN
        RETURN 'El nombre del tipo de permiso debe tener al menos 3 caracteres.';
    END IF;

    UPDATE t_tipos_permisos
    SET nombre_tipo = TRIM(wnombre_tipo),
        descuenta_tiempo = COALESCE(wdescuenta_tiempo, FALSE),
        usr_update = COALESCE(NULLIF(current_setting('app.current_user', true), ''), CURRENT_USER),
        fec_update = CURRENT_TIMESTAMP
    WHERE id_tipo_permiso = wid_tipo_permiso
      AND fec_delete IS NULL;

    IF FOUND THEN
        RETURN 'Tipo de permiso actualizado correctamente.';
    END IF;

    RETURN 'No se encontró el tipo de permiso activo para actualizar.';

EXCEPTION
    WHEN OTHERS THEN
        RAISE NOTICE 'Error: %', SQLERRM;
        RETURN 'Ocurrió un error al actualizar el tipo de permiso.';
END;
$$
LANGUAGE PLPGSQL;