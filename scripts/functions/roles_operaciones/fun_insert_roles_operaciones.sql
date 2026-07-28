CREATE OR REPLACE FUNCTION fun_insert_roles_operaciones(
    wid_rol t_roles_operaciones.id_rol%TYPE,
    wid_operacion t_roles_operaciones.id_operacion%TYPE
) RETURNS VARCHAR AS
$$
BEGIN
    IF wid_rol IS NULL OR wid_rol <= 0 THEN
        RETURN 'El ID del rol no es válido.';
    END IF;

    IF wid_operacion IS NULL OR wid_operacion <= 0 THEN
        RETURN 'El ID de la operación no es válido.';
    END IF;

    INSERT INTO t_roles_operaciones (
        id_rol,
        id_operacion,
        usr_insert,
        fec_insert
    )
    VALUES (
        wid_rol,
        wid_operacion,
        COALESCE(NULLIF(current_setting('app.current_user', true), ''), CURRENT_USER),
        CURRENT_TIMESTAMP
    );

    RETURN 'Asignación rol-operación insertada correctamente.';

EXCEPTION
    WHEN SQLSTATE '23505' THEN
        RETURN 'La asignación rol-operación ya existe.';
    WHEN SQLSTATE '23503' THEN
        RETURN 'El rol u operación indicado no existe.';
    WHEN OTHERS THEN
        RAISE NOTICE 'Error: %', SQLERRM;
        RETURN 'Ocurrió un error al insertar la asignación rol-operación.';
END;
$$
LANGUAGE PLPGSQL;