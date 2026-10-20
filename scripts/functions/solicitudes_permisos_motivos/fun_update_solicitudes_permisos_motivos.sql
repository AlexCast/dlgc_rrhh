DELIMITER //

CREATE OR REPLACE FUNCTION fun_update_solicitudes_permisos_motivos(
    wid_permiso INT,
    wid_tipo_permiso INT,
    wdetalle_motivo VARCHAR(255)
) RETURNS VARCHAR(255)
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION RETURN 'Ocurrió un error al actualizar el motivo de solicitud.';

    IF wid_permiso IS NULL OR wid_permiso <= 0 OR wid_tipo_permiso IS NULL OR wid_tipo_permiso <= 0 THEN
        RETURN 'Los IDs de permiso y tipo de permiso deben ser válidos.';
    END IF;

    UPDATE t_solicitudes_permisos_motivos
    SET detalle_motivo = NULLIF(TRIM(COALESCE(wdetalle_motivo, '')), ''),
        usr_update = COALESCE(@app_current_user, CURRENT_USER()),
        fec_update = CURRENT_TIMESTAMP
    WHERE id_permiso = wid_permiso
      AND id_tipo_permiso = wid_tipo_permiso
      AND fec_delete IS NULL;

    IF ROW_COUNT() > 0 THEN
        RETURN 'Motivo de solicitud actualizado correctamente.';
    END IF;

    RETURN 'No se encontró la relación activa para actualizar.';
END //

DELIMITER ;