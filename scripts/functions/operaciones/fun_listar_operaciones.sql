DELIMITER //

CREATE OR REPLACE FUNCTION fun_listar_operaciones(
    wid_operacion INT
) RETURNS BOOLEAN
BEGIN
    DECLARE v_id_operacion INT;
    DECLARE v_id_modulo INT;
    DECLARE v_nombre_operacion VARCHAR(50);
    DECLARE v_not_found BOOLEAN DEFAULT FALSE;

    DECLARE CONTINUE HANDLER FOR NOT FOUND SET v_not_found = TRUE;
    DECLARE EXIT HANDLER FOR SQLEXCEPTION RETURN FALSE;

    IF wid_operacion IS NULL OR wid_operacion <= 0 THEN
        RETURN FALSE;
    END IF;

    SELECT id_operacion, id_modulo, nombre_operacion
    INTO v_id_operacion, v_id_modulo, v_nombre_operacion
    FROM t_operaciones
    WHERE id_operacion = wid_operacion
      AND fec_delete IS NULL
    LIMIT 1;

    IF v_not_found THEN
        RETURN FALSE;
    END IF;

    RETURN TRUE;
END //

DELIMITER ;