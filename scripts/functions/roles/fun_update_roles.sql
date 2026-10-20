DELIMITER //

CREATE OR REPLACE FUNCTION fun_update_roles(
    wid_rol INT,
    wnombre_rol VARCHAR(50)
) RETURNS VARCHAR(255)
BEGIN
    DECLARE EXIT HANDLER FOR SQLSTATE '23000' RETURN 'Ya existe un rol con ese nombre.';
    DECLARE EXIT HANDLER FOR SQLEXCEPTION RETURN 'Ocurrió un error al actualizar el rol.';

    IF wid_rol IS NULL OR wid_rol <= 0 THEN
        RETURN 'El ID del rol no es válido.';
    END IF;

    IF wnombre_rol IS NULL OR LENGTH(TRIM(wnombre_rol)) < 3 THEN
        RETURN 'El nombre del rol debe tener al menos 3 caracteres.';
    END IF;

    UPDATE t_roles
    SET nombre_rol = TRIM(wnombre_rol),
        usr_update = COALESCE(@app_current_user, CURRENT_USER()),
        fec_update = CURRENT_TIMESTAMP
    WHERE id_rol = wid_rol
      AND fec_delete IS NULL;

    IF ROW_COUNT() > 0 THEN
        RETURN 'Rol actualizado correctamente.';
    END IF;

    RETURN 'No se encontró el rol activo para actualizar.';
END //

DELIMITER ;