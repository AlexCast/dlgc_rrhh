DELIMITER //

CREATE OR REPLACE FUNCTION fun_listar_arl(
    wid_arl INT
) RETURNS BOOLEAN
BEGIN
    DECLARE v_id_arl INT;
    DECLARE v_nombre_arl VARCHAR(50);
    DECLARE v_not_found BOOLEAN DEFAULT FALSE;
    
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET v_not_found = TRUE;
    DECLARE EXIT HANDLER FOR SQLEXCEPTION RETURN FALSE;

    IF wid_arl IS NULL OR wid_arl <= 0 THEN
        RETURN FALSE;
    END IF;

    SELECT id_arl, nombre_arl
    INTO v_id_arl, v_nombre_arl
    FROM t_arl
    WHERE id_arl = wid_arl
      AND fec_delete IS NULL
    LIMIT 1;

    IF v_not_found THEN
        RETURN FALSE;
    END IF;

    RETURN TRUE;
END //

DELIMITER ;