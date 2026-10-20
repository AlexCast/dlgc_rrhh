DELIMITER //

CREATE OR REPLACE FUNCTION fun_restore_tipos_permisos(
    wid_tipo_permiso INT
) RETURNS BOOLEAN
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION RETURN FALSE;

    UPDATE t_tipos_permisos
    SET fec_delete = NULL,
        usr_delete = NULL
    WHERE id_tipo_permiso = wid_tipo_permiso
      AND fec_delete IS NOT NULL;

    IF ROW_COUNT() > 0 THEN
        RETURN TRUE;
    END IF;

    RETURN FALSE;
END //

DELIMITER ;