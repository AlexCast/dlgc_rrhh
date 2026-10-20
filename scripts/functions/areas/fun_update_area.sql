DELIMITER //

CREATE OR REPLACE FUNCTION fun_update_area(
    wid_area INT,
    wnombre_area VARCHAR(30)
) RETURNS VARCHAR(255)
BEGIN
    DECLARE EXIT HANDLER FOR SQLSTATE '23000' RETURN 'El registro ya existe.. Trabaje bien o ábrase llaveee';
    DECLARE EXIT HANDLER FOR SQLEXCEPTION RETURN 'Esta vaina se totió.. Y no fue de la risa.. Déjeme trabajar';

    IF wid_area IS NULL OR wid_area <= 0 THEN
        RETURN 'el id del área no puede ser nulo';
    END IF;
    
    IF wnombre_area IS NULL OR LENGTH(wnombre_area) < 3 THEN
        RETURN 'El nombre del área debe tener al menos 3 caracteres y no puede ser nulo.';
    END IF;

    UPDATE t_areas
    SET nombre_area = wnombre_area,
        usr_update = COALESCE(@app_current_user, CURRENT_USER()),
        fec_update = CURRENT_TIMESTAMP
    WHERE id_area = wid_area
      AND fec_delete IS NULL;
            
    IF ROW_COUNT() > 0 THEN
        RETURN 'Esta vaina funcionó.. Somos duros en ADSO';
    ELSE
        RETURN 'Eche pa la primaria porque de esto no va a comer... Sorry';
    END IF;
END //

DELIMITER ;