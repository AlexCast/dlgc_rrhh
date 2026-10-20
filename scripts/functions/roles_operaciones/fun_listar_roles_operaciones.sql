DELIMITER //

CREATE OR REPLACE FUNCTION fun_listar_roles_operaciones(
    wid_rol INT,
    wid_operacion INT
) RETURNS BOOLEAN
BEGIN
    DECLARE v_id_rol INT;
    DECLARE v_id_operacion INT;
    DECLARE v_not_found BOOLEAN DEFAULT FALSE;

    DECLARE CONTINUE HANDLER FOR NOT FOUND SET v_not_found = TRUE;
    DECLARE EXIT HANDLER FOR SQLEXCEPTION RETURN FALSE;

    IF wid_rol IS NULL OR wid_rol <= 0 OR wid_operacion IS NULL OR wid_operacion <= 0 THEN
        RETURN FALSE;
    END IF;

    SELECT id_rol, id_operacion
    INTO v_id_rol, v_id_operacion
    FROM t_roles_operaciones
    WHERE id_rol = wid_rol
      AND id_operacion = wid_operacion
      AND fec_delete IS NULL
    LIMIT 1;

    IF v_not_found THEN
        RETURN FALSE;
    END IF;

    RETURN TRUE;
END //

DELIMITER ;