CREATE OR REPLACE FUNCTION fun_softdelete_comunicados(
    wid_comunicado t_comunicados.id_comunicado%TYPE
) RETURNS BOOLEAN AS
$$
BEGIN
    UPDATE t_comunicados
    SET fec_delete = CURRENT_TIMESTAMP,
        usr_delete = COALESCE(NULLIF(current_setting('app.current_user', true), ''), CURRENT_USER)
    WHERE id_comunicado = wid_comunicado
      AND fec_delete IS NULL;

    IF FOUND THEN
        RAISE NOTICE 'Comunicado % eliminado lógicamente.', wid_comunicado;
        RETURN TRUE;
    END IF;

    RAISE NOTICE 'No se encontró comunicado activo con ID %.', wid_comunicado;
    RETURN FALSE;
END;
$$
LANGUAGE PLPGSQL;