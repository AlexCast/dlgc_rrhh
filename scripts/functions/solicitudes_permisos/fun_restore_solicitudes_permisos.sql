DELIMITER //

CREATE OR REPLACE FUNCTION fun_restore_solicitudes_permisos(
    wid_permiso INT
) RETURNS BOOLEAN
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION RETURN FALSE;

    UPDATE t_solicitudes_permisos
    SET fec_delete = NULL,
        usr_delete = NULL
    WHERE id_permiso = wid_permiso
      AND fec_delete IS NOT NULL;

    IF ROW_COUNT() > 0 THEN
        RETURN TRUE;
    END IF;

    RETURN FALSE;
END //

DELIMITER ;