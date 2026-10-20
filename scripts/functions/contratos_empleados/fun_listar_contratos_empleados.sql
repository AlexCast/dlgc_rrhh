DELIMITER //

CREATE OR REPLACE FUNCTION fun_listar_contratos_empleados(
    wid_contrato INT
) RETURNS BOOLEAN
BEGIN
    DECLARE v_id_contrato INT;
    DECLARE v_id_usuario VARCHAR(20);
    DECLARE v_id_area INT;
    DECLARE v_puesto VARCHAR(50);
    DECLARE v_fecha_inicio_puesto DATE;
    DECLARE v_fecha_fin_puesto DATE;
    DECLARE v_tipo_contrato VARCHAR(20);
    DECLARE v_direccion_oficina VARCHAR(100);
    DECLARE v_not_found BOOLEAN DEFAULT FALSE;

    DECLARE CONTINUE HANDLER FOR NOT FOUND SET v_not_found = TRUE;
    DECLARE EXIT HANDLER FOR SQLEXCEPTION RETURN FALSE;

    IF wid_contrato IS NULL OR wid_contrato <= 0 THEN
        RETURN FALSE;
    END IF;

    SELECT id_contrato, id_usuario, id_area, puesto, fecha_inicio_puesto, fecha_fin_puesto, tipo_contrato, direccion_oficina
    INTO v_id_contrato, v_id_usuario, v_id_area, v_puesto, v_fecha_inicio_puesto, v_fecha_fin_puesto, v_tipo_contrato, v_direccion_oficina
    FROM t_contratos_empleados
    WHERE id_contrato = wid_contrato
      AND fec_delete IS NULL
    LIMIT 1;

    IF v_not_found THEN
        RETURN FALSE;
    END IF;

    RETURN TRUE;
END //

DELIMITER ;