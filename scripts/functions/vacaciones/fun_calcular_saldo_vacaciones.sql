-- Calcula el saldo de vacaciones de un empleado bajo política de la empresa: NO acumulables.
-- Cada aniversario de fecha_ingreso reinicia el ciclo y se causan 15 días hábiles nuevos;
-- lo no disfrutado del ciclo anterior se pierde (no pasa al siguiente).
--   - periodo_inicio/periodo_fin: ventana del ciclo aniversario vigente (fecha_ingreso + N
--     años, hasta el día antes del próximo aniversario). Antes de cumplir el primer año no
--     hay ciclo activo y dias_causados = 0.
--   - dias_disfrutados = días hábiles (o equivalente en horas / 8) de solicitudes APROBADAS
--     con metodo_descuento = 'VACACIONES' que se solapen con el ciclo vigente; si una solicitud
--     cruza el límite del ciclo, solo se cuenta la porción de días dentro del ciclo actual.
--   - ajuste_manual = suma de t_vacaciones_ajustes activos cuya fecha_ajuste cae dentro del
--     ciclo vigente (ajustes de ciclos anteriores ya no cuentan, se perdieron con el reinicio).
--   - saldo_disponible = dias_causados - dias_disfrutados + ajuste_manual. No se permiten
--     anticipos (lo valida fun_resolver_permisos_aprobaciones).
--   - dias_para_vencer / alerta_vencimiento_proximo: aviso para que RRHH empuje al empleado
--     a tomar el saldo restante antes de perderlo en el próximo aniversario (<=30 días).
-- Empleados con fecha_egreso: el cálculo se congela ahí (no hay ciclo vigente después).
CREATE OR REPLACE FUNCTION fun_calcular_saldo_vacaciones(
    wid_empleado t_empleados.id_usuario%TYPE
) RETURNS TABLE (
    id_empleado VARCHAR,
    anios_servicio INT,
    periodo_inicio DATE,
    periodo_fin DATE,
    dias_causados NUMERIC,
    dias_disfrutados NUMERIC,
    ajuste_manual NUMERIC,
    saldo_disponible NUMERIC,
    dias_para_vencer INT,
    alerta_vencimiento_proximo BOOLEAN
) AS
$$
DECLARE
    vfecha_ingreso DATE;
    vfecha_egreso DATE;
    vfecha_corte DATE;
    vanios_servicio INT;
    vperiodo_inicio DATE;
    vperiodo_fin DATE;
    vdias_causados NUMERIC;
    vdias_disfrutados NUMERIC;
    vajuste_manual NUMERIC;
BEGIN
    SELECT e.fecha_ingreso, e.fecha_egreso
      INTO vfecha_ingreso, vfecha_egreso
      FROM t_empleados e
     WHERE e.id_usuario = wid_empleado
       AND e.fec_delete IS NULL;

    IF NOT FOUND THEN
        RETURN;
    END IF;

    -- Si fecha_egreso es futura (retiro programado), el corte sigue siendo hoy, no esa fecha.
    vfecha_corte := LEAST(COALESCE(vfecha_egreso, CURRENT_DATE), CURRENT_DATE);
    vanios_servicio := GREATEST(0, DATE_PART('year', AGE(vfecha_corte, vfecha_ingreso))::INT);

    IF vanios_servicio < 1 THEN
        -- Aún no cumple el primer año: no hay ciclo causado todavía.
        RETURN QUERY SELECT wid_empleado, 0, NULL::DATE, NULL::DATE, 0::NUMERIC, 0::NUMERIC, 0::NUMERIC, 0::NUMERIC, NULL::INT, FALSE;
        RETURN;
    END IF;

    vperiodo_inicio := vfecha_ingreso + (vanios_servicio || ' years')::INTERVAL;
    vperiodo_fin := vperiodo_inicio + INTERVAL '1 year' - INTERVAL '1 day';
    vdias_causados := 15;

    SELECT COALESCE(SUM(
               CASE WHEN sp.es_por_horas
                    THEN EXTRACT(EPOCH FROM (sp.hora_fin - sp.hora_inicio)) / 3600 / 8
                    -- Recorta al rango vigente por si la solicitud cruza el límite del ciclo.
                    ELSE fun_dias_habiles_rango(GREATEST(sp.fecha_inicio, vperiodo_inicio), LEAST(sp.fecha_fin, vperiodo_fin))
               END
           ), 0)
      INTO vdias_disfrutados
      FROM t_solicitudes_permisos sp
     WHERE sp.id_empleado = wid_empleado
       AND sp.estado = 'APROBADO'
       AND sp.metodo_descuento = 'VACACIONES'
       AND sp.fec_delete IS NULL
       AND sp.fecha_inicio <= vperiodo_fin
       AND sp.fecha_fin >= vperiodo_inicio;

    SELECT COALESCE(SUM(va.dias_ajuste), 0) INTO vajuste_manual
      FROM t_vacaciones_ajustes va
     WHERE va.id_empleado = wid_empleado
       AND va.fec_delete IS NULL
       AND va.fecha_ajuste >= vperiodo_inicio
       AND va.fecha_ajuste <= vperiodo_fin;

    RETURN QUERY SELECT
        wid_empleado,
        vanios_servicio,
        vperiodo_inicio,
        vperiodo_fin,
        vdias_causados,
        vdias_disfrutados,
        vajuste_manual,
        vdias_causados - vdias_disfrutados + vajuste_manual,
        (vperiodo_fin - CURRENT_DATE)::INT,
        (vdias_causados - vdias_disfrutados + vajuste_manual) > 0 AND (vperiodo_fin - CURRENT_DATE) <= 30;
END;
$$
LANGUAGE PLPGSQL STABLE;

