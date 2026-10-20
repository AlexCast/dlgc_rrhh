DELIMITER //

CREATE OR REPLACE FUNCTION fun_softdelete_afiliaciones_empleados(
    wid_usuario VARCHAR(20),
    wid_eps INT,
    wid_arl INT,
    wid_caja INT,
    wid_pension INT
) RETURNS BOOLEAN
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION RETURN FALSE;

    UPDATE t_afiliaciones_empleados
    SET fec_delete = CURRENT_TIMESTAMP,
        usr_delete = COALESCE(@app_current_user, CURRENT_USER())
    WHERE id_usuario = wid_usuario
      AND id_eps = wid_eps
      AND id_arl = wid_arl
      AND id_caja = wid_caja
      AND id_pension = wid_pension
      AND fec_delete IS NULL;

    IF ROW_COUNT() > 0 THEN
        RETURN TRUE;
    END IF;

    RETURN FALSE;
END //

DELIMITER ;