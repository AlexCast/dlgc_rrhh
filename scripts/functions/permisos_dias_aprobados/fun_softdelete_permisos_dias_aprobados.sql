DELIMITER //

CREATE OR REPLACE FUNCTION fun_softdelete_permisos_dias_aprobados(
    wid_dia_permiso INT
) RETURNS BOOLEAN
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION RETURN FALSE;

    UPDATE t_permisos_dias_aprobados
    SET fec_delete = CURRENT_TIMESTAMP,
        usr_delete = COALESCE(@app_current_user, CURRENT_USER())
    WHERE id_dia_permiso = wid_dia_permiso
      AND fec_delete IS NULL;

    IF ROW_COUNT() > 0 THEN
        RETURN TRUE;
    END IF;

    RETURN FALSE;
END //

DELIMITER ;