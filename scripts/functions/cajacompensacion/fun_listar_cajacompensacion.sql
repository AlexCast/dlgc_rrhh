DELIMITER //

CREATE OR REPLACE FUNCTION fun_listar_caja(
    wid_caja INT
) RETURNS BOOLEAN
BEGIN
    DECLARE v_id_caja INT;
    DECLARE v_nombre_caja VARCHAR(50);
    DECLARE v_not_found BOOLEAN DEFAULT FALSE;
    
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET v_not_found = TRUE;
    DECLARE EXIT HANDLER FOR SQLEXCEPTION RETURN FALSE;

    IF wid_caja IS NULL OR wid_caja <= 0 THEN
        RETURN FALSE;
    END IF;

    SELECT id_caja, nombre_caja
    INTO v_id_caja, v_nombre_caja
    FROM t_caja_compensacion
    WHERE id_caja = wid_caja
      AND fec_delete IS NULL
    LIMIT 1;

    IF v_not_found THEN
        RETURN FALSE;
    END IF;

    RETURN TRUE;
END //

DELIMITER ;