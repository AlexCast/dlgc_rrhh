CREATE OR REPLACE FUNCTION fun_listar_sst_quejas_usuario(
    wid_usuario t_sst_buzon_quejas.id_usuario%TYPE
) RETURNS TABLE (
    id_queja INT,
    id_usuario VARCHAR,
    tipo_peticion VARCHAR,
    asunto VARCHAR,
    descripcion TEXT,
    estado VARCHAR,
    respuesta TEXT,
    id_encargado VARCHAR,
    fec_respuesta TIMESTAMP WITHOUT TIME ZONE,
    cancelado_por VARCHAR,
    fec_cancelacion TIMESTAMP WITHOUT TIME ZONE,
    puede_editar BOOLEAN,
    usr_insert VARCHAR,
    fec_insert TIMESTAMP WITHOUT TIME ZONE
) AS
$$
BEGIN
    RETURN QUERY
    SELECT
        q.id_queja,
        q.id_usuario,
        q.tipo_peticion,
        q.asunto,
        q.descripcion,
        q.estado,
        q.respuesta,
        q.id_encargado,
        q.fec_respuesta,
        q.cancelado_por,
        q.fec_cancelacion,
        (q.estado = 'PENDIENTE' AND CURRENT_TIMESTAMP <= (q.fec_insert + INTERVAL '5 minutes')) AS puede_editar,
        q.usr_insert,
        q.fec_insert
    FROM t_sst_buzon_quejas q
    WHERE q.id_usuario = wid_usuario
      AND q.fec_delete IS NULL
    ORDER BY q.fec_insert DESC;
END;
$$
LANGUAGE PLPGSQL;
