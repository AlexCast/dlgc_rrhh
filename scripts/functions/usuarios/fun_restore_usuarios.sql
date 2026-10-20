DELIMITER //

CREATE OR REPLACE FUNCTION fun_restore_usuarios(
    wid_usuario VARCHAR(20)
) RETURNS BOOLEAN
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION RETURN FALSE;

    UPDATE t_usuarios
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