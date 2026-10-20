DELIMITER //

CREATE OR REPLACE FUNCTION fun_restore_arl(
    wid_arl INT
) RETURNS BOOLEAN
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION RETURN FALSE;

    UPDATE t_arl
    SET fec_delete = NULL, 
        usr_delete = NULL
    WHERE id_arl = wid_arl 
      AND fec_delete IS NOT NULL;

    IF ROW_COUNT() > 0 THEN
        RETURN TRUE;
    ELSE
        RETURN FALSE;
    END IF;
END //

DELIMITER ;