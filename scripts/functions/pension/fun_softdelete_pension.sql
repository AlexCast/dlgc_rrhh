CREATE OR REPLACE FUNCTION fun_softdelete_pension(
    wid_pension t_pension.id_pension%TYPE
)
RETURNS BOOLEAN AS
$$
BEGIN
    UPDATE t_pension
    SET 
        fec_delete = CURRENT_TIMESTAMP,
        usr_delete = COALESCE(NULLIF(current_setting('app.current_user', true), ''), CURRENT_USER)
        WHERE id_pension = wid_pension
      AND fec_delete IS NULL;

    IF FOUND THEN
        RAISE NOTICE 'Entidad de Pensión % eliminada lógicamente.', wid_pension;
        RETURN TRUE;
    ELSE
        RAISE NOTICE 'No se encontró la entidad de Pensión o ya está eliminada.';
        RETURN FALSE;
    END IF;
END;
$$ LANGUAGE PLPGSQL;
