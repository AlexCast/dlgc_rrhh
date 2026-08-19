CREATE OR REPLACE FUNCTION fun_restore_modulos(
    wid_modulo t_modulos.id_modulo%TYPE
) RETURNS BOOLEAN AS
$$
BEGIN
    UPDATE t_modulos
    SET fec_delete = NULL,
        usr_delete = NULL
    WHERE id_modulo = wid_modulo
      AND fec_delete IS NOT NULL;

    IF FOUND THEN
        RAISE NOTICE 'Módulo % restaurado correctamente.', wid_modulo;
        RETURN TRUE;
    END IF;

    RAISE NOTICE 'No se encontró módulo eliminado con ID %.', wid_modulo;
    RETURN FALSE;
END;
$$
LANGUAGE PLPGSQL;