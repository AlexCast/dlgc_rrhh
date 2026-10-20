DELIMITER //

CREATE OR REPLACE FUNCTION fun_update_modulos(
    wid_modulo INT,
    wnombre_modulo VARCHAR(30)
) RETURNS VARCHAR(255)
BEGIN
    DECLARE EXIT HANDLER FOR SQLSTATE '23000' RETURN 'Ya existe un módulo con ese nombre.';
    DECLARE EXIT HANDLER FOR SQLEXCEPTION RETURN 'Ocurrió un error al actualizar el módulo.';

    IF wid_modulo IS NULL OR wid_modulo <= 0 THEN
        RETURN 'El ID del módulo no es válido.';
    END IF;

    IF wnombre_modulo IS NULL OR LENGTH(TRIM(wnombre_modulo)) < 3 THEN
        RETURN 'El nombre del módulo debe tener al menos 3 caracteres.';
    END IF;

    UPDATE t_modulos
    SET nombre_modulo = TRIM(wnombre_modulo),
        usr_update = COALESCE(@app_current_user, CURRENT_USER()),
        fec_update = CURRENT_TIMESTAMP
    WHERE id_modulo = wid_modulo
      AND fec_delete IS NULL;

    -- Reemplazo exacto del IF FOUND de PostgreSQL
    IF ROW_COUNT() > 0 THEN
        RETURN 'Módulo actualizado correctamente.';
    END IF;

    RETURN 'No se encontró el módulo activo para actualizar.';
END //

DELIMITER ;