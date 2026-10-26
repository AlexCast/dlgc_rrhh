DELIMITER //

CREATE OR REPLACE FUNCTION fun_update_tipos_permisos(
    wid_tipo_permiso INT,
    wnombre_tipo VARCHAR(50),
    wdescuenta_tiempo BOOLEAN,
    wporcentaje_descuento DECIMAL(5,2),
    wrequiere_evidencia BOOLEAN
) RETURNS VARCHAR(255)
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION RETURN 'Ocurrió un error al actualizar el tipo de permiso (verifica el porcentaje de descuento).';

    IF wid_tipo_permiso IS NULL OR wid_tipo_permiso <= 0 THEN
        RETURN 'El ID del tipo de permiso no es válido.';
    END IF;

    IF wnombre_tipo IS NULL OR LENGTH(TRIM(wnombre_tipo)) < 3 THEN
        RETURN 'El nombre del tipo de permiso debe tener al menos 3 caracteres.';
    END IF;

    IF COALESCE(wdescuenta_tiempo, FALSE) = TRUE AND wporcentaje_descuento IS NULL THEN
        RETURN 'El porcentaje de descuento es obligatorio cuando el tipo descuenta tiempo.';
    END IF;

    IF COALESCE(wdescuenta_tiempo, FALSE) = FALSE AND wporcentaje_descuento IS NOT NULL THEN
        RETURN 'No se debe indicar porcentaje de descuento cuando el tipo no descuenta tiempo.';
    END IF;

    IF wporcentaje_descuento IS NOT NULL AND (wporcentaje_descuento < 0 OR wporcentaje_descuento > 100) THEN
        RETURN 'El porcentaje de descuento debe estar entre 0 y 100.';
    END IF;

    UPDATE t_tipos_permisos
    SET nombre_tipo = TRIM(wnombre_tipo),
        descuenta_tiempo = COALESCE(wdescuenta_tiempo, FALSE),
        porcentaje_descuento = wporcentaje_descuento,
        requiere_evidencia = COALESCE(wrequiere_evidencia, FALSE),
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