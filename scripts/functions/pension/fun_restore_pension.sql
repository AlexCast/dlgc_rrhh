DELIMITER //

CREATE OR REPLACE FUNCTION fun_restore_pension(
    wid_pension INT
) RETURNS BOOLEAN
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION RETURN FALSE;

    UPDATE t_pension
    SET fec_delete = NULL, 
        usr_delete = NULL
    WHERE id_pension = wid_pension 
      AND fec_delete IS NOT NULL;

    IF ROW_COUNT() > 0 THEN
        RETURN TRUE;
    ELSE
        RETURN FALSE;
    END IF;
END //

DELIMITER ;