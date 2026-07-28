CREATE OR REPLACE FUNCTION fun_restore_cesantias(wid_cesantia t_cesantias.id_cesantia%TYPE)
RETURNS BOOLEAN AS
$$
BEGIN
    UPDATE t_cesantias
    SET fec_delete = NULL, usr_delete = NULL
    WHERE id_cesantia = wid_cesantia AND fec_delete IS NOT NULL;

    IF FOUND THEN
        RAISE NOTICE 'Entidad de Cesantías % restaurada correctamente.', wid_cesantia;
        RETURN TRUE;
    ELSE
        RAISE NOTICE 'No se encontró la entidad de Cesantías eliminada.';
        RETURN FALSE;
    END IF;
END;
$$ LANGUAGE plpgsql;
