CREATE OR REPLACE FUNCTION fun_restore_solicitudes_permisos(
    wid_permiso t_solicitudes_permisos.id_permiso%TYPE
) RETURNS BOOLEAN AS
$$
BEGIN
    UPDATE t_solicitudes_permisos
    SET fec_delete = NULL,
        usr_delete = NULL
    WHERE id_permiso = wid_permiso
      AND fec_delete IS NOT NULL;

    IF FOUND THEN
        RAISE NOTICE 'Solicitud % restaurada correctamente.', wid_permiso;
        RETURN TRUE;
    END IF;

    RAISE NOTICE 'No se encontró solicitud eliminada con ID %.', wid_permiso;
    RETURN FALSE;
END;
$$
LANGUAGE PLPGSQL;