DELIMITER //

CREATE OR REPLACE FUNCTION fun_update_tipos_permisos(
    wid_tipo_permiso INT,
    wnombre_tipo VARCHAR(50),
    wdescuenta_tiempo BOOLEAN
) RETURNS VARCHAR(255)
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION RETURN 'Ocurrió un error al actualizar el tipo de permiso.';

    IF wid_tipo_permiso IS NULL OR wid_tipo_permiso <= 0 THEN
        RETURN 'El ID del tipo de permiso no es válido.';
    END IF;

    IF wnombre_tipo IS NULL OR LENGTH(TRIM(wnombre_tipo)) < 3 THEN
        RETURN 'El nombre del tipo de permiso debe tener al menos 3 caracteres.';
    END IF;

    UPDATE t_tipos_permisos
    SET nombre_tipo = TRIM(wnombre_tipo),
        descuenta_tiempo = COALESCE(wdescuenta_tiempo, FALSE),
        usr_update = COALESCE(@app_current_user, CURRENT_USER()),
        fec_update = CURRENT_TIMESTAMP
    WHERE id_tipo_permiso = wid_tipo_permiso
      AND fec_delete IS NULL;

    IF ROW_COUNT() > 0 THEN
        RETURN 'Tipo de permiso actualizado correctamente.';
    END IF;

    RETURN 'No se encontró el tipo de permiso activo para actualizar.';
END //

DELIMITER ;