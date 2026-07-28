CREATE OR REPLACE FUNCTION fun_softdelete_eps(
    wid_eps t_eps.id_eps%TYPE
)
RETURNS BOOLEAN AS
$$
BEGIN
    UPDATE t_eps
    SET 
        fec_delete = CURRENT_TIMESTAMP,
        usr_delete = COALESCE(NULLIF(current_setting('app.current_user', true), ''), CURRENT_USER)
        WHERE id_eps = wid_eps
      AND fec_delete IS NULL;

    IF FOUND THEN
        RAISE NOTICE 'EPS % eliminada lógicamente.', wid_eps;
        RETURN TRUE;
    ELSE
        RAISE NOTICE 'No se encontró la EPS o ya está eliminada.';
        RETURN FALSE;
    END IF;
END;
$$ LANGUAGE PLPGSQL;
