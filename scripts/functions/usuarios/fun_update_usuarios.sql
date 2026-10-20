DELIMITER //

CREATE OR REPLACE FUNCTION fun_update_usuarios(
    wid_usuario VARCHAR(20),
    wid_rol INT,
    wusername VARCHAR(30),
    wtipo_documento VARCHAR(20),
    wprimer_nombre VARCHAR(30),
    wprimer_apellido VARCHAR(30),
    wcorreo VARCHAR(40),
    wcontrasena VARCHAR(255),
    wsegundo_nombre VARCHAR(30),
    wsegundo_apellido VARCHAR(30)
) RETURNS VARCHAR(255)
BEGIN
    DECLARE EXIT HANDLER FOR SQLSTATE '23000' RETURN 'El documento, usuario o correo ya están registrados.';
    DECLARE EXIT HANDLER FOR SQLEXCEPTION RETURN 'Error inesperado al actualizar el usuario.';

    IF wid_usuario IS NULL OR wid_usuario = '' THEN
        RETURN 'El id_usuario no puede ser nulo o vacío.';
    END IF;
    IF wid_rol IS NULL OR wid_rol <= 0 THEN
        RETURN 'El id_rol no puede ser nulo o inválido.';
    END IF;
    IF wusername IS NULL OR wusername = '' THEN
        RETURN 'El username no puede ser nulo o vacío.';
    END IF;
    IF wcorreo IS NULL OR wcorreo = '' THEN
        RETURN 'El correo no puede ser nulo o vacío.';
    END IF;

    UPDATE t_usuarios
    SET id_rol = wid_rol,
        username = wusername,
        tipo_documento = wtipo_documento,
        primer_nombre = wprimer_nombre,
        segundo_nombre = wsegundo_nombre,
        primer_apellido = wprimer_apellido,
        segundo_apellido = wsegundo_apellido,
        correo = wcorreo,
        contrasena = CASE WHEN wcontrasena IS NULL OR wcontrasena = '' THEN contrasena ELSE wcontrasena END,
        usr_update = COALESCE(@app_current_user, CURRENT_USER()),
        fec_update = CURRENT_TIMESTAMP
    WHERE id_usuario = wid_usuario
      AND fec_delete IS NULL;

    IF ROW_COUNT() > 0 THEN
        RETURN 'Usuario actualizado correctamente.';
    END IF;

    RETURN 'No se encontró el usuario activo para actualizar.';
END //

DELIMITER ;