CREATE OR REPLACE FUNCTION fun_listar_permisos_dias_aprobados(
    wid_dia_permiso t_permisos_dias_aprobados.id_dia_permiso%TYPE
) RETURNS BOOLEAN AS
$$
DECLARE
    wreg_dia t_permisos_dias_aprobados%ROWTYPE;
BEGIN
    IF wid_dia_permiso IS NULL OR wid_dia_permiso <= 0 THEN
        RAISE NOTICE 'El ID del día de permiso no es válido.';
        RETURN FALSE;
    END IF;

    SELECT *
    INTO wreg_dia
    FROM t_permisos_dias_aprobados
    WHERE id_dia_permiso = wid_dia_permiso
      AND fec_delete IS NULL;

    IF NOT FOUND THEN
        RAISE NOTICE 'No existe día de permiso activo con ID %.', wid_dia_permiso;
        RETURN FALSE;
    END IF;

    RAISE NOTICE 'ID: %, Permiso: %, Empleado: %, Fecha: %, Horas: %',
        wreg_dia.id_dia_permiso,
        wreg_dia.id_permiso,
        wreg_dia.id_empleado,
        wreg_dia.fecha,
        wreg_dia.horas_aprobadas;
    RETURN TRUE;

EXCEPTION
    WHEN OTHERS THEN
        RAISE NOTICE 'Error: %', SQLERRM;
        RETURN FALSE;
END;
$$
LANGUAGE PLPGSQL;