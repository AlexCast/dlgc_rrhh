DELIMITER //

CREATE OR REPLACE FUNCTION fun_insert_sst_queja(
    wid_usuario VARCHAR(20),
    wtipo_peticion VARCHAR(30),
    wasunto VARCHAR(150),
    wdescripcion TEXT
) RETURNS VARCHAR(255)
BEGIN
    -- Maneja violación de llave foránea (usuario no existe) y checks en MariaDB
    DECLARE EXIT HANDLER FOR SQLEXCEPTION RETURN 'El usuario/petición no es válido o ocurrió un error al enviar.';

    IF wid_usuario IS NULL OR wid_usuario = '' THEN
        RETURN 'El usuario es obligatorio.';
    END IF;

    IF wtipo_peticion IS NULL OR TRIM(wtipo_peticion) = '' THEN
        RETURN 'El tipo de petición es obligatorio.';
    END IF;

    IF wasunto IS NULL OR LENGTH(TRIM(wasunto)) < 5 THEN
        RETURN 'El asunto debe tener al menos 5 caracteres.';
    END IF;

    IF wdescripcion IS NULL OR LENGTH(TRIM(wdescripcion)) < 10 THEN
        RETURN 'La descripción debe tener al menos 10 caracteres.';
    END IF;

    INSERT INTO t_sst_buzon_quejas (
        id_usuario,
        tipo_peticion,
        asunto,
        descripcion,
        estado,
        usr_insert,
        fec_insert
    )
    VALUES (
        wid_usuario,
        UPPER(TRIM(wtipo_peticion)),
        TRIM(wasunto),
        TRIM(wdescripcion),
        'PENDIENTE',
        COALESCE(@app_current_user, CURRENT_USER()),
        CURRENT_TIMESTAMP
    );

    RETURN 'Queja / sugerencia enviada correctamente.';
END //

DELIMITER ;