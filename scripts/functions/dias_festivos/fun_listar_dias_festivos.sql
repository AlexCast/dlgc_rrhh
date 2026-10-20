DELIMITER //

CREATE OR REPLACE FUNCTION fun_listar_dias_festivos(
    wid_festivo INT
) RETURNS BOOLEAN
BEGIN
    DECLARE v_id_festivo INT;
    DECLARE v_fecha DATE;
    DECLARE v_descripcion VARCHAR(100);
    DECLARE v_not_found BOOLEAN DEFAULT FALSE;
    
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET v_not_found = TRUE;
    DECLARE EXIT HANDLER FOR SQLEXCEPTION RETURN FALSE;

    IF wid_festivo IS NULL OR wid_festivo <= 0 THEN
        RETURN FALSE;
    END IF;

    SELECT id_festivo, fecha, descripcion
    INTO v_id_festivo, v_fecha, v_descripcion
    FROM t_dias_festivos
    WHERE id_festivo = wid_festivo
      AND fec_delete IS NULL
    LIMIT 1;

    IF v_not_found THEN
        RETURN FALSE;
    END IF;

    RETURN TRUE;
END //

DELIMITER ;