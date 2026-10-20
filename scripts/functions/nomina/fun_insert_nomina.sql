DELIMITER //

CREATE OR REPLACE FUNCTION fun_insert_nomina(
    wid_usuario VARCHAR(20),
    wid_banco INT,
    wnum_cuenta VARCHAR(20),
    wsalario DECIMAL(10,2)
) RETURNS VARCHAR(255)
BEGIN
    DECLARE EXIT HANDLER FOR SQLSTATE '23000' RETURN 'El registro ya existe.. Trabaje bien o ábrase llaveee';
    DECLARE EXIT HANDLER FOR SQLEXCEPTION RETURN 'Esta vaina se totió.. Y no fue de la risa.. Déjeme trabajar';

    IF wid_usuario IS NULL OR wid_usuario = '' THEN
        RETURN 'El id_usuario no puede ser nulo.';
    END IF;
    IF wid_banco IS NULL OR wid_banco <= 0 THEN
        RETURN 'El id_banco no puede ser nulo.';
    END IF;
    IF wnum_cuenta IS NULL OR wnum_cuenta = '' THEN
        RETURN 'El número de cuenta no puede ser nulo.';
    END IF;
    IF wsalario IS NULL OR wsalario < 0 THEN
        RETURN 'El salario no puede ser nulo o negativo.';
    END IF;

    INSERT INTO t_nomina (
        id_usuario,
        id_banco,
        num_cuenta,
        salario,
        usr_insert,
        fec_insert
    )
    VALUES (
        wid_usuario,
        wid_banco,
        wnum_cuenta,
        wsalario,
        COALESCE(@app_current_user, CURRENT_USER()),
        CURRENT_TIMESTAMP
    );
    
    RETURN 'Esta vaina funcionó.. Somos duros en ADSO';
END //

DELIMITER ;