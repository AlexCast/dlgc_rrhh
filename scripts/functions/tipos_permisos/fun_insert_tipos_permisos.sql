DELIMITER //

CREATE OR REPLACE FUNCTION fun_insert_tipos_permisos(
    wnombre_tipo VARCHAR(50),
    wdescuenta_tiempo BOOLEAN
) RETURNS VARCHAR(255)
BEGIN
    DECLARE EXIT HANDLER FOR SQLSTATE '23000' RETURN 'El tipo de permiso ya existe.';
    DECLARE EXIT HANDLER FOR SQLEXCEPTION RETURN 'Ocurrió un error al insertar el tipo de permiso.';

    IF wnombre_tipo IS NULL OR LENGTH(TRIM(wnombre_tipo)) < 3 THEN
        RETURN 'El nombre del tipo de permiso debe tener al menos 3 caracteres.';
    END IF;

    INSERT INTO t_tipos_permisos (
        nombre_tipo,
        descuenta_tiempo,
        usr_insert,
        fec_insert
    )
    VALUES (
        TRIM(wnombre_tipo),
        COALESCE(wdescuenta_tiempo, FALSE),
        COALESCE(@app_current_user, CURRENT_USER()),
        CURRENT_TIMESTAMP
    );

    RETURN 'Tipo de permiso insertado correctamente.';
END //

DELIMITER ;