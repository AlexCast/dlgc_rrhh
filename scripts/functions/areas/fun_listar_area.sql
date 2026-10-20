DELIMITER //

CREATE OR REPLACE FUNCTION fun_listar_area(
    wid_area INT
) RETURNS BOOLEAN
BEGIN
    DECLARE v_id_area INT;
    DECLARE v_nombre_area VARCHAR(30);
    DECLARE v_not_found BOOLEAN DEFAULT FALSE;
    
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET v_not_found = TRUE;
    DECLARE EXIT HANDLER FOR SQLEXCEPTION RETURN FALSE;

    IF wid_area IS NULL OR wid_area <= 0 THEN
        RETURN FALSE;
    END IF;

    SELECT id_area, nombre_area
    INTO v_id_area, v_nombre_area
    FROM t_areas
    WHERE id_area = wid_area
      AND fec_delete IS NULL
    LIMIT 1;

    IF v_not_found THEN
        RETURN FALSE;
    END IF;

    RETURN TRUE;
END //

DELIMITER ;