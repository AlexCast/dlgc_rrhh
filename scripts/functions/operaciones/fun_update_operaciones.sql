DELIMITER //

CREATE OR REPLACE FUNCTION fun_update_operaciones(
    wid_operacion INT,
    wid_modulo INT,
    wnombre_operacion VARCHAR(50)
) RETURNS VARCHAR(255)
BEGIN
    DECLARE EXIT HANDLER FOR SQLSTATE '23000' RETURN 'El módulo indicado no existe o la operación ya existe.';
    DECLARE EXIT HANDLER FOR SQLEXCEPTION RETURN 'Ocurrió un error al actualizar la operación.';

    IF wid_operacion IS NULL OR wid_operacion <= 0 THEN
        RETURN 'El ID de la operación no es válido.';
    END IF;

    IF wid_modulo IS NULL OR wid_modulo <= 0 THEN
        RETURN 'El ID del módulo no es válido.';
    END IF;

    IF wnombre_operacion IS NULL OR LENGTH(TRIM(wnombre_operacion)) < 3 THEN
        RETURN 'El nombre de la operación debe tener al menos 3 caracteres.';
    END IF;

    UPDATE t_operaciones
    SET id_modulo = wid_modulo,
        nombre_operacion = TRIM(wnombre_operacion),
        usr_update = COALESCE(@app_current_user, CURRENT_USER()),
        fec_update = CURRENT_TIMESTAMP
    WHERE id_operacion = wid_operacion
      AND fec_delete IS NULL;

    IF ROW_COUNT() > 0 THEN
        RETURN 'Operación actualizada correctamente.';
    END IF;

    RETURN 'No se encontró la operación activa para actualizar.';
END //

DELIMITER ;