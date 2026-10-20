DELIMITER //

CREATE OR REPLACE FUNCTION fun_cancelar_sst_queja_usuario(
    wid_queja INT,
    wid_usuario VARCHAR(20)
) RETURNS VARCHAR(255)
BEGIN
    DECLARE v_estado VARCHAR(20);
    DECLARE v_not_found BOOLEAN DEFAULT FALSE;

    DECLARE CONTINUE HANDLER FOR NOT FOUND SET v_not_found = TRUE;
    DECLARE EXIT HANDLER FOR SQLEXCEPTION RETURN 'Ocurrió un error al cancelar la queja / sugerencia.';

    IF wid_queja IS NULL OR wid_queja <= 0 THEN
        RETURN 'El ID de la queja no es válido.';
    END IF;

    SELECT estado
    INTO v_estado
    FROM t_sst_buzon_quejas
    WHERE id_queja = wid_queja
      AND id_usuario = wid_usuario
      AND fec_delete IS NULL
    LIMIT 1;

    IF v_not_found THEN
        RETURN 'No se encontró la queja para cancelar.';
    END IF;

    IF v_estado IN ('RESUELTO', 'CANCELADO_USUARIO', 'CANCELADO_ENCARGADO') THEN
        RETURN 'La queja ya no se puede cancelar.';
    END IF;

    UPDATE t_sst_buzon_quejas
    SET estado = 'CANCELADO_USUARIO',
        cancelado_por = wid_usuario,
        fec_cancelacion = CURRENT_TIMESTAMP
    WHERE id_queja = wid_queja
      AND id_usuario = wid_usuario;

    RETURN 'Queja / sugerencia cancelada correctamente.';
END //

DELIMITER ;