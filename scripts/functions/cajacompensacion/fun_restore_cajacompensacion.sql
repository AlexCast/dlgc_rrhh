DELIMITER //

CREATE OR REPLACE FUNCTION fun_restore_cajacompensacion(
    wid_caja INT
) RETURNS BOOLEAN
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION RETURN FALSE;

    UPDATE t_caja_compensacion
    SET fec_delete = NULL, 
        usr_delete = NULL
    WHERE id_caja = wid_caja 
      AND fec_delete IS NOT NULL;

    IF ROW_COUNT() > 0 THEN
        RETURN TRUE;
    ELSE
        RETURN FALSE;
    END IF;
END //

DELIMITER ;