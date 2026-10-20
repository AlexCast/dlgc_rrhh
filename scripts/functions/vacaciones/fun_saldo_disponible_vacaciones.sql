DELIMITER //

-- Devuelve únicamente el saldo disponible de vacaciones (DECIMAL) para un empleado.
-- Replica la política de proc_calcular_saldo_vacaciones: 15 días/año, no acumulables,
-- reinicio en el aniversario; descuenta lo disfrutado y suma los ajustes manuales.
-- Se usa dentro de otros procedimientos (ej. proc_resolver_permisos_aprobaciones)
-- donde no se puede consumir el result set de un CALL.
CREATE OR REPLACE FUNCTION fun_saldo_disponible_vacaciones(
    wid_empleado VARCHAR(20)
) RETURNS DECIMAL(10,2)
BEGIN
    DECLARE vfecha_ingreso DATE;
    DECLARE vfecha_egreso DATE;
    DECLARE vcorte DATE;
    DECLARE vanios INT;
    DECLARE vp_inicio DATE;
    DECLARE vp_fin DATE;
    DECLARE vdisfrutados DECIMAL(10,2) DEFAULT 0;
    DECLARE vajustes DECIMAL(10,2) DEFAULT 0;

    IF wid_empleado IS NULL OR TRIM(wid_empleado) = '' THEN
        RETURN 0;
    END IF;

    SELECT fecha_ingreso, fecha_egreso
    INTO vfecha_ingreso, vfecha_egreso
    FROM t_empleados
    WHERE id_usuario = wid_empleado
      AND fec_delete IS NULL
    LIMIT 1;

    IF vfecha_ingreso IS NULL THEN
        RETURN 0;
    END IF;

    SET vcorte = LEAST(COALESCE(vfecha_egreso, CURRENT_DATE), CURRENT_DATE);
    SET vanios = GREATEST(0, TIMESTAMPDIFF(YEAR, vfecha_ingreso, vcorte));

    IF vanios < 1 THEN
        RETURN 0;
    END IF;

    SET vp_inicio = DATE_ADD(vfecha_ingreso, INTERVAL vanios YEAR);
    SET vp_fin = DATE_SUB(DATE_ADD(vp_inicio, INTERVAL 1 YEAR), INTERVAL 1 DAY);

    SELECT COALESCE(SUM(
               CASE WHEN sp.es_por_horas
                    THEN TIME_TO_SEC(TIMEDIFF(sp.hora_fin, sp.hora_inicio)) / 3600 / 8
                    ELSE fun_dias_habiles_rango(GREATEST(sp.fecha_inicio, vp_inicio), LEAST(sp.fecha_fin, vp_fin))
               END
           ), 0)
    INTO vdisfrutados
    FROM t_solicitudes_permisos sp
    WHERE sp.id_empleado = wid_empleado
      AND sp.estado = 'APROBADO'
      AND sp.metodo_descuento = 'VACACIONES'
      AND sp.fec_delete IS NULL
      AND sp.fecha_inicio <= vp_fin
      AND sp.fecha_fin >= vp_inicio;

    SELECT COALESCE(SUM(CASE WHEN va.dias_ajuste > 0 THEN va.dias_ajuste ELSE 0 END), 0)
         - COALESCE(SUM(CASE WHEN va.dias_ajuste < 0 THEN -va.dias_ajuste ELSE 0 END), 0)
    INTO vajustes
    FROM t_vacaciones_ajustes va
    WHERE va.id_empleado = wid_empleado
      AND va.fec_delete IS NULL
      AND va.fecha_ajuste >= vp_inicio
      AND va.fecha_ajuste <= vp_fin;

    RETURN 15 - vdisfrutados + vajustes;
END //

DELIMITER ;
