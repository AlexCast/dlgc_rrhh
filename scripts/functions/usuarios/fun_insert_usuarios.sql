DELIMITER //

CREATE OR REPLACE FUNCTION fun_insert_usuarios(
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
    DECLARE EXIT HANDLER FOR SQLEXCEPTION RETURN 'Error inesperado al crear el usuario.';

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
    IF wcontrasena IS NULL OR wcontrasena = '' THEN
        RETURN 'La contraseña no puede ser nula o vacía.';
    END IF;

    INSERT INTO t_usuarios (
        id_usuario,
        id_rol,
        username,
        tipo_documento,
        primer_nombre,
        segundo_nombre,
        primer_apellido,
        segundo_apellido,
        correo,
        contrasena,
        usr_insert,
        fec_insert
    )
    VALUES (
        wid_usuario,
        wid_rol,
        wusername,
        wtipo_documento,
        wprimer_nombre,
        wsegundo_nombre,
        wprimer_apellido,
        wsegundo_apellido,
        wcorreo,
        wcontrasena,
        COALESCE(@app_current_user, CURRENT_USER()),
        CURRENT_TIMESTAMP
    );

    RETURN 'OK';
END //

DELIMITER ;