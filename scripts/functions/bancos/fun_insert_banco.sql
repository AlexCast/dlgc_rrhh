DELIMITER //

CREATE OR REPLACE FUNCTION fun_insert_banco(
    wnombre_banco VARCHAR(50)
) RETURNS VARCHAR(255)
BEGIN
    DECLARE EXIT HANDLER FOR SQLSTATE '23000' RETURN 'El banco ya existe.';
    DECLARE EXIT HANDLER FOR SQLEXCEPTION RETURN 'Ocurrió un error al insertar el banco.';

    IF wnombre_banco IS NULL OR LENGTH(TRIM(wnombre_banco)) < 3 THEN
        RETURN 'El nombre del banco debe tener al menos 3 caracteres.';
    END IF;

    INSERT INTO t_bancos (
        nombre_banco,
        usr_insert,
        fec_insert
    )
    VALUES (
        TRIM(wnombre_banco),
        COALESCE(@app_current_user, CURRENT_USER()),
        CURRENT_TIMESTAMP
    );

    RETURN 'Banco insertado correctamente.';
END //

DELIMITER ;