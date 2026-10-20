DELIMITER //

CREATE OR REPLACE FUNCTION fun_update_banco(
    wid_banco INT,
    wnombre_banco VARCHAR(50)
) RETURNS VARCHAR(255)
BEGIN
    DECLARE EXIT HANDLER FOR SQLSTATE '23000' RETURN 'Ya existe un banco con ese nombre.';
    DECLARE EXIT HANDLER FOR SQLEXCEPTION RETURN 'Ocurrió un error al actualizar el banco.';

    IF wid_banco IS NULL OR wid_banco <= 0 THEN
        RETURN 'El ID del banco no es válido.';
    END IF;

    IF wnombre_banco IS NULL OR LENGTH(TRIM(wnombre_banco)) < 3 THEN
        RETURN 'El nombre del banco debe tener al menos 3 caracteres.';
    END IF;

    UPDATE t_bancos
    SET nombre_banco = TRIM(wnombre_banco),
        usr_update = COALESCE(@app_current_user, CURRENT_USER()),
        fec_update = CURRENT_TIMESTAMP
    WHERE id_banco = wid_banco
      AND fec_delete IS NULL;

    IF ROW_COUNT() > 0 THEN
        RETURN 'Banco actualizado correctamente.';
    END IF;

    RETURN 'No se encontró el banco activo para actualizar.';
END //

DELIMITER ;