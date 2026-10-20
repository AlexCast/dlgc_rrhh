DELIMITER //

CREATE OR REPLACE FUNCTION fun_insert_afiliaciones_empleados(
    wid_usuario VARCHAR(20),
    wid_eps INT,
    wid_arl INT,
    wid_caja INT,
    wid_pension INT,
    wid_cesantia INT
) RETURNS VARCHAR(255)
BEGIN
    DECLARE EXIT HANDLER FOR SQLSTATE '23000' RETURN 'El registro ya existe.. Trabaje bien o ábrase llaveee';
    DECLARE EXIT HANDLER FOR SQLEXCEPTION RETURN 'Esta vaina se totió.. Y no fue de la risa.. Déjeme trabajar';

    IF wid_usuario IS NULL OR wid_usuario = '' THEN
        RETURN 'El id_usuario no puede ser nulo o vacío.';
    END IF;

    INSERT INTO t_afiliaciones_empleados (
        id_usuario,
        id_eps,
        id_arl,
        id_caja,
        id_pension,
        id_cesantia,
        usr_insert,
        fec_insert
    )
    VALUES (
        wid_usuario,
        wid_eps,
        wid_arl,
        wid_caja,
        wid_pension,
        wid_cesantia,
        COALESCE(@app_current_user, CURRENT_USER()),
        CURRENT_TIMESTAMP
    );
    
    RETURN 'Esta vaina funcionó.. Somos duros en ADSO';
END //

DELIMITER ;