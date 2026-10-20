DELIMITER //

CREATE OR REPLACE FUNCTION fun_softdelete_tipos_permisos(
    wid_tipo_permiso INT
) RETURNS BOOLEAN
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION RETURN FALSE;

    UPDATE t_tipos_permisos
    SET fec_delete = CURRENT_TIMESTAMP,
        usr_delete = COALESCE(@app_current_user, CURRENT_USER())
    WHERE id_tipo_permiso = wid_tipo_permiso
      AND fec_delete IS NULL;

    IF ROW_COUNT() > 0 THEN
        RETURN TRUE;
    END IF;

    RETURN FALSE;
END //

DELIMITER ;