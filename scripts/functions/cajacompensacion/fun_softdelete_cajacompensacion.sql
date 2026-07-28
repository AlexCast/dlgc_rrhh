CREATE OR REPLACE FUNCTION fun_softdelete_caja(
    wid_caja   t_caja_compensacion.id_caja%TYPE
)
RETURNS BOOLEAN AS
$$
BEGIN
    UPDATE t_caja_compensacion
    SET 
        fec_delete = CURRENT_TIMESTAMP,
        usr_delete = COALESCE(NULLIF(current_setting('app.current_user', true), ''), CURRENT_USER)
        WHERE id_caja = wid_caja
      AND fec_delete IS NULL;

    IF FOUND THEN
        RAISE NOTICE 'Caja de Compensación % eliminada lógicamente.', wid_caja;
        RETURN TRUE;
    ELSE
        RAISE NOTICE 'No se encontró la Caja de Compensación o ya está eliminada.';
        RETURN FALSE;
    END IF;
END;
$$ LANGUAGE PLPGSQL;
