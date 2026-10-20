DELIMITER //

CREATE OR REPLACE FUNCTION fun_listar_pension(
    wid_pension INT
) RETURNS BOOLEAN
BEGIN
    DECLARE v_id_pension INT;
    DECLARE v_nombre_pension VARCHAR(50);
    DECLARE v_not_found BOOLEAN DEFAULT FALSE;
    
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET v_not_found = TRUE;
    DECLARE EXIT HANDLER FOR SQLEXCEPTION RETURN FALSE;

    IF wid_pension IS NULL OR wid_pension <= 0 THEN
        RETURN FALSE;
    END IF;

    SELECT id_pension, nombre_pension
    INTO v_id_pension, v_nombre_pension
    FROM t_pension
    WHERE id_pension = wid_pension
      AND fec_delete IS NULL
    LIMIT 1;

    IF v_not_found THEN
        RETURN FALSE;
    END IF;

    RETURN TRUE;
END //

DELIMITER ;