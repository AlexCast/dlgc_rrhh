DELIMITER //

CREATE OR REPLACE FUNCTION fun_restore_cesantias(
    wid_cesantia INT
) RETURNS BOOLEAN
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION RETURN FALSE;

    UPDATE t_cesantias
    SET fec_delete = NULL, 
        usr_delete = NULL
    WHERE id_cesantia = wid_cesantia 
      AND fec_delete IS NOT NULL;

    IF ROW_COUNT() > 0 THEN
        RETURN TRUE;
    ELSE
        RETURN FALSE;
    END IF;
END //

DELIMITER ;