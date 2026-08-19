CREATE OR REPLACE FUNCTION fun_softdelete_cesantias(
    wid_cesantia t_cesantias.id_cesantia%TYPE
)
RETURNS BOOLEAN AS
$$
BEGIN
    UPDATE t_cesantias
    SET 
        fec_delete = CURRENT_TIMESTAMP,
        usr_delete = COALESCE(NULLIF(current_setting('app.current_user', true), ''), CURRENT_USER)
        WHERE id_cesantia = wid_cesantia
      AND fec_delete IS NULL;

    IF FOUND THEN
        RAISE NOTICE 'Entidad de Cesantías % eliminada lógicamente.', wid_cesantia;
        RETURN TRUE;
    ELSE
        RAISE NOTICE 'No se encontró la entidad de Cesantías o ya está eliminada.';
        RETURN FALSE;
    END IF;
END;
$$ LANGUAGE PLPGSQL;
