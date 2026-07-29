CREATE OR REPLACE FUNCTION fun_insert_sst_queja(
    wid_usuario t_sst_buzon_quejas.id_usuario%TYPE,
    wtipo_peticion t_sst_buzon_quejas.tipo_peticion%TYPE,
    wasunto t_sst_buzon_quejas.asunto%TYPE,
    wdescripcion t_sst_buzon_quejas.descripcion%TYPE
) RETURNS VARCHAR AS
$$
BEGIN
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
        COALESCE(NULLIF(current_setting('app.current_user', true), ''), CURRENT_USER),
        CURRENT_TIMESTAMP
    );

    RETURN 'Queja / sugerencia enviada correctamente.';

EXCEPTION
    WHEN SQLSTATE '23503' THEN
        RETURN 'No existe el usuario indicado.';
    WHEN SQLSTATE '23514' THEN
        RETURN 'El tipo de petición o estado no es válido.';
    WHEN OTHERS THEN
        RAISE NOTICE 'Error: %', SQLERRM;
        RETURN 'Ocurrió un error al enviar la queja / sugerencia.';
END;
$$
LANGUAGE PLPGSQL;
