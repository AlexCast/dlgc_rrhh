CREATE OR REPLACE FUNCTION fun_insert_permisos_usuarios(
    wid_usuario t_usuarios_operaciones.id_usuario%TYPE,
    wid_operacion t_usuarios_operaciones.id_operacion%TYPE
) RETURNS VARCHAR AS
$$
BEGIN
    IF wid_usuario IS NULL OR TRIM(wid_usuario) = '' THEN
        RETURN 'El ID del usuario no es válido.';
    END IF;

    IF wid_operacion IS NULL OR wid_operacion <= 0 THEN
        RETURN 'El ID de la operación no es válido.';
    END IF;

    INSERT INTO t_usuarios_operaciones (
        id_usuario,
        id_operacion,
        usr_insert,
        fec_insert
    )
    VALUES (
        TRIM(wid_usuario),
        wid_operacion,
        COALESCE(NULLIF(current_setting('app.current_user', true), ''), CURRENT_USER),
        CURRENT_TIMESTAMP
    );

    RETURN 'Permiso individual insertado correctamente.';

EXCEPTION
    WHEN SQLSTATE '23505' THEN
        RETURN 'El permiso individual ya existe.';
    WHEN SQLSTATE '23503' THEN
        RETURN 'El usuario u operación indicado no existe.';
    WHEN OTHERS THEN
        RAISE NOTICE 'Error: %', SQLERRM;
        RETURN 'Ocurrió un error al insertar el permiso individual.';
END;
$$
LANGUAGE PLPGSQL;
