DELIMITER //

CREATE OR REPLACE FUNCTION fun_restore_soat(
    wid_soat INT
) RETURNS BOOLEAN
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION RETURN FALSE;

    UPDATE t_soat
    SET fec_delete = NULL, 
        usr_delete = NULL
    WHERE id_soat = wid_soat 
      AND fec_delete IS NOT NULL;

    IF ROW_COUNT() > 0 THEN
        RETURN TRUE;
    ELSE
        RETURN FALSE;
    END IF;
END //

DELIMITER ;