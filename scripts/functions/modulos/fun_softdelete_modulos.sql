CREATE OR REPLACE FUNCTION fun_softdelete_modulos(
    wid_modulo t_modulos.id_modulo%TYPE
) RETURNS BOOLEAN AS
$$
BEGIN
    UPDATE t_modulos
    SET fec_delete = CURRENT_TIMESTAMP,
        usr_delete = COALESCE(NULLIF(current_setting('app.current_user', true), ''), CURRENT_USER)
    WHERE id_modulo = wid_modulo
      AND fec_delete IS NULL;

    IF FOUND THEN
        RAISE NOTICE 'Módulo % eliminado lógicamente.', wid_modulo;
        RETURN TRUE;
    END IF;

    RAISE NOTICE 'No se encontró módulo activo con ID %.', wid_modulo;
    RETURN FALSE;
END;
$$
LANGUAGE PLPGSQL;