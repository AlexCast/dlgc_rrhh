CREATE OR REPLACE FUNCTION fun_insert_dias_festivos(
    wfecha t_dias_festivos.fecha%TYPE,
    wdescripcion t_dias_festivos.descripcion%TYPE,
    wdescuenta_salario t_dias_festivos.descuenta_salario%TYPE DEFAULT TRUE
) RETURNS VARCHAR AS
$$
BEGIN
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
        COALESCE(NULLIF(current_setting('app.current_user', true), ''), CURRENT_USER),
        CURRENT_TIMESTAMP
    );

    RETURN 'Día festivo insertado correctamente.';

EXCEPTION
    WHEN SQLSTATE '23505' THEN
        RETURN 'Ya existe un festivo para esa fecha.';
    WHEN OTHERS THEN
        RAISE NOTICE 'Error: %', SQLERRM;
        RETURN 'Ocurrió un error al insertar el día festivo.';
END;
$$
LANGUAGE PLPGSQL;