DELIMITER //

CREATE OR REPLACE FUNCTION fun_marcar_visto_comunicado(
    wid_comunicado INT,
    wid_usuario VARCHAR(20)
) RETURNS BOOLEAN
BEGIN
    DECLARE v_existe INT DEFAULT 0;
    DECLARE EXIT HANDLER FOR SQLEXCEPTION RETURN FALSE;

    IF wid_comunicado IS NULL OR wid_comunicado <= 0 THEN
        RETURN FALSE;
    END IF;

    IF wid_usuario IS NULL OR TRIM(wid_usuario) = '' THEN
        RETURN FALSE;
    END IF;

    SELECT 1 INTO v_existe 
    FROM t_comunicados
    WHERE id_comunicado = wid_comunicado
      AND fec_delete IS NULL
    LIMIT 1;

    IF v_existe = 0 THEN
        RETURN FALSE;
    END IF;

    -- INSERT IGNORE es el equivalente de MariaDB para ON CONFLICT DO NOTHING
    INSERT IGNORE INTO t_comunicados_vistos (id_comunicado, id_usuario, fec_visto)
    VALUES (wid_comunicado, TRIM(wid_usuario), CURRENT_TIMESTAMP);

    RETURN TRUE;
END //

DELIMITER ;