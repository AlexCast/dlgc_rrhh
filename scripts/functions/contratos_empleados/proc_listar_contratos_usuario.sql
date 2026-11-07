DELIMITER //

CREATE OR REPLACE PROCEDURE proc_listar_contratos_usuario(
    IN wid_usuario VARCHAR(20) COLLATE utf8mb4_unicode_ci
)
BEGIN
    SELECT id_contrato, puesto, tipo_contrato, fecha_inicio_puesto, fecha_fin_puesto
    FROM t_contratos_empleados
    WHERE id_usuario = wid_usuario
      AND fec_delete IS NULL
    ORDER BY fecha_inicio_puesto DESC, id_contrato DESC;
END //

DELIMITER ;
