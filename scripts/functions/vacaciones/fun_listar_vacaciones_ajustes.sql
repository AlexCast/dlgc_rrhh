-- Lista el historial de ajustes manuales de vacaciones de un empleado (activos e inactivos),
-- para que RRHH pueda auditar por qué el saldo no parte de cero/quince.
CREATE OR REPLACE FUNCTION fun_listar_vacaciones_ajustes(
    wid_empleado t_vacaciones_ajustes.id_empleado%TYPE
) RETURNS TABLE (
    id_ajuste INT,
    id_empleado VARCHAR,
    dias_ajuste NUMERIC,
    motivo VARCHAR,
    fecha_ajuste DATE,
    activo BOOLEAN,
    usr_insert VARCHAR,
    fec_insert TIMESTAMP WITHOUT TIME ZONE
) AS
$$
BEGIN
    RETURN QUERY
    SELECT va.id_ajuste, va.id_empleado, va.dias_ajuste, va.motivo, va.fecha_ajuste,
           (va.fec_delete IS NULL) AS activo, va.usr_insert, va.fec_insert
    FROM t_vacaciones_ajustes va
    WHERE va.id_empleado = wid_empleado
    ORDER BY va.fec_insert DESC;
END;
$$
LANGUAGE PLPGSQL STABLE;
