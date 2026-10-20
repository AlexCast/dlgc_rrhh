DELIMITER //

CREATE OR REPLACE FUNCTION fun_restore_comunicados(
    wid_comunicado INT
) RETURNS BOOLEAN
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION RETURN FALSE;

    UPDATE t_comunicados
    SET fec_delete = NULL,
        usr_delete = NULL
    WHERE id_comunicado = wid_comunicado
      AND fec_delete IS NOT NULL;

    IF ROW_COUNT() > 0 THEN
        RETURN TRUE;
    END IF;

    RETURN FALSE;
END //

DELIMITER ;