CREATE OR REPLACE FUNCTION fun_restore_area(wid_area t_areas.id_area%TYPE)
RETURNS BOOLEAN AS
$$
BEGIN
    UPDATE t_areas
    SET fec_delete = NULL, usr_delete = NULL
    WHERE id_area = wid_area AND fec_delete IS NOT NULL;

    IF FOUND THEN
        RAISE NOTICE 'Area % restaurada correctamente.', wid_area;
        RETURN TRUE;
    ELSE
        RAISE NOTICE 'No se encontro el area eliminada.';
        RETURN FALSE;
    END IF;
END;
$$ LANGUAGE plpgsql;