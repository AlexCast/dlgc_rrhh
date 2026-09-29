CREATE OR REPLACE FUNCTION fun_softdelete_banco(
    wid_banco t_bancos.id_banco%TYPE
) RETURNS BOOLEAN AS
$$
BEGIN
    UPDATE t_bancos
    SET fec_delete = CURRENT_TIMESTAMP,
        usr_delete = COALESCE(NULLIF(current_setting('app.current_user', true), ''), CURRENT_USER)
    WHERE id_banco = wid_banco
      AND fec_delete IS NULL;

    IF FOUND THEN
        RAISE NOTICE 'Banco % eliminado lógicamente.', wid_banco;
        RETURN TRUE;
    END IF;

    RAISE NOTICE 'No se encontró banco activo con ID %.', wid_banco;
    RETURN FALSE;
END;
$$
LANGUAGE PLPGSQL;