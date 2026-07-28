CREATE OR REPLACE FUNCTION fun_restore_nomina(wid_nomina t_nomina.id_nomina%TYPE)
RETURNS BOOLEAN AS
$$
BEGIN
    UPDATE t_nomina
    SET fec_delete = NULL, usr_delete = NULL
    WHERE id_nomina = wid_nomina AND fec_delete IS NOT NULL;

    IF FOUND THEN
        RAISE NOTICE 'Nómina % restaurada correctamente.', wid_nomina;
        RETURN TRUE;
    ELSE
        RAISE NOTICE 'No se encontró la nómina eliminada.';
        RETURN FALSE;
    END IF;
END;
$$ LANGUAGE plpgsql;
