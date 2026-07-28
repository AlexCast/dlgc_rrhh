CREATE OR REPLACE FUNCTION fun_insert_operaciones(
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

    INSERT INTO t_operaciones (
        id_operacion,
        id_modulo,
        nombre_operacion,
        usr_insert,
        fec_insert
    )
    VALUES (
        wid_operacion,
        wid_modulo,
        TRIM(wnombre_operacion),
        COALESCE(NULLIF(current_setting('app.current_user', true), ''), CURRENT_USER),
        CURRENT_TIMESTAMP
    );

    RETURN 'Operación insertada correctamente.';

EXCEPTION
    WHEN SQLSTATE '23505' THEN
        RETURN 'La operación ya existe.';
    WHEN SQLSTATE '23503' THEN
        RETURN 'El módulo indicado no existe.';
    WHEN OTHERS THEN
        RAISE NOTICE 'Error: %', SQLERRM;
        RETURN 'Ocurrió un error al insertar la operación.';
END;
$$
LANGUAGE PLPGSQL;