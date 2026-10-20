DELIMITER //

CREATE OR REPLACE FUNCTION fun_update_nomina(
    wid_nomina INT,
    wid_usuario VARCHAR(20),
    wid_banco INT,
    wnum_cuenta VARCHAR(20),
    wsalario DECIMAL(10,2)
) RETURNS VARCHAR(255)
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION RETURN 'Esta vaina se totió.. Y no fue de la risa.. Déjeme trabajar';

    IF wid_nomina IS NULL OR wid_nomina <= 0 THEN
        RETURN 'el id de la nómina no puede ser nulo';
    END IF;

    UPDATE t_nomina
    SET id_usuario = wid_usuario,
        id_banco = wid_banco,
        num_cuenta = wnum_cuenta,
        salario = wsalario,
        usr_update = COALESCE(@app_current_user, CURRENT_USER()),
        fec_update = CURRENT_TIMESTAMP
    WHERE id_nomina = wid_nomina
      AND fec_delete IS NULL;
            
    IF ROW_COUNT() > 0 THEN
        RETURN 'Esta vaina funcionó.. Somos duros en ADSO';
    ELSE
        RETURN 'Eche pa la primaria porque de esto no va a comer... Sorry';
    END IF;
END //

DELIMITER ;