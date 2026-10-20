DELIMITER //

CREATE OR REPLACE FUNCTION fun_listar_nomina(
    wid_nomina INT
) RETURNS BOOLEAN
BEGIN
    DECLARE v_id_nomina INT;
    DECLARE v_id_usuario VARCHAR(20);
    DECLARE v_id_banco INT;
    DECLARE v_num_cuenta VARCHAR(20);
    DECLARE v_salario DECIMAL(10,2);
    DECLARE v_not_found BOOLEAN DEFAULT FALSE;

    DECLARE CONTINUE HANDLER FOR NOT FOUND SET v_not_found = TRUE;
    DECLARE EXIT HANDLER FOR SQLEXCEPTION RETURN FALSE;

    IF wid_nomina IS NULL OR wid_nomina <= 0 THEN
        RETURN FALSE;
    END IF;

    SELECT id_nomina, id_usuario, id_banco, num_cuenta, salario
    INTO v_id_nomina, v_id_usuario, v_id_banco, v_num_cuenta, v_salario
    FROM t_nomina
    WHERE id_nomina = wid_nomina
      AND fec_delete IS NULL
    LIMIT 1;

    IF v_not_found THEN
        RETURN FALSE;
    END IF;

    RETURN TRUE;
END //

DELIMITER ;