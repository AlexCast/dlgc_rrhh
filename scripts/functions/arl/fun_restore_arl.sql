CREATE OR REPLACE FUNCTION fun_restore_arl(wid_arl t_arl.id_arl%TYPE)
RETURNS BOOLEAN AS
$$
BEGIN
    UPDATE t_arl
    SET fec_delete = NULL, usr_delete = NULL
    WHERE id_arl = wid_arl AND fec_delete IS NOT NULL;

    IF FOUND THEN
        RAISE NOTICE 'ARL % restaurada correctamente.', wid_arl;
        RETURN TRUE;
    ELSE
        RAISE NOTICE 'No se encontró la ARL eliminada.';
        RETURN FALSE;
    END IF;
END;
$$ LANGUAGE plpgsql;
