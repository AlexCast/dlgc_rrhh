DELIMITER //

CREATE OR REPLACE FUNCTION fun_restore_vacaciones_ajustes(
    wid_ajuste INT
) RETURNS BOOLEAN
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION RETURN FALSE;

    UPDATE t_vacaciones_ajustes
    SET fec_delete = NULL,
        usr_delete = NULL
    WHERE id_ajuste = wid_ajuste
      AND fec_delete IS NOT NULL;

    IF ROW_COUNT() > 0 THEN
        RETURN TRUE;
    END IF;

    RETURN FALSE;
END //

DELIMITER ;