CREATE OR REPLACE FUNCTION fun_softdelete_area(
    wid_area t_areas.id_area%TYPE
)
RETURNS BOOLEAN AS
$$
BEGIN
    UPDATE t_areas
    SET 
        fec_delete = CURRENT_TIMESTAMP,
        usr_delete = COALESCE(NULLIF(current_setting('app.current_user', true), ''), CURRENT_USER)
        WHERE id_area = wid_area
      AND fec_delete IS NULL;

    IF FOUND THEN
        RAISE NOTICE 'Área % eliminada lógicamente.', wid_area;
        RETURN TRUE;
    ELSE
        RAISE NOTICE 'No se encontró el área o ya está eliminada.';
        RETURN FALSE;
    END IF;
END;
$$ LANGUAGE PLPGSQL;
