DELIMITER //

CREATE OR REPLACE FUNCTION fun_softdelete_contratos_empleados(
    wid_contrato INT
) RETURNS BOOLEAN
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION RETURN FALSE;

    UPDATE t_contratos_empleados
    SET fec_delete = CURRENT_TIMESTAMP,
        usr_delete = COALESCE(@app_current_user, CURRENT_USER())
    WHERE id_contrato = wid_contrato
      AND fec_delete IS NULL;

    IF ROW_COUNT() > 0 THEN
        RETURN TRUE;
    END IF;

    RETURN FALSE;
END //

DELIMITER ;