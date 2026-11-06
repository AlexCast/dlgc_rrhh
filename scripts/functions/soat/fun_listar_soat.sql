DELIMITER //

CREATE OR REPLACE FUNCTION fun_listar_soat(
    wid_soat INT
) RETURNS BOOLEAN
BEGIN
    DECLARE v_id_soat INT;
    DECLARE v_nombre_soat VARCHAR(50);
    DECLARE v_not_found BOOLEAN DEFAULT FALSE;
    
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET v_not_found = TRUE;
    DECLARE EXIT HANDLER FOR SQLEXCEPTION RETURN FALSE;

    IF wid_soat IS NULL OR wid_soat <= 0 THEN
        RETURN FALSE;
    END IF;

    SELECT id_soat, nombre_soat
    INTO v_id_soat, v_nombre_soat
    FROM t_soat
    WHERE id_soat = wid_soat
      AND fec_delete IS NULL
    LIMIT 1;

    IF v_not_found THEN
        RETURN FALSE;
    END IF;

    RETURN TRUE;
END //

DELIMITER ;