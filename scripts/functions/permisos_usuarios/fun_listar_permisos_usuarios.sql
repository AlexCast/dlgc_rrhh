DELIMITER //

CREATE OR REPLACE FUNCTION fun_listar_permisos_usuarios(
    wid_usuario VARCHAR(20),
    wid_operacion INT
) RETURNS BOOLEAN
BEGIN
    DECLARE v_id_usuario VARCHAR(20);
    DECLARE v_id_operacion INT;
    DECLARE v_not_found BOOLEAN DEFAULT FALSE;

    DECLARE CONTINUE HANDLER FOR NOT FOUND SET v_not_found = TRUE;
    DECLARE EXIT HANDLER FOR SQLEXCEPTION RETURN FALSE;

    IF wid_usuario IS NULL OR TRIM(wid_usuario) = '' OR wid_operacion IS NULL OR wid_operacion <= 0 THEN
        RETURN FALSE;
    END IF;

    SELECT id_usuario, id_operacion
    INTO v_id_usuario, v_id_operacion
    FROM t_usuarios_operaciones
    WHERE id_usuario = TRIM(wid_usuario)
      AND id_operacion = wid_operacion
      AND fec_delete IS NULL
    LIMIT 1;

    IF v_not_found THEN
        RETURN FALSE;
    END IF;

    RETURN TRUE;
END //

DELIMITER ;