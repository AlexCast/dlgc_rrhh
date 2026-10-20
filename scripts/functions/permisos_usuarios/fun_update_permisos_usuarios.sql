DELIMITER //

CREATE OR REPLACE FUNCTION fun_update_permisos_usuarios(
    wid_usuario_actual VARCHAR(20),
    wid_operacion_actual INT,
    wid_usuario_nuevo VARCHAR(20),
    wid_operacion_nueva INT
) RETURNS VARCHAR(255)
BEGIN
    DECLARE EXIT HANDLER FOR SQLSTATE '23000' RETURN 'El nuevo permiso ya existe o el destino no existe.';
    DECLARE EXIT HANDLER FOR SQLEXCEPTION RETURN 'Ocurrió un error al actualizar el permiso individual.';

    IF wid_usuario_actual IS NULL OR TRIM(wid_usuario_actual) = '' OR wid_operacion_actual IS NULL OR wid_operacion_actual <= 0 THEN
        RETURN 'La llave actual del permiso no es válida.';
    END IF;

    IF wid_usuario_nuevo IS NULL OR TRIM(wid_usuario_nuevo) = '' OR wid_operacion_nueva IS NULL OR wid_operacion_nueva <= 0 THEN
        RETURN 'La nueva llave del permiso no es válida.';
    END IF;

    UPDATE t_usuarios_operaciones
    SET id_usuario = TRIM(wid_usuario_nuevo),
        id_operacion = wid_operacion_nueva,
        usr_update = COALESCE(@app_current_user, CURRENT_USER()),
        fec_update = CURRENT_TIMESTAMP
    WHERE id_usuario = TRIM(wid_usuario_actual)
      AND id_operacion = wid_operacion_actual
      AND fec_delete IS NULL;

    IF ROW_COUNT() > 0 THEN
        RETURN 'Permiso individual actualizado correctamente.';
    END IF;

    RETURN 'No se encontró el permiso activo para actualizar.';
END //

DELIMITER ;