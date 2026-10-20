DELIMITER //

CREATE OR REPLACE FUNCTION fun_restore_empleados(
    wid_usuario VARCHAR(20)
) RETURNS BOOLEAN
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION RETURN FALSE;

    UPDATE t_empleados
    SET fec_delete = NULL,
        usr_delete = NULL
    WHERE id_usuario = wid_usuario
      AND fec_delete IS NOT NULL;

    IF ROW_COUNT() > 0 THEN
        RETURN TRUE;
    END IF;
    
    RETURN FALSE;
END //

DELIMITER ;