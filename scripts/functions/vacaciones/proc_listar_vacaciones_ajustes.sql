DELIMITER //

CREATE OR REPLACE PROCEDURE proc_listar_vacaciones_ajustes(
    IN wid_empleado VARCHAR(20)
)
BEGIN
    SELECT va.id_ajuste, va.id_empleado, va.dias_ajuste, va.motivo, va.fecha_ajuste,
           (va.fec_delete IS NULL) AS activo, va.usr_insert, va.fec_insert
    FROM t_vacaciones_ajustes va
    WHERE va.id_empleado = wid_empleado
    ORDER BY va.fec_insert DESC;
END //

DELIMITER ;