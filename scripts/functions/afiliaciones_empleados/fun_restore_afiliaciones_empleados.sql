DELIMITER //

CREATE OR REPLACE FUNCTION fun_restore_afiliaciones_empleados(
    wid_usuario VARCHAR(20),
    wid_eps INT,
    wid_arl INT,
    wid_caja INT,
    wid_pension INT
) RETURNS BOOLEAN
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION RETURN FALSE;

    UPDATE t_afiliaciones_empleados
    SET fec_delete = NULL, 
        usr_delete = NULL
    WHERE id_usuario = wid_usuario 
      AND id_eps = wid_eps
      AND id_arl = wid_arl
      AND id_caja = wid_caja
      AND id_pension = wid_pension
      AND fec_delete IS NOT NULL;

    IF ROW_COUNT() > 0 THEN
        RETURN TRUE;
    END IF;

    RETURN FALSE;
END //

DELIMITER ;