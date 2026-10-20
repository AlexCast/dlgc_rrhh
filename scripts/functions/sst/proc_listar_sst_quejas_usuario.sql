DELIMITER //

CREATE OR REPLACE PROCEDURE proc_listar_sst_quejas_usuario(
    IN wid_usuario VARCHAR(20)
)
BEGIN
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
        (q.estado = 'PENDIENTE' AND CURRENT_TIMESTAMP <= DATE_ADD(q.fec_insert, INTERVAL 5 MINUTE)) AS puede_editar,
        q.usr_insert,
        q.fec_insert
    FROM t_sst_buzon_quejas q
    WHERE q.id_usuario = wid_usuario
      AND q.fec_delete IS NULL
    ORDER BY q.fec_insert DESC;
END //

DELIMITER ;