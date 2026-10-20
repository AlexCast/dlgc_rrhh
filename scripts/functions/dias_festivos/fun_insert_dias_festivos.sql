DELIMITER //

CREATE OR REPLACE FUNCTION fun_insert_dias_festivos(
    wfecha DATE,
    wdescripcion VARCHAR(100),
    wdescuenta_salario BOOLEAN
) RETURNS VARCHAR(255)
BEGIN
    DECLARE EXIT HANDLER FOR SQLSTATE '23000' RETURN 'Ya existe un festivo para esa fecha.';
    DECLARE EXIT HANDLER FOR SQLEXCEPTION RETURN 'Ocurrió un error al insertar el día festivo.';

    IF wfecha IS NULL THEN
        RETURN 'La fecha del festivo es obligatoria.';
    END IF;

    IF wdescripcion IS NULL OR LENGTH(TRIM(wdescripcion)) < 3 THEN
        RETURN 'La descripción del festivo debe tener al menos 3 caracteres.';
    END IF;

    -- Este endpoint es solo para festivos de EMPRESA; los NACIONAL se siembran con fun_sembrar_festivos_colombia.
    INSERT INTO t_dias_festivos (
        fecha,
        descripcion,
        tipo_festivo,
        descuenta_salario,
        usr_insert,
        fec_insert
    )
    VALUES (
        wfecha,
        TRIM(wdescripcion),
        'EMPRESA',
        COALESCE(wdescuenta_salario, TRUE),
        COALESCE(@app_current_user, CURRENT_USER()),
        CURRENT_TIMESTAMP
    );

    RETURN 'Día festivo insertado correctamente.';
END //

DELIMITER ;