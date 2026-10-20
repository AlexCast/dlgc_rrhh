DELIMITER //

CREATE OR REPLACE FUNCTION fun_update_dias_festivos(
    wid_festivo INT,
    wfecha DATE,
    wdescripcion VARCHAR(100),
    wdescuenta_salario BOOLEAN
) RETURNS VARCHAR(255)
BEGIN
    DECLARE EXIT HANDLER FOR SQLSTATE '23000' RETURN 'Ya existe otro festivo con esa fecha.';
    DECLARE EXIT HANDLER FOR SQLEXCEPTION RETURN 'Ocurrió un error al actualizar el día festivo.';

    IF wid_festivo IS NULL OR wid_festivo <= 0 THEN
        RETURN 'El ID del festivo no es válido.';
    END IF;

    IF wfecha IS NULL THEN
        RETURN 'La fecha del festivo es obligatoria.';
    END IF;

    IF wdescripcion IS NULL OR LENGTH(TRIM(wdescripcion)) < 3 THEN
        RETURN 'La descripción del festivo debe tener al menos 3 caracteres.';
    END IF;

    -- Solo se pueden editar festivos de EMPRESA desde aquí; los NACIONAL son de solo lectura.
    UPDATE t_dias_festivos
    SET fecha = wfecha,
        descripcion = TRIM(wdescripcion),
        descuenta_salario = COALESCE(wdescuenta_salario, TRUE),
        usr_update = COALESCE(@app_current_user, CURRENT_USER()),
        fec_update = CURRENT_TIMESTAMP
    WHERE id_festivo = wid_festivo
      AND tipo_festivo = 'EMPRESA'
      AND fec_delete IS NULL;

    IF ROW_COUNT() > 0 THEN
        RETURN 'Día festivo actualizado correctamente.';
    END IF;

    RETURN 'No se encontró el festivo de empresa activo para actualizar.';
END //

DELIMITER ;