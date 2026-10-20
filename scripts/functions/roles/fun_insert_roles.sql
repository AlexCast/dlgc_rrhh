DELIMITER //

CREATE OR REPLACE FUNCTION fun_insert_roles(
    wid_rol INT,
    wnombre_rol VARCHAR(50)
) RETURNS VARCHAR(255)
BEGIN
    DECLARE EXIT HANDLER FOR SQLSTATE '23000' RETURN 'El rol ya existe.';
    DECLARE EXIT HANDLER FOR SQLEXCEPTION RETURN 'Ocurrió un error al insertar el rol.';

    IF wid_rol IS NULL OR wid_rol <= 0 THEN
        RETURN 'El ID del rol no es válido.';
    END IF;

    IF wnombre_rol IS NULL OR LENGTH(TRIM(wnombre_rol)) < 3 THEN
        RETURN 'El nombre del rol debe tener al menos 3 caracteres.';
    END IF;

    INSERT INTO t_roles (
        id_rol,
        nombre_rol,
        usr_insert,
        fec_insert
    )
    VALUES (
        wid_rol,
        TRIM(wnombre_rol),
        COALESCE(@app_current_user, CURRENT_USER()),
        CURRENT_TIMESTAMP
    );

    RETURN 'Rol insertado correctamente.';
END //

DELIMITER ;