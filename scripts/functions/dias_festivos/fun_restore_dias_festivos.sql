DELIMITER //

CREATE OR REPLACE FUNCTION fun_restore_dias_festivos(
    wid_festivo INT
) RETURNS BOOLEAN
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION RETURN FALSE;

    UPDATE t_dias_festivos
    SET fec_delete = NULL,
        usr_delete = NULL
    WHERE id_festivo = wid_festivo
      AND tipo_festivo = 'EMPRESA'
      AND fec_delete IS NOT NULL;

    IF ROW_COUNT() > 0 THEN
        RETURN TRUE;
    END IF;

    RETURN FALSE;
END //

DELIMITER ;