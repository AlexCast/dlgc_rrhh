DELIMITER //

CREATE OR REPLACE FUNCTION fun_insert_soat(
    wnombre_soat VARCHAR(50)
) RETURNS VARCHAR(255)
BEGIN
    DECLARE EXIT HANDLER FOR SQLSTATE '23000' RETURN 'El registro ya existe.. Trabaje bien o ábrase llaveee';
    DECLARE EXIT HANDLER FOR SQLEXCEPTION RETURN 'Esta vaina se totió.. Y no fue de la risa.. Déjeme trabajar';

    IF wnombre_soat IS NULL OR LENGTH(wnombre_soat) < 3 THEN
        RETURN 'El nombre de la entidad de SOAT debe tener al menos 3 caracteres y no puede ser nulo.';
    END IF;

    INSERT INTO t_soat (
        nombre_soat,
        usr_insert,
        fec_insert
    )
    VALUES (
        wnombre_soat,
        COALESCE(@app_current_user, CURRENT_USER()),
        CURRENT_TIMESTAMP
    );
    
    RETURN 'Esta vaina funcionó.. Somos duros en ADSO';
END //

DELIMITER ;