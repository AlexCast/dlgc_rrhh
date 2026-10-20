DELIMITER //

CREATE OR REPLACE FUNCTION fun_restore_banco(
    wid_banco INT
) RETURNS BOOLEAN
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION RETURN FALSE;

    UPDATE t_bancos
    SET fec_delete = NULL,
        usr_delete = NULL
    WHERE id_banco = wid_banco
      AND fec_delete IS NOT NULL;

    IF ROW_COUNT() > 0 THEN
        RETURN TRUE;
    END IF;

    RETURN FALSE;
END //

DELIMITER ;