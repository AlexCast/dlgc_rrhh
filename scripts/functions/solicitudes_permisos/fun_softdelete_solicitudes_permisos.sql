CREATE OR REPLACE FUNCTION fun_softdelete_solicitudes_permisos(
    wid_permiso t_solicitudes_permisos.id_permiso%TYPE
) RETURNS BOOLEAN AS
$$
BEGIN
    UPDATE t_solicitudes_permisos
    SET fec_delete = CURRENT_TIMESTAMP,
        usr_delete = COALESCE(NULLIF(current_setting('app.current_user', true), ''), CURRENT_USER)
    WHERE id_permiso = wid_permiso
      AND fec_delete IS NULL;

    IF FOUND THEN
        RAISE NOTICE 'Solicitud % eliminada lógicamente.', wid_permiso;
        RETURN TRUE;
    END IF;

    RAISE NOTICE 'No se encontró solicitud activa con ID %.', wid_permiso;
    RETURN FALSE;
END;
$$
LANGUAGE PLPGSQL;