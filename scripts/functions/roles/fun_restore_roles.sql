DELIMITER //

CREATE OR REPLACE FUNCTION fun_restore_roles(
    wid_rol INT
) RETURNS BOOLEAN
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION RETURN FALSE;

    UPDATE t_roles
    SET fec_delete = NULL,
        usr_delete = NULL
    WHERE id_rol = wid_rol
      AND fec_delete IS NOT NULL;

    IF ROW_COUNT() > 0 THEN
        RETURN TRUE;
    END IF;

    RETURN FALSE;
END //

DELIMITER ;