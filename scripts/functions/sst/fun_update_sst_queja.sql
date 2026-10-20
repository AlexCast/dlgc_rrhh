DELIMITER //

CREATE OR REPLACE FUNCTION fun_update_sst_queja(
    wid_queja INT,
    wid_usuario VARCHAR(20),
    wtipo_peticion VARCHAR(30),
    wasunto VARCHAR(150),
    wdescripcion TEXT
) RETURNS VARCHAR(255)
BEGIN
    DECLARE v_estado VARCHAR(20);
    DECLARE v_fec_insert DATETIME;
    DECLARE v_not_found BOOLEAN DEFAULT FALSE;

    DECLARE CONTINUE HANDLER FOR NOT FOUND SET v_not_found = TRUE;
    DECLARE EXIT HANDLER FOR SQLEXCEPTION RETURN 'El tipo de petición no es válido o ocurrió un error al actualizar.';

    IF wid_queja IS NULL OR wid_queja <= 0 THEN
        RETURN 'El ID de la queja no es válido.';
    END IF;

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

    SELECT estado, fec_insert
    INTO v_estado, v_fec_insert
    FROM t_sst_buzon_quejas
    WHERE id_queja = wid_queja
      AND id_usuario = wid_usuario
      AND fec_delete IS NULL
    LIMIT 1;

    IF v_not_found THEN
        RETURN 'No se encontró la queja para actualizar.';
    END IF;

    IF v_estado NOT IN ('PENDIENTE') THEN
        RETURN 'Solo se puede editar una queja que esté PENDIENTE.';
    END IF;

    -- Validación de intervalo adaptada a MariaDB
    IF CURRENT_TIMESTAMP > DATE_ADD(v_fec_insert, INTERVAL 5 MINUTE) THEN
        RETURN 'El tiempo para editar la queja ha vencido (5 minutos).';
    END IF;

    UPDATE t_sst_buzon_quejas
    SET tipo_peticion = UPPER(TRIM(wtipo_peticion)),
        asunto = TRIM(wasunto),
        descripcion = TRIM(wdescripcion),
        usr_update = COALESCE(@app_current_user, CURRENT_USER()),
        fec_update = CURRENT_TIMESTAMP
    WHERE id_queja = wid_queja
      AND id_usuario = wid_usuario
      AND estado = 'PENDIENTE';

    RETURN 'Queja / sugerencia actualizada correctamente.';
END //

DELIMITER ;