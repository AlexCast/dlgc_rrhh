DELIMITER //

CREATE OR REPLACE FUNCTION fun_softdelete_usuarios(
    wid_usuario VARCHAR(20)
) RETURNS BOOLEAN
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION RETURN FALSE;

    UPDATE t_usuarios
    SET fec_delete = CURRENT_TIMESTAMP,
        usr_delete = COALESCE(@app_current_user, CURRENT_USER())
    WHERE id_usuario = wid_usuario
      AND fec_delete IS NULL;

    IF ROW_COUNT() > 0 THEN
        RETURN TRUE;
    END IF;
    
    RETURN FALSE;
END //

DELIMITER ;