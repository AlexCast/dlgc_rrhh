CREATE OR REPLACE FUNCTION fun_update_permisos_dias_aprobados(
    wid_dia_permiso t_permisos_dias_aprobados.id_dia_permiso%TYPE,
    wfecha t_permisos_dias_aprobados.fecha%TYPE,
    whoras_aprobadas t_permisos_dias_aprobados.horas_aprobadas%TYPE
) RETURNS VARCHAR AS
$$
BEGIN
    IF wid_dia_permiso IS NULL OR wid_dia_permiso <= 0 THEN
        RETURN 'El ID del día de permiso no es válido.';
    END IF;

    IF wfecha IS NULL THEN
        RETURN 'La fecha es obligatoria.';
    END IF;

    IF whoras_aprobadas IS NULL OR whoras_aprobadas <= 0 OR whoras_aprobadas > 24 THEN
        RETURN 'Las horas aprobadas deben estar entre 0 y 24.';
    END IF;

    UPDATE t_permisos_dias_aprobados
    SET fecha = wfecha,
        horas_aprobadas = whoras_aprobadas,
        usr_update = COALESCE(NULLIF(current_setting('app.current_user', true), ''), CURRENT_USER),
        fec_update = CURRENT_TIMESTAMP
    WHERE id_dia_permiso = wid_dia_permiso
      AND fec_delete IS NULL;

    IF FOUND THEN
        RETURN 'Día de permiso aprobado actualizado correctamente.';
    END IF;

    RETURN 'No se encontró el día de permiso activo para actualizar.';

EXCEPTION
    WHEN SQLSTATE '23514' THEN
        RETURN 'Las horas aprobadas no cumplen las reglas de la tabla.';
    WHEN OTHERS THEN
        RAISE NOTICE 'Error: %', SQLERRM;
        RETURN 'Ocurrió un error al actualizar el día de permiso aprobado.';
END;
$$
LANGUAGE PLPGSQL;