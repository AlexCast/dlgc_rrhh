CREATE OR REPLACE FUNCTION fun_listar_sst_quejas_encargado(
    westado t_sst_buzon_quejas.estado%TYPE DEFAULT NULL
) RETURNS TABLE (
    id_queja INT,
    id_usuario VARCHAR,
    nombre_usuario VARCHAR,
    correo_usuario VARCHAR,
    tipo_peticion VARCHAR,
    asunto VARCHAR,
    descripcion TEXT,
    estado VARCHAR,
    respuesta TEXT,
    id_encargado VARCHAR,
    fec_respuesta TIMESTAMP WITHOUT TIME ZONE,
    cancelado_por VARCHAR,
    fec_cancelacion TIMESTAMP WITHOUT TIME ZONE,
    usr_insert VARCHAR,
    fec_insert TIMESTAMP WITHOUT TIME ZONE
) AS
$$
BEGIN
    RETURN QUERY
    SELECT
        q.id_queja,
        q.id_usuario,
        CAST(TRIM(u.primer_nombre || ' ' || COALESCE(u.segundo_nombre, '') || ' ' || u.primer_apellido || ' ' || COALESCE(u.segundo_apellido, '')) AS VARCHAR) AS nombre_usuario,
        u.correo AS correo_usuario,
        q.tipo_peticion,
        q.asunto,
        q.descripcion,
        q.estado,
        q.respuesta,
        q.id_encargado,
        q.fec_respuesta,
        q.cancelado_por,
        q.fec_cancelacion,
        q.usr_insert,
        q.fec_insert
    FROM t_sst_buzon_quejas q
    INNER JOIN t_usuarios u ON u.id_usuario = q.id_usuario
    WHERE q.fec_delete IS NULL
      AND q.estado NOT IN ('CANCELADO_USUARIO')
      AND (westado IS NULL OR TRIM(westado) = '' OR q.estado = UPPER(TRIM(westado)))
    ORDER BY q.fec_insert DESC;
END;
$$
LANGUAGE PLPGSQL;
