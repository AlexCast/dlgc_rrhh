CREATE OR REPLACE FUNCTION fun_softdelete_nomina(
    wid_nomina t_nomina.id_nomina%TYPE
)
RETURNS BOOLEAN AS
$$
BEGIN
    UPDATE t_nomina
    SET 
        fec_delete = CURRENT_TIMESTAMP,
        usr_delete = COALESCE(NULLIF(current_setting('app.current_user', true), ''), CURRENT_USER)
        WHERE id_nomina = wid_nomina
      AND fec_delete IS NULL;

    IF FOUND THEN
        RAISE NOTICE 'Nómina % eliminada lógicamente.', wid_nomina;
        RETURN TRUE;
    ELSE
        RAISE NOTICE 'No se encontró la nómina o ya está eliminada.';
        RETURN FALSE;
    END IF;
END;
$$ LANGUAGE PLPGSQL;
