CREATE OR REPLACE FUNCTION fun_insert_modulos(
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

    INSERT INTO t_modulos (
        id_modulo,
        nombre_modulo,
        usr_insert,
        fec_insert
    )
    VALUES (
        wid_modulo,
        TRIM(wnombre_modulo),
        COALESCE(NULLIF(current_setting('app.current_user', true), ''), CURRENT_USER),
        CURRENT_TIMESTAMP
    );

    RETURN 'Módulo insertado correctamente.';

EXCEPTION
    WHEN SQLSTATE '23505' THEN
        RETURN 'El módulo ya existe.';
    WHEN OTHERS THEN
        RAISE NOTICE 'Error: %', SQLERRM;
        RETURN 'Ocurrió un error al insertar el módulo.';
END;
$$
LANGUAGE PLPGSQL;