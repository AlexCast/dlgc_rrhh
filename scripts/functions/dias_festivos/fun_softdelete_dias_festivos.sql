CREATE OR REPLACE FUNCTION fun_softdelete_dias_festivos(
    wid_festivo t_dias_festivos.id_festivo%TYPE
) RETURNS BOOLEAN AS
$$
BEGIN
    UPDATE t_dias_festivos
    SET fec_delete = CURRENT_TIMESTAMP,
        usr_delete = COALESCE(NULLIF(current_setting('app.current_user', true), ''), CURRENT_USER)
    WHERE id_festivo = wid_festivo
      AND tipo_festivo = 'EMPRESA'
      AND fec_delete IS NULL;

    IF FOUND THEN
        RAISE NOTICE 'Festivo % eliminado lógicamente.', wid_festivo;
        RETURN TRUE;
    END IF;

    RAISE NOTICE 'No se encontró festivo activo con ID %.', wid_festivo;
    RETURN FALSE;
END;
$$
LANGUAGE PLPGSQL;