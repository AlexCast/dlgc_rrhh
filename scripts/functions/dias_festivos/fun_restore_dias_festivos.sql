CREATE OR REPLACE FUNCTION fun_restore_dias_festivos(
    wid_festivo t_dias_festivos.id_festivo%TYPE
) RETURNS BOOLEAN AS
$$
BEGIN
    UPDATE t_dias_festivos
    SET fec_delete = NULL,
        usr_delete = NULL
    WHERE id_festivo = wid_festivo
      AND fec_delete IS NOT NULL;

    IF FOUND THEN
        RAISE NOTICE 'Festivo % restaurado correctamente.', wid_festivo;
        RETURN TRUE;
    END IF;

    RAISE NOTICE 'No se encontró festivo eliminado con ID %.', wid_festivo;
    RETURN FALSE;
END;
$$
LANGUAGE PLPGSQL;