CREATE OR REPLACE FUNCTION fun_insert_permisos_dias_aprobados(
    wid_permiso t_permisos_dias_aprobados.id_permiso%TYPE,
    wid_empleado t_permisos_dias_aprobados.id_empleado%TYPE,
    wfecha t_permisos_dias_aprobados.fecha%TYPE,
    whoras_aprobadas t_permisos_dias_aprobados.horas_aprobadas%TYPE
) RETURNS VARCHAR AS
$$
BEGIN
    IF wid_permiso IS NULL OR wid_permiso <= 0 THEN
        RETURN 'El ID del permiso no es válido.';
    END IF;

    IF wid_empleado IS NULL OR wid_empleado = '' THEN
        RETURN 'El ID del empleado es obligatorio.';
    END IF;

    IF wfecha IS NULL THEN
        RETURN 'La fecha es obligatoria.';
    END IF;

    IF whoras_aprobadas IS NULL OR whoras_aprobadas <= 0 OR whoras_aprobadas > 24 THEN
        RETURN 'Las horas aprobadas deben estar entre 0 y 24.';
    END IF;

    INSERT INTO t_permisos_dias_aprobados (
        id_permiso,
        id_empleado,
        fecha,
        horas_aprobadas,
        usr_insert,
        fec_insert
    )
    VALUES (
        wid_permiso,
        wid_empleado,
        wfecha,
        whoras_aprobadas,
        COALESCE(NULLIF(current_setting('app.current_user', true), ''), CURRENT_USER),
        CURRENT_TIMESTAMP
    );

    RETURN 'Día de permiso aprobado insertado correctamente.';

EXCEPTION
    WHEN SQLSTATE '23503' THEN
        RETURN 'No existe el permiso o empleado indicado.';
    WHEN SQLSTATE '23514' THEN
        RETURN 'Las horas aprobadas no cumplen las reglas de la tabla.';
    WHEN OTHERS THEN
        RAISE NOTICE 'Error: %', SQLERRM;
        RETURN 'Ocurrió un error al insertar el día de permiso aprobado.';
END;
$$
LANGUAGE PLPGSQL;