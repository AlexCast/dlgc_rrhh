CREATE OR REPLACE FUNCTION fun_softdelete_operaciones(
    wid_operacion t_operaciones.id_operacion%TYPE
) RETURNS BOOLEAN AS
$$
BEGIN
    UPDATE t_operaciones
    SET fec_delete = CURRENT_TIMESTAMP,
        usr_delete = COALESCE(NULLIF(current_setting('app.current_user', true), ''), CURRENT_USER)
    WHERE id_operacion = wid_operacion
      AND fec_delete IS NULL;

    IF FOUND THEN
        RAISE NOTICE 'Operación % eliminada lógicamente.', wid_operacion;
        RETURN TRUE;
    END IF;

    RAISE NOTICE 'No se encontró operación activa con ID %.', wid_operacion;
    RETURN FALSE;
END;
$$
LANGUAGE PLPGSQL;