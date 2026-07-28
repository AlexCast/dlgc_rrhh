CREATE OR REPLACE FUNCTION fun_update_banco(
    wid_banco t_bancos.id_banco%TYPE,
    wnombre_banco t_bancos.nombre_banco%TYPE
) RETURNS VARCHAR AS
$$
BEGIN
    IF wid_banco IS NULL OR wid_banco <= 0 THEN
        RETURN 'El ID del banco no es válido.';
    END IF;

    IF wnombre_banco IS NULL OR LENGTH(TRIM(wnombre_banco)) < 3 THEN
        RETURN 'El nombre del banco debe tener al menos 3 caracteres.';
    END IF;

    UPDATE t_bancos
    SET nombre_banco = TRIM(wnombre_banco),
        usr_update = COALESCE(NULLIF(current_setting('app.current_user', true), ''), CURRENT_USER),
        fec_update = CURRENT_TIMESTAMP
    WHERE id_banco = wid_banco
      AND fec_delete IS NULL;

    IF FOUND THEN
        RETURN 'Banco actualizado correctamente.';
    END IF;

    RETURN 'No se encontró el banco activo para actualizar.';

EXCEPTION
    WHEN SQLSTATE '23505' THEN
        RETURN 'Ya existe un banco con ese nombre.';
    WHEN OTHERS THEN
        RAISE NOTICE 'Error: %', SQLERRM;
        RETURN 'Ocurrió un error al actualizar el banco.';
END;
$$
LANGUAGE PLPGSQL;