DELIMITER //

CREATE OR REPLACE FUNCTION fun_insert_tipos_permisos(
    wnombre_tipo VARCHAR(50),
    wdescuenta_tiempo BOOLEAN,
    wporcentaje_descuento DECIMAL(5,2),
    wrequiere_evidencia BOOLEAN
) RETURNS VARCHAR(255)
BEGIN
    DECLARE EXIT HANDLER FOR SQLSTATE '23000' RETURN 'Ya existe un tipo de permiso con ese nombre.';
    DECLARE EXIT HANDLER FOR SQLEXCEPTION RETURN 'Ocurrió un error al insertar el tipo de permiso (verifica el porcentaje de descuento).';

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

    INSERT INTO t_tipos_permisos (
        nombre_tipo,
        descuenta_tiempo,
        porcentaje_descuento,
        requiere_evidencia,
        usr_insert,
        fec_insert
    )
    VALUES (
        TRIM(wnombre_tipo),
        COALESCE(wdescuenta_tiempo, FALSE),
        wporcentaje_descuento,
        COALESCE(wrequiere_evidencia, FALSE),
        COALESCE(@app_current_user, CURRENT_USER()),
        CURRENT_TIMESTAMP
    );

    RETURN 'Tipo de permiso insertado correctamente.';
END //

DELIMITER ;