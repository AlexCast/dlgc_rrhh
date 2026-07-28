CREATE OR REPLACE FUNCTION fun_update_dias_festivos(
    wid_festivo t_dias_festivos.id_festivo%TYPE,
    wfecha t_dias_festivos.fecha%TYPE,
    wdescripcion t_dias_festivos.descripcion%TYPE
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

    UPDATE t_dias_festivos
    SET fecha = wfecha,
        descripcion = TRIM(wdescripcion),
        usr_update = COALESCE(NULLIF(current_setting('app.current_user', true), ''), CURRENT_USER),
        fec_update = CURRENT_TIMESTAMP
    WHERE id_festivo = wid_festivo
      AND fec_delete IS NULL;

    IF FOUND THEN
        RETURN 'Día festivo actualizado correctamente.';
    END IF;

    RETURN 'No se encontró el festivo activo para actualizar.';

EXCEPTION
    WHEN SQLSTATE '23505' THEN
        RETURN 'Ya existe otro festivo con esa fecha.';
    WHEN OTHERS THEN
        RAISE NOTICE 'Error: %', SQLERRM;
        RETURN 'Ocurrió un error al actualizar el día festivo.';
END;
$$
LANGUAGE PLPGSQL;