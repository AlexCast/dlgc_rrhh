DELIMITER //

CREATE OR REPLACE PROCEDURE proc_calcular_saldo_vacaciones(
    IN wid_empleado VARCHAR(20)
)
BEGIN
    DECLARE vfecha_ingreso DATE;
    DECLARE vfecha_egreso DATE;
    DECLARE vfecha_corte DATE;
    DECLARE vanios_servicio INT;
    DECLARE vperiodo_inicio DATE;
    DECLARE vperiodo_fin DATE;
    DECLARE vdias_causados DECIMAL(10,2);
    DECLARE vdias_disfrutados DECIMAL(10,2) DEFAULT 0;
    DECLARE vajuste_manual DECIMAL(10,2) DEFAULT 0;
    DECLARE vdias_disfrutados_ajuste DECIMAL(10,2) DEFAULT 0;

    SELECT fecha_ingreso, fecha_egreso
    INTO vfecha_ingreso, vfecha_egreso
    FROM t_empleados
    WHERE id_usuario = wid_empleado AND fec_delete IS NULL LIMIT 1;

    IF vfecha_ingreso IS NULL THEN
        SELECT wid_empleado AS id_empleado, 0 AS anios_servicio, NULL AS periodo_inicio, NULL AS periodo_fin,
               0 AS dias_causados, 0 AS dias_disfrutados, 0 AS ajuste_manual, 0 AS saldo_disponible,
               NULL AS dias_para_vencer, FALSE AS alerta_vencimiento_proximo;
    ELSE
        SET vfecha_corte = LEAST(COALESCE(vfecha_egreso, CURRENT_DATE), CURRENT_DATE);
        SET vanios_servicio = GREATEST(0, TIMESTAMPDIFF(YEAR, vfecha_ingreso, vfecha_corte));

        IF vanios_servicio < 1 THEN
            SELECT wid_empleado AS id_empleado, 0 AS anios_servicio, NULL AS periodo_inicio, NULL AS periodo_fin,
                   0 AS dias_causados, 0 AS dias_disfrutados, 0 AS ajuste_manual, 0 AS saldo_disponible,
                   NULL AS dias_para_vencer, FALSE AS alerta_vencimiento_proximo;
        ELSE
            SET vperiodo_inicio = DATE_ADD(vfecha_ingreso, INTERVAL vanios_servicio YEAR);
            SET vperiodo_fin = DATE_SUB(DATE_ADD(vperiodo_inicio, INTERVAL 1 YEAR), INTERVAL 1 DAY);
            SET vdias_causados = 15;

            SELECT COALESCE(SUM(
                       CASE WHEN sp.es_por_horas
                            THEN TIME_TO_SEC(TIMEDIFF(sp.hora_fin, sp.hora_inicio)) / 3600 / 8
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

            SELECT COALESCE(SUM(CASE WHEN va.dias_ajuste > 0 THEN va.dias_ajuste ELSE 0 END), 0),
                   COALESCE(SUM(CASE WHEN va.dias_ajuste < 0 THEN -va.dias_ajuste ELSE 0 END), 0)
            INTO vajuste_manual, vdias_disfrutados_ajuste
            FROM t_vacaciones_ajustes va
            WHERE va.id_empleado = wid_empleado
              AND va.fec_delete IS NULL
              AND va.fecha_ajuste >= vperiodo_inicio
              AND va.fecha_ajuste <= vperiodo_fin;

            SET vdias_disfrutados = vdias_disfrutados + vdias_disfrutados_ajuste;

            SELECT
                wid_empleado AS id_empleado,
                vanios_servicio AS anios_servicio,
                vperiodo_inicio AS periodo_inicio,
                vperiodo_fin AS periodo_fin,
                vdias_causados AS dias_causados,
                vdias_disfrutados AS dias_disfrutados,
                vajuste_manual AS ajuste_manual,
                (vdias_causados - vdias_disfrutados + vajuste_manual) AS saldo_disponible,
                DATEDIFF(vperiodo_fin, CURRENT_DATE) AS dias_para_vencer,
                ((vdias_causados - vdias_disfrutados + vajuste_manual) > 0 AND DATEDIFF(vperiodo_fin, CURRENT_DATE) <= 30) AS alerta_vencimiento_proximo;
        END IF;
    END IF;
END //

DELIMITER ;