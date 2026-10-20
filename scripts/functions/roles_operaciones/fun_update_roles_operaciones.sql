DELIMITER //

CREATE OR REPLACE FUNCTION fun_update_roles_operaciones(
    wid_rol_actual INT,
    wid_operacion_actual INT,
    wid_rol_nuevo INT,
    wid_operacion_nueva INT
) RETURNS VARCHAR(255)
BEGIN
    DECLARE EXIT HANDLER FOR SQLSTATE '23000' RETURN 'La nueva asignación ya existe o el destino no existe.';
    DECLARE EXIT HANDLER FOR SQLEXCEPTION RETURN 'Ocurrió un error al actualizar la asignación rol-operación.';

    IF wid_rol_actual IS NULL OR wid_rol_actual <= 0 OR wid_operacion_actual IS NULL OR wid_operacion_actual <= 0 THEN
        RETURN 'La llave actual de la asignación no es válida.';
    END IF;

    IF wid_rol_nuevo IS NULL OR wid_rol_nuevo <= 0 OR wid_operacion_nueva IS NULL OR wid_operacion_nueva <= 0 THEN
        RETURN 'La nueva llave de la asignación no es válida.';
    END IF;

    UPDATE t_roles_operaciones
    SET id_rol = wid_rol_nuevo,
        id_operacion = wid_operacion_nueva,
        usr_update = COALESCE(@app_current_user, CURRENT_USER()),
        fec_update = CURRENT_TIMESTAMP
    WHERE id_rol = wid_rol_actual
      AND id_operacion = wid_operacion_actual
      AND fec_delete IS NULL;

    IF ROW_COUNT() > 0 THEN
        RETURN 'Asignación rol-operación actualizada correctamente.';
    END IF;

    RETURN 'No se encontró la asignación activa para actualizar.';
END //

DELIMITER ;
