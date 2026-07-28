CREATE OR REPLACE FUNCTION fun_restore_comunicados(
    wid_comunicado t_comunicados.id_comunicado%TYPE
) RETURNS BOOLEAN AS
$$
BEGIN
    UPDATE t_comunicados
    SET fec_delete = NULL,
        usr_delete = NULL
    WHERE id_comunicado = wid_comunicado
      AND fec_delete IS NOT NULL;

    IF FOUND THEN
        RAISE NOTICE 'Comunicado % restaurado correctamente.', wid_comunicado;
        RETURN TRUE;
    END IF;

    RAISE NOTICE 'No se encontró comunicado eliminado con ID %.', wid_comunicado;
    RETURN FALSE;
END;
$$
LANGUAGE PLPGSQL;