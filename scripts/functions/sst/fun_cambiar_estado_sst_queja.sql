CREATE OR REPLACE FUNCTION fun_cambiar_estado_sst_queja(
    wid_queja t_sst_buzon_quejas.id_queja%TYPE,
    wid_encargado t_sst_buzon_quejas.id_encargado%TYPE,
    westado t_sst_buzon_quejas.estado%TYPE,
    wrespuesta t_sst_buzon_quejas.respuesta%TYPE DEFAULT NULL
) RETURNS VARCHAR AS
$$
DECLARE
    v_estado_actual VARCHAR;
BEGIN
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
       AND fec_delete IS NULL;

    IF v_estado_actual IS NULL THEN
        RETURN 'No se encontró la queja.';
    END IF;

    IF v_estado_actual = 'CANCELADO_USUARIO' THEN
        RETURN 'No se puede modificar una queja cancelada por el usuario.';
    END IF;

    UPDATE t_sst_buzon_quejas
    SET estado = UPPER(TRIM(westado)),
        id_encargado = wid_encargado,
        respuesta = CASE WHEN wrespuesta IS NULL OR TRIM(wrespuesta) = '' THEN respuesta ELSE TRIM(wrespuesta) END,
        fec_respuesta = CASE WHEN westado IN ('RESUELTO') THEN CURRENT_TIMESTAMP ELSE fec_respuesta END,
        cancelado_por = CASE WHEN UPPER(TRIM(westado)) = 'CANCELADO_ENCARGADO' THEN wid_encargado ELSE cancelado_por END,
        fec_cancelacion = CASE WHEN UPPER(TRIM(westado)) = 'CANCELADO_ENCARGADO' THEN CURRENT_TIMESTAMP ELSE fec_cancelacion END
    WHERE id_queja = wid_queja;

    RETURN 'Estado de la queja / sugerencia actualizado correctamente.';

EXCEPTION
    WHEN SQLSTATE '23514' THEN
        RETURN 'El estado indicado no es válido.';
    WHEN OTHERS THEN
        RAISE NOTICE 'Error: %', SQLERRM;
        RETURN 'Ocurrió un error al cambiar el estado de la queja / sugerencia.';
END;
$$
LANGUAGE PLPGSQL;
