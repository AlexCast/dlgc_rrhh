CREATE OR REPLACE FUNCTION fun_restore_caja(wid_caja t_caja_compensacion.id_caja%TYPE)
RETURNS BOOLEAN AS
$$
BEGIN
    UPDATE t_caja_compensacion
    SET fec_delete = NULL, usr_delete = NULL
    WHERE id_caja = wid_caja AND fec_delete IS NOT NULL;

    IF FOUND THEN
        RAISE NOTICE 'Caja de Compensación % restaurada correctamente.', wid_caja;
        RETURN TRUE;
    ELSE
        RAISE NOTICE 'No se encontró la Caja de Compensación eliminada.';
        RETURN FALSE;
    END IF;
END;
$$ LANGUAGE plpgsql;
