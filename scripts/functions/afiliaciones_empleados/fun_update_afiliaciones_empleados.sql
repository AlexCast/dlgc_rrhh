DELIMITER //

CREATE OR REPLACE FUNCTION fun_update_afiliaciones_empleados(
    wid_usuario VARCHAR(20),
    wid_eps INT,
    wid_arl INT,
    wid_caja INT,
    wid_pension INT,
    wid_cesantia INT
) RETURNS VARCHAR(255)
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION RETURN 'Esta vaina se totió.. Y no fue de la risa.. Déjeme trabajar';

    UPDATE t_afiliaciones_empleados
    SET id_cesantia = wid_cesantia,
        usr_update = COALESCE(@app_current_user, CURRENT_USER()),
        fec_update = CURRENT_TIMESTAMP
    WHERE id_usuario = wid_usuario
      AND id_eps = wid_eps
      AND id_arl = wid_arl
      AND id_caja = wid_caja
      AND id_pension = wid_pension
      AND fec_delete IS NULL;
            
    IF ROW_COUNT() > 0 THEN
        RETURN 'Esta vaina funcionó.. Somos duros en ADSO';
    ELSE
        RETURN 'Eche pa la primaria porque de esto no va a comer... Sorry';
    END IF;
END //

DELIMITER ;