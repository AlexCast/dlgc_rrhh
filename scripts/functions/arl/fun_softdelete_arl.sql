CREATE OR REPLACE FUNCTION fun_softdelete_arl(
    wid_arl t_arl.id_arl%TYPE
)
RETURNS BOOLEAN AS
$$
BEGIN
    UPDATE t_arl
    SET 
        fec_delete = CURRENT_TIMESTAMP,
        usr_delete = COALESCE(NULLIF(current_setting('app.current_user', true), ''), CURRENT_USER)
        WHERE id_arl = wid_arl
      AND fec_delete IS NULL;

    IF FOUND THEN
        RAISE NOTICE 'ARL % eliminada lógicamente.', wid_arl;
        RETURN TRUE;
    ELSE
        RAISE NOTICE 'No se encontró la ARL o ya está eliminada.';
        RETURN FALSE;
    END IF;
END;
$$ LANGUAGE PLPGSQL;
