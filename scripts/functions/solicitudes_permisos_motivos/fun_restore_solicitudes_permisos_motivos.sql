CREATE OR REPLACE FUNCTION fun_restore_solicitudes_permisos_motivos(
    wid_permiso t_solicitudes_permisos_motivos.id_permiso%TYPE,
    wid_tipo_permiso t_solicitudes_permisos_motivos.id_tipo_permiso%TYPE
) RETURNS BOOLEAN AS
$$
BEGIN
    UPDATE t_solicitudes_permisos_motivos
    SET fec_delete = NULL,
        usr_delete = NULL
    WHERE id_permiso = wid_permiso
      AND id_tipo_permiso = wid_tipo_permiso
      AND fec_delete IS NOT NULL;

    IF FOUND THEN
        RAISE NOTICE 'Relación permiso % - tipo % restaurada correctamente.', wid_permiso, wid_tipo_permiso;
        RETURN TRUE;
    END IF;

    RAISE NOTICE 'No se encontró relación eliminada para restaurar.';
    RETURN FALSE;
END;
$$
LANGUAGE PLPGSQL;