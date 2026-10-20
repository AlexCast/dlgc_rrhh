DELIMITER //

CREATE OR REPLACE PROCEDURE proc_listar_sst_quejas_encargado(
    IN westado VARCHAR(20)
)
BEGIN
    SELECT
        q.id_queja,
        q.id_usuario,
        -- CONCAT_WS elimina espacios extra si hay nulos
        CONCAT_WS(' ', u.primer_nombre, NULLIF(u.segundo_nombre, ''), u.primer_apellido, NULLIF(u.segundo_apellido, '')) AS nombre_usuario,
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
END //

DELIMITER ;