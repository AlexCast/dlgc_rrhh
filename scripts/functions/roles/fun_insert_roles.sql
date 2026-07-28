CREATE OR REPLACE FUNCTION fun_insert_roles(
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

    INSERT INTO t_roles (
        id_rol,
        nombre_rol,
        usr_insert,
        fec_insert
    )
    VALUES (
        wid_rol,
        TRIM(wnombre_rol),
        COALESCE(NULLIF(current_setting('app.current_user', true), ''), CURRENT_USER),
        CURRENT_TIMESTAMP
    );

    RETURN 'Rol insertado correctamente.';

EXCEPTION
    WHEN SQLSTATE '23505' THEN
        RETURN 'El rol ya existe.';
    WHEN OTHERS THEN
        RAISE NOTICE 'Error: %', SQLERRM;
        RETURN 'Ocurrió un error al insertar el rol.';
END;
$$
LANGUAGE PLPGSQL;