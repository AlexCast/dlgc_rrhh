DELIMITER //

CREATE OR REPLACE FUNCTION fun_update_permisos_dias_aprobados(
    wid_dia_permiso INT,
    wfecha DATE,
    whoras_aprobadas DECIMAL(4,2)
) RETURNS VARCHAR(255)
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION RETURN 'Ocurrió un error al actualizar el día de permiso aprobado o las horas no cumplen la regla.';

    IF wid_dia_permiso IS NULL OR wid_dia_permiso <= 0 THEN
        RETURN 'El ID del día de permiso no es válido.';
    END IF;

    IF wfecha IS NULL THEN
        RETURN 'La fecha es obligatoria.';
    END IF;

    IF whoras_aprobadas IS NULL OR whoras_aprobadas <= 0 OR whoras_aprobadas > 24 THEN
        RETURN 'Las horas aprobadas deben ser mayores que 0 y no superar 24.';
    END IF;

    UPDATE t_permisos_dias_aprobados
    SET fecha = wfecha,
        horas_aprobadas = whoras_aprobadas,
        usr_update = COALESCE(@app_current_user, CURRENT_USER()),
        fec_update = CURRENT_TIMESTAMP
    WHERE id_dia_permiso = wid_dia_permiso
      AND fec_delete IS NULL;

    IF ROW_COUNT() > 0 THEN
        RETURN 'Día de permiso aprobado actualizado correctamente.';
    END IF;

    RETURN 'No se encontró el día de permiso activo para actualizar.';
END //

DELIMITER ;