DELIMITER //

CREATE OR REPLACE FUNCTION fun_update_soat(
    wid_soat INT,
    wnombre_soat VARCHAR(50)
) RETURNS VARCHAR(255)
BEGIN
    DECLARE EXIT HANDLER FOR SQLSTATE '23000' RETURN 'El registro ya existe.. Trabaje bien o ábrase llaveee';
    DECLARE EXIT HANDLER FOR SQLEXCEPTION RETURN 'Esta vaina se totió.. Y no fue de la risa.. Déjeme trabajar';

    IF wid_soat IS NULL OR wid_soat <= 0 THEN
        RETURN 'el id de la entidad de SOAT no puede ser nulo';
    END IF;
    
    IF wnombre_soat IS NULL OR LENGTH(wnombre_soat) < 3 THEN
        RETURN 'El nombre de la entidad de SOAT debe tener al menos 3 caracteres y no puede ser nulo.';
    END IF;

    UPDATE t_soat
    SET nombre_soat = wnombre_soat,
        usr_update = COALESCE(@app_current_user, CURRENT_USER()),
        fec_update = CURRENT_TIMESTAMP
    WHERE id_soat = wid_soat
      AND fec_delete IS NULL;
            
    IF ROW_COUNT() > 0 THEN
        RETURN 'Esta vaina funcionó.. Somos duros en ADSO';
    ELSE
        RETURN 'Eche pa la primaria porque de esto no va a comer... Sorry';
    END IF;
END //

DELIMITER ;