CREATE OR REPLACE FUNCTION fun_restore_pension(wid_pension t_pension.id_pension%TYPE)
RETURNS BOOLEAN AS
$$
BEGIN
    UPDATE t_pension
    SET fec_delete = NULL, usr_delete = NULL
    WHERE id_pension = wid_pension AND fec_delete IS NOT NULL;

    IF FOUND THEN
        RAISE NOTICE 'Entidad de Pensión % restaurada correctamente.', wid_pension;
        RETURN TRUE;
    ELSE
        RAISE NOTICE 'No se encontró la entidad de Pensión eliminada.';
        RETURN FALSE;
    END IF;
END;
$$ LANGUAGE plpgsql;
