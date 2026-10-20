DELIMITER //

CREATE OR REPLACE FUNCTION fun_listar_roles(
    wid_rol INT
) RETURNS BOOLEAN
BEGIN
    DECLARE v_id_rol INT;
    DECLARE v_nombre_rol VARCHAR(50);
    DECLARE v_not_found BOOLEAN DEFAULT FALSE;

    DECLARE CONTINUE HANDLER FOR NOT FOUND SET v_not_found = TRUE;
    DECLARE EXIT HANDLER FOR SQLEXCEPTION RETURN FALSE;

    IF wid_rol IS NULL OR wid_rol <= 0 THEN
        RETURN FALSE;
    END IF;

    SELECT id_rol, nombre_rol
    INTO v_id_rol, v_nombre_rol
    FROM t_roles
    WHERE id_rol = wid_rol
      AND fec_delete IS NULL
    LIMIT 1;

    IF v_not_found THEN
        RETURN FALSE;
    END IF;

    RETURN TRUE;
END //

DELIMITER ;