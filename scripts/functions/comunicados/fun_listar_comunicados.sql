DELIMITER //

CREATE OR REPLACE FUNCTION fun_listar_comunicados(
    wid_comunicado INT
) RETURNS BOOLEAN
BEGIN
    DECLARE v_id_comunicado INT;
    DECLARE v_titulo VARCHAR(100);
    DECLARE v_categoria VARCHAR(30);
    DECLARE v_not_found BOOLEAN DEFAULT FALSE;

    DECLARE CONTINUE HANDLER FOR NOT FOUND SET v_not_found = TRUE;
    DECLARE EXIT HANDLER FOR SQLEXCEPTION RETURN FALSE;

    IF wid_comunicado IS NULL OR wid_comunicado <= 0 THEN
        RETURN FALSE;
    END IF;

    SELECT id_comunicado, titulo, categoria
    INTO v_id_comunicado, v_titulo, v_categoria
    FROM t_comunicados
    WHERE id_comunicado = wid_comunicado
      AND fec_delete IS NULL
    LIMIT 1;

    IF v_not_found THEN
        RETURN FALSE;
    END IF;

    RETURN TRUE;
END //

DELIMITER ;