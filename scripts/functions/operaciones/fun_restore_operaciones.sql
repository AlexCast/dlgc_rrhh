CREATE OR REPLACE FUNCTION fun_restore_operaciones(
    wid_operacion t_operaciones.id_operacion%TYPE
) RETURNS BOOLEAN AS
$$
BEGIN
    UPDATE t_operaciones
    SET fec_delete = NULL,
        usr_delete = NULL
    WHERE id_operacion = wid_operacion
      AND fec_delete IS NOT NULL;

    IF FOUND THEN
        RAISE NOTICE 'Operación % restaurada correctamente.', wid_operacion;
        RETURN TRUE;
    END IF;

    RAISE NOTICE 'No se encontró operación eliminada con ID %.', wid_operacion;
    RETURN FALSE;
END;
$$
LANGUAGE PLPGSQL;