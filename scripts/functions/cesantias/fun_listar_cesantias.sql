DELIMITER //

CREATE OR REPLACE FUNCTION fun_listar_cesantias(
    wid_cesantia INT
) RETURNS BOOLEAN
BEGIN
    DECLARE v_id_cesantia INT;
    DECLARE v_nombre_cesantia VARCHAR(50);
    DECLARE v_not_found BOOLEAN DEFAULT FALSE;
    
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET v_not_found = TRUE;
    DECLARE EXIT HANDLER FOR SQLEXCEPTION RETURN FALSE;

    IF wid_cesantia IS NULL OR wid_cesantia <= 0 THEN
        RETURN FALSE;
    END IF;

    SELECT id_cesantia, nombre_cesantia
    INTO v_id_cesantia, v_nombre_cesantia
    FROM t_cesantias
    WHERE id_cesantia = wid_cesantia
      AND fec_delete IS NULL
    LIMIT 1;

    IF v_not_found THEN
        RETURN FALSE;
    END IF;

    RETURN TRUE;
END //

DELIMITER ;