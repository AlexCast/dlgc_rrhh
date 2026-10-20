DELIMITER //

CREATE OR REPLACE FUNCTION fun_restore_permisos_dias_aprobados(
    wid_dia_permiso INT
) RETURNS BOOLEAN
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION RETURN FALSE;

    UPDATE t_permisos_dias_aprobados
    SET fec_delete = NULL,
        usr_delete = NULL
    WHERE id_dia_permiso = wid_dia_permiso
      AND fec_delete IS NOT NULL;

    IF ROW_COUNT() > 0 THEN
        RETURN TRUE;
    END IF;

    RETURN FALSE;
END //

DELIMITER ;