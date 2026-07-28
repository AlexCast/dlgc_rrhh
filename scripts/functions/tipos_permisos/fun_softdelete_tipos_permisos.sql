CREATE OR REPLACE FUNCTION fun_softdelete_tipos_permisos(
    wid_tipo_permiso t_tipos_permisos.id_tipo_permiso%TYPE
) RETURNS BOOLEAN AS
$$
BEGIN
    UPDATE t_tipos_permisos
    SET fec_delete = CURRENT_TIMESTAMP,
        usr_delete = COALESCE(NULLIF(current_setting('app.current_user', true), ''), CURRENT_USER)
    WHERE id_tipo_permiso = wid_tipo_permiso
      AND fec_delete IS NULL;

    IF FOUND THEN
        RAISE NOTICE 'Tipo de permiso % eliminado lógicamente.', wid_tipo_permiso;
        RETURN TRUE;
    END IF;

    RAISE NOTICE 'No se encontró tipo de permiso activo con ID %.', wid_tipo_permiso;
    RETURN FALSE;
END;
$$
LANGUAGE PLPGSQL;