CREATE OR REPLACE FUNCTION fun_update_solicitudes_permisos_motivos(
    wid_permiso t_solicitudes_permisos_motivos.id_permiso%TYPE,
    wid_tipo_permiso t_solicitudes_permisos_motivos.id_tipo_permiso%TYPE,
    wdetalle_motivo t_solicitudes_permisos_motivos.detalle_motivo%TYPE
) RETURNS VARCHAR AS
$$
BEGIN
    IF wid_permiso IS NULL OR wid_permiso <= 0 OR wid_tipo_permiso IS NULL OR wid_tipo_permiso <= 0 THEN
        RETURN 'Los IDs de permiso y tipo de permiso deben ser válidos.';
    END IF;

    UPDATE t_solicitudes_permisos_motivos
    SET detalle_motivo = NULLIF(TRIM(COALESCE(wdetalle_motivo, '')), ''),
        usr_update = COALESCE(NULLIF(current_setting('app.current_user', true), ''), CURRENT_USER),
        fec_update = CURRENT_TIMESTAMP
    WHERE id_permiso = wid_permiso
      AND id_tipo_permiso = wid_tipo_permiso
      AND fec_delete IS NULL;

    IF FOUND THEN
        RETURN 'Motivo de solicitud actualizado correctamente.';
    END IF;

    RETURN 'No se encontró la relación activa para actualizar.';

EXCEPTION
    WHEN OTHERS THEN
        RAISE NOTICE 'Error: %', SQLERRM;
        RETURN 'Ocurrió un error al actualizar el motivo de solicitud.';
END;
$$
LANGUAGE PLPGSQL;