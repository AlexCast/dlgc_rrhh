CREATE OR REPLACE FUNCTION fun_insert_banco(
    wnombre_banco t_bancos.nombre_banco%TYPE
) RETURNS VARCHAR AS
$$
BEGIN
    IF wnombre_banco IS NULL OR LENGTH(TRIM(wnombre_banco)) < 3 THEN
        RETURN 'El nombre del banco debe tener al menos 3 caracteres.';
    END IF;

    INSERT INTO t_bancos (
        nombre_banco,
        usr_insert,
        fec_insert
    )
    VALUES (
        TRIM(wnombre_banco),
        COALESCE(NULLIF(current_setting('app.current_user', true), ''), CURRENT_USER),
        CURRENT_TIMESTAMP
    );

    RETURN 'Banco insertado correctamente.';

EXCEPTION
    WHEN SQLSTATE '23505' THEN
        RETURN 'El banco ya existe.';
    WHEN OTHERS THEN
        RAISE NOTICE 'Error: %', SQLERRM;
        RETURN 'Ocurrió un error al insertar el banco.';
END;
$$
LANGUAGE PLPGSQL;