CREATE OR REPLACE FUNCTION fun_update_sst_queja(
    wid_queja t_sst_buzon_quejas.id_queja%TYPE,
    wid_usuario t_sst_buzon_quejas.id_usuario%TYPE,
    wtipo_peticion t_sst_buzon_quejas.tipo_peticion%TYPE,
    wasunto t_sst_buzon_quejas.asunto%TYPE,
    wdescripcion t_sst_buzon_quejas.descripcion%TYPE
) RETURNS VARCHAR AS
$$
DECLARE
    v_fec_insert TIMESTAMP WITHOUT TIME ZONE;
    v_estado VARCHAR;
BEGIN
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
       AND fec_delete IS NULL;

    IF v_estado IS NULL THEN
        RETURN 'No se encontró la queja para actualizar.';
    END IF;

    IF v_estado NOT IN ('PENDIENTE') THEN
        RETURN 'Solo se puede editar una queja que esté PENDIENTE.';
    END IF;

    IF CURRENT_TIMESTAMP > (v_fec_insert + INTERVAL '5 minutes') THEN
        RETURN 'El tiempo para editar la queja ha vencido (5 minutos).';
    END IF;

    UPDATE t_sst_buzon_quejas
    SET tipo_peticion = UPPER(TRIM(wtipo_peticion)),
        asunto = TRIM(wasunto),
        descripcion = TRIM(wdescripcion),
        usr_update = COALESCE(NULLIF(current_setting('app.current_user', true), ''), CURRENT_USER),
        fec_update = CURRENT_TIMESTAMP
    WHERE id_queja = wid_queja
      AND id_usuario = wid_usuario
      AND estado = 'PENDIENTE';

    RETURN 'Queja / sugerencia actualizada correctamente.';

EXCEPTION
    WHEN SQLSTATE '23514' THEN
        RETURN 'El tipo de petición indicado no es válido.';
    WHEN OTHERS THEN
        RAISE NOTICE 'Error: %', SQLERRM;
        RETURN 'Ocurrió un error al actualizar la queja / sugerencia.';
END;
$$
LANGUAGE PLPGSQL;
