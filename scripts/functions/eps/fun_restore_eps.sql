CREATE OR REPLACE FUNCTION fun_restore_eps(wid_eps t_eps.id_eps%TYPE)
RETURNS BOOLEAN AS
$$
BEGIN
    UPDATE t_eps
    SET fec_delete = NULL, usr_delete = NULL
    WHERE id_eps = wid_eps AND fec_delete IS NOT NULL;

    IF FOUND THEN
        RAISE NOTICE 'EPS % restaurada correctamente.', wid_eps;
        RETURN TRUE;
    ELSE
        RAISE NOTICE 'No se encontró la EPS eliminada.';
        RETURN FALSE;
    END IF;
END;
$$ LANGUAGE plpgsql;
