DELIMITER //

CREATE OR REPLACE FUNCTION fun_insert_modulos(
    wid_modulo INT,
    wnombre_modulo VARCHAR(30)
) RETURNS VARCHAR(255)
BEGIN
    -- Declaración de excepciones al inicio del bloque
    DECLARE EXIT HANDLER FOR SQLSTATE '23000' RETURN 'El módulo ya existe.';
    DECLARE EXIT HANDLER FOR SQLEXCEPTION RETURN 'Ocurrió un error al insertar el módulo.';

    IF wid_modulo IS NULL OR wid_modulo <= 0 THEN
        RETURN 'El ID del módulo no es válido.';
    END IF;

    IF wnombre_modulo IS NULL OR LENGTH(TRIM(wnombre_modulo)) < 3 THEN
        RETURN 'El nombre del módulo debe tener al menos 3 caracteres.';
    END IF;

    INSERT INTO t_modulos (
        id_modulo,
        nombre_modulo,
        usr_insert,
        fec_insert
    )
    VALUES (
        wid_modulo,
        TRIM(wnombre_modulo),
        COALESCE(@app_current_user, CURRENT_USER()),
        CURRENT_TIMESTAMP
    );

    RETURN 'Módulo insertado correctamente.';
END //

DELIMITER ;