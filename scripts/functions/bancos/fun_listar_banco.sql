CREATE OR REPLACE FUNCTION fun_listar_banco(
    wid_banco t_bancos.id_banco%TYPE
) RETURNS BOOLEAN AS
$$
DECLARE
    wreg_banco t_bancos%ROWTYPE;
BEGIN
    IF wid_banco IS NULL OR wid_banco <= 0 THEN
        RAISE NOTICE 'El ID del banco no es válido.';
        RETURN FALSE;
    END IF;

    SELECT *
    INTO wreg_banco
    FROM t_bancos
    WHERE id_banco = wid_banco
      AND fec_delete IS NULL;

    IF NOT FOUND THEN
        RAISE NOTICE 'No existe banco activo con ID %.', wid_banco;
        RETURN FALSE;
    END IF;

    RAISE NOTICE 'ID: %, Banco: %', wreg_banco.id_banco, wreg_banco.nombre_banco;
    RETURN TRUE;

EXCEPTION
    WHEN OTHERS THEN
        RAISE NOTICE 'Error: %', SQLERRM;
        RETURN FALSE;
END;
$$
LANGUAGE PLPGSQL;