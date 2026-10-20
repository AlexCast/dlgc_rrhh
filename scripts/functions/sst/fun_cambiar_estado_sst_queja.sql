DELIMITER //

CREATE OR REPLACE FUNCTION fun_cambiar_estado_sst_queja(
    wid_queja INT,
    wid_encargado VARCHAR(20),
    westado VARCHAR(20),
    wrespuesta TEXT
) RETURNS VARCHAR(255)
BEGIN
    DECLARE v_estado_actual VARCHAR(20);
    DECLARE v_not_found BOOLEAN DEFAULT FALSE;

    DECLARE CONTINUE HANDLER FOR NOT FOUND SET v_not_found = TRUE;
    DECLARE EXIT HANDLER FOR SQLEXCEPTION RETURN 'El estado indicado no es válido o ocurrió un error.';

    IF wid_queja IS NULL OR wid_queja <= 0 THEN
        RETURN 'El ID de la queja no es válido.';
    END IF;

    IF wid_encargado IS NULL OR wid_encargado = '' THEN
        RETURN 'El encargado es obligatorio.';
    END IF;

    IF westado IS NULL OR TRIM(westado) = '' THEN
        RETURN 'El estado es obligatorio.';
    END IF;

    SELECT estado
    INTO v_estado_actual
    FROM t_sst_buzon_quejas
    WHERE id_queja = wid_queja
      AND fec_delete IS NULL
    LIMIT 1;

    IF v_not_found THEN
        RETURN 'No se encontró la queja.';
    END IF;

    IF v_estado_actual = 'CANCELADO_USUARIO' THEN
        RETURN 'No se puede modificar una queja cancelada por el usuario.';
    END IF;

    UPDATE t_sst_buzon_quejas
    SET estado = UPPER(TRIM(westado)),
        id_encargado = wid_encargado,
        respuesta = CASE WHEN wrespuesta IS NULL OR TRIM(wrespuesta) = '' THEN respuesta ELSE TRIM(wrespuesta) END,
        fec_respuesta = CASE WHEN UPPER(TRIM(westado)) IN ('RESUELTO') THEN CURRENT_TIMESTAMP ELSE fec_respuesta END,
        cancelado_por = CASE WHEN UPPER(TRIM(westado)) = 'CANCELADO_ENCARGADO' THEN wid_encargado ELSE cancelado_por END,
        fec_cancelacion = CASE WHEN UPPER(TRIM(westado)) = 'CANCELADO_ENCARGADO' THEN CURRENT_TIMESTAMP ELSE fec_cancelacion END
    WHERE id_queja = wid_queja;

    RETURN 'Estado de la queja / sugerencia actualizado correctamente.';
END //

DELIMITER ;