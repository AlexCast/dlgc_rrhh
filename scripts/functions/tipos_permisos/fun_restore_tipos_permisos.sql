CREATE OR REPLACE FUNCTION fun_restore_tipos_permisos(
    wid_tipo_permiso t_tipos_permisos.id_tipo_permiso%TYPE
) RETURNS BOOLEAN AS
$$
BEGIN
    UPDATE t_tipos_permisos
    SET fec_delete = NULL,
        usr_delete = NULL
    WHERE id_tipo_permiso = wid_tipo_permiso
      AND fec_delete IS NOT NULL;

    IF FOUND THEN
        RAISE NOTICE 'Tipo de permiso % restaurado correctamente.', wid_tipo_permiso;
        RETURN TRUE;
    END IF;

    RAISE NOTICE 'No se encontró tipo de permiso eliminado con ID %.', wid_tipo_permiso;
    RETURN FALSE;
END;
$$
LANGUAGE PLPGSQL;