DELIMITER //

CREATE OR REPLACE FUNCTION fun_insert_permisos_usuarios(
    wid_usuario VARCHAR(20),
    wid_operacion INT
) RETURNS VARCHAR(255)
BEGIN
    DECLARE EXIT HANDLER FOR SQLSTATE '23000' RETURN 'El permiso individual ya existe o el usuario/operación no existe.';
    DECLARE EXIT HANDLER FOR SQLEXCEPTION RETURN 'Ocurrió un error al insertar el permiso individual.';

    IF wid_usuario IS NULL OR TRIM(wid_usuario) = '' THEN
        RETURN 'El ID del usuario no es válido.';
    END IF;

    IF wid_operacion IS NULL OR wid_operacion <= 0 THEN
        RETURN 'El ID de la operación no es válido.';
    END IF;

    INSERT INTO t_usuarios_operaciones (
        id_usuario,
        id_operacion,
        usr_insert,
        fec_insert
    )
    VALUES (
        TRIM(wid_usuario),
        wid_operacion,
        COALESCE(@app_current_user, CURRENT_USER()),
        CURRENT_TIMESTAMP
    );

    RETURN 'Permiso individual insertado correctamente.';
END //

DELIMITER ;