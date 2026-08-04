CREATE OR REPLACE FUNCTION fun_update_dias_festivos(
    wid_festivo t_dias_festivos.id_festivo%TYPE,
    wfecha t_dias_festivos.fecha%TYPE,
    wdescripcion t_dias_festivos.descripcion%TYPE,
    wdescuenta_salario t_dias_festivos.descuenta_salario%TYPE DEFAULT TRUE
) RETURNS VARCHAR AS
$$
BEGIN
    IF wid_festivo IS NULL OR wid_festivo <= 0 THEN
        RETURN 'El ID del festivo no es válido.';
    END IF;

    IF wfecha IS NULL THEN
        RETURN 'La fecha del festivo es obligatoria.';
    END IF;

    IF wdescripcion IS NULL OR LENGTH(TRIM(wdescripcion)) < 3 THEN
        RETURN 'La descripción del festivo debe tener al menos 3 caracteres.';
    END IF;

    -- Solo se pueden editar festivos de EMPRESA desde aquí; los NACIONAL son de solo lectura.
    UPDATE t_dias_festivos
    SET fecha = wfecha,
        descripcion = TRIM(wdescripcion),
        descuenta_salario = COALESCE(wdescuenta_salario, TRUE),
        usr_update = COALESCE(NULLIF(current_setting('app.current_user', true), ''), CURRENT_USER),
        fec_update = CURRENT_TIMESTAMP
    WHERE id_festivo = wid_festivo
      AND tipo_festivo = 'EMPRESA'
      AND fec_delete IS NULL;

    IF FOUND THEN
        RETURN 'Día festivo actualizado correctamente.';
    END IF;

    RETURN 'No se encontró el festivo de empresa activo para actualizar.';

EXCEPTION
    WHEN SQLSTATE '23505' THEN
        RETURN 'Ya existe otro festivo con esa fecha.';
    WHEN OTHERS THEN
        RAISE NOTICE 'Error: %', SQLERRM;
        RETURN 'Ocurrió un error al actualizar el día festivo.';
END;
$$
LANGUAGE PLPGSQL;