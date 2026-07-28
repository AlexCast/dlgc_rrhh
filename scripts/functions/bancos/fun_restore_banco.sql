CREATE OR REPLACE FUNCTION fun_restore_banco(
    wid_banco t_bancos.id_banco%TYPE
) RETURNS BOOLEAN AS
$$
BEGIN
    UPDATE t_bancos
    SET fec_delete = NULL,
        usr_delete = NULL
    WHERE id_banco = wid_banco
      AND fec_delete IS NOT NULL;

    IF FOUND THEN
        RAISE NOTICE 'Banco % restaurado correctamente.', wid_banco;
        RETURN TRUE;
    END IF;

    RAISE NOTICE 'No se encontró banco eliminado con ID %.', wid_banco;
    RETURN FALSE;
END;
$$
LANGUAGE PLPGSQL;