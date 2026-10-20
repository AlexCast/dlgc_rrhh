DELIMITER //

CREATE OR REPLACE FUNCTION fun_insert_operaciones(
    wid_operacion INT,
    wid_modulo INT,
    wnombre_operacion VARCHAR(50)
) RETURNS VARCHAR(255)
BEGIN
    -- En MariaDB, SQLSTATE '23000' cubre tanto duplicados (23505 en PG) como llaves foráneas inexistentes (23503 en PG)
    DECLARE EXIT HANDLER FOR SQLSTATE '23000' RETURN 'La operación ya existe o el módulo indicado no existe.';
    DECLARE EXIT HANDLER FOR SQLEXCEPTION RETURN 'Ocurrió un error al insertar la operación.';

    IF wid_operacion IS NULL OR wid_operacion <= 0 THEN
        RETURN 'El ID de la operación no es válido.';
    END IF;

    IF wid_modulo IS NULL OR wid_modulo <= 0 THEN
        RETURN 'El ID del módulo no es válido.';
    END IF;

    IF wnombre_operacion IS NULL OR LENGTH(TRIM(wnombre_operacion)) < 3 THEN
        RETURN 'El nombre de la operación debe tener al menos 3 caracteres.';
    END IF;

    INSERT INTO t_operaciones (
        id_operacion,
        id_modulo,
        nombre_operacion,
        usr_insert,
        fec_insert
    )
    VALUES (
        wid_operacion,
        wid_modulo,
        TRIM(wnombre_operacion),
        COALESCE(@app_current_user, CURRENT_USER()),
        CURRENT_TIMESTAMP
    );

    RETURN 'Operación insertada correctamente.';
END //

DELIMITER ;