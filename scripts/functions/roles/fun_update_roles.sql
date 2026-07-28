CREATE OR REPLACE FUNCTION fun_update_roles(
    wid_rol t_roles.id_rol%TYPE,
    wnombre_rol t_roles.nombre_rol%TYPE
) RETURNS VARCHAR AS
$$
BEGIN
    IF wid_rol IS NULL OR wid_rol <= 0 THEN
        RETURN 'El ID del rol no es válido.';
    END IF;

    IF wnombre_rol IS NULL OR LENGTH(TRIM(wnombre_rol)) < 3 THEN
        RETURN 'El nombre del rol debe tener al menos 3 caracteres.';
    END IF;

    UPDATE t_roles
    SET nombre_rol = TRIM(wnombre_rol),
        usr_update = COALESCE(NULLIF(current_setting('app.current_user', true), ''), CURRENT_USER),
        fec_update = CURRENT_TIMESTAMP
    WHERE id_rol = wid_rol
      AND fec_delete IS NULL;

    IF FOUND THEN
        RETURN 'Rol actualizado correctamente.';
    END IF;

    RETURN 'No se encontró el rol activo para actualizar.';

EXCEPTION
    WHEN SQLSTATE '23505' THEN
        RETURN 'Ya existe un rol con ese nombre.';
    WHEN OTHERS THEN
        RAISE NOTICE 'Error: %', SQLERRM;
        RETURN 'Ocurrió un error al actualizar el rol.';
END;
$$
LANGUAGE PLPGSQL;