DELIMITER //

CREATE OR REPLACE FUNCTION fun_listar_eps(
    wid_eps INT
) RETURNS BOOLEAN
BEGIN
    DECLARE v_id_eps INT;
    DECLARE v_nombre_eps VARCHAR(50);
    DECLARE v_not_found BOOLEAN DEFAULT FALSE;
    
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET v_not_found = TRUE;
    DECLARE EXIT HANDLER FOR SQLEXCEPTION RETURN FALSE;

    IF wid_eps IS NULL OR wid_eps <= 0 THEN
        RETURN FALSE;
    END IF;

    SELECT id_eps, nombre_eps
    INTO v_id_eps, v_nombre_eps
    FROM t_eps
    WHERE id_eps = wid_eps
      AND fec_delete IS NULL
    LIMIT 1;

    IF v_not_found THEN
        RETURN FALSE;
    END IF;

    RETURN TRUE;
END //

DELIMITER ;