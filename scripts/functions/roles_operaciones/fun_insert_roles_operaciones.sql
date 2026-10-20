DELIMITER //

CREATE OR REPLACE FUNCTION fun_insert_roles_operaciones(
    wid_rol INT,
    wid_operacion INT
) RETURNS VARCHAR(255)
BEGIN
    DECLARE EXIT HANDLER FOR SQLSTATE '23000' RETURN 'La asignación rol-operación ya existe o el rol/operación no existe.';
    DECLARE EXIT HANDLER FOR SQLEXCEPTION RETURN 'Ocurrió un error al insertar la asignación rol-operación.';

    IF wid_rol IS NULL OR wid_rol <= 0 THEN
        RETURN 'El ID del rol no es válido.';
    END IF;

    IF wid_operacion IS NULL OR wid_operacion <= 0 THEN
        RETURN 'El ID de la operación no es válido.';
    END IF;

    INSERT INTO t_roles_operaciones (
        id_rol,
        id_operacion,
        usr_insert,
        fec_insert
    )
    VALUES (
        wid_rol,
        wid_operacion,
        COALESCE(@app_current_user, CURRENT_USER()),
        CURRENT_TIMESTAMP
    );

    RETURN 'Asignación rol-operación insertada correctamente.';
END //

DELIMITER ;