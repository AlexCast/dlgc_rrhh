DELIMITER //

CREATE OR REPLACE FUNCTION fun_softdelete_permisos_usuarios(
    wid_usuario VARCHAR(20),
    wid_operacion INT
) RETURNS BOOLEAN
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION RETURN FALSE;

    UPDATE t_usuarios_operaciones
    SET fec_delete = CURRENT_TIMESTAMP,
        usr_delete = COALESCE(@app_current_user, CURRENT_USER())
    WHERE id_usuario = TRIM(wid_usuario)
      AND id_operacion = wid_operacion
      AND fec_delete IS NULL;

    IF ROW_COUNT() > 0 THEN
        RETURN TRUE;
    END IF;

    RETURN FALSE;
END //

DELIMITER ;