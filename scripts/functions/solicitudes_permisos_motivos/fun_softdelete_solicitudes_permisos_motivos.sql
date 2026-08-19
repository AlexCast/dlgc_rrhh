CREATE OR REPLACE FUNCTION fun_softdelete_solicitudes_permisos_motivos(
    wid_permiso t_solicitudes_permisos_motivos.id_permiso%TYPE,
    wid_tipo_permiso t_solicitudes_permisos_motivos.id_tipo_permiso%TYPE
) RETURNS BOOLEAN AS
$$
BEGIN
    UPDATE t_solicitudes_permisos_motivos
    SET fec_delete = CURRENT_TIMESTAMP,
        usr_delete = COALESCE(NULLIF(current_setting('app.current_user', true), ''), CURRENT_USER)
    WHERE id_permiso = wid_permiso
      AND id_tipo_permiso = wid_tipo_permiso
      AND fec_delete IS NULL;

    IF FOUND THEN
        RAISE NOTICE 'Relación permiso % - tipo % eliminada lógicamente.', wid_permiso, wid_tipo_permiso;
        RETURN TRUE;
    END IF;

    RAISE NOTICE 'No se encontró relación activa para eliminar.';
    RETURN FALSE;
END;
$$
LANGUAGE PLPGSQL;