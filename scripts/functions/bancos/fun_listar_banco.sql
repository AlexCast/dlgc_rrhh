DELIMITER //

CREATE OR REPLACE FUNCTION fun_listar_banco(
    wid_banco INT
) RETURNS BOOLEAN
BEGIN
    DECLARE v_id_banco INT;
    DECLARE v_nombre_banco VARCHAR(50);
    DECLARE v_not_found BOOLEAN DEFAULT FALSE;

    DECLARE CONTINUE HANDLER FOR NOT FOUND SET v_not_found = TRUE;
    DECLARE EXIT HANDLER FOR SQLEXCEPTION RETURN FALSE;

    IF wid_banco IS NULL OR wid_banco <= 0 THEN
        RETURN FALSE;
    END IF;

    SELECT id_banco, nombre_banco
    INTO v_id_banco, v_nombre_banco
    FROM t_bancos
    WHERE id_banco = wid_banco
      AND fec_delete IS NULL
    LIMIT 1;

    IF v_not_found THEN
        RETURN FALSE;
    END IF;

    RETURN TRUE;
END //

DELIMITER ;