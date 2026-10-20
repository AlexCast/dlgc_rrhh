DELIMITER //

CREATE OR REPLACE PROCEDURE proc_listar_comunicados_activos(
    IN wid_usuario VARCHAR(20)
)
BEGIN
    SELECT
        c.id_comunicado,
        c.titulo,
        c.contenido,
        c.categoria,
        c.usr_insert,
        c.fec_insert,
        COALESCE(cv_usuario.fec_visto IS NOT NULL, FALSE) AS visto_por_usuario,
        cv_usuario.fec_visto AS fecha_visto,
        COALESCE(cv_total.total, 0) AS total_vistos
    FROM t_comunicados c
    LEFT JOIN t_comunicados_vistos cv_usuario
        ON cv_usuario.id_comunicado = c.id_comunicado
        AND cv_usuario.id_usuario = TRIM(COALESCE(wid_usuario, ''))
    LEFT JOIN (
        SELECT v.id_comunicado AS id_comunicado, COUNT(*) AS total
        FROM t_comunicados_vistos v
        GROUP BY v.id_comunicado
    ) cv_total ON cv_total.id_comunicado = c.id_comunicado
    WHERE c.fec_delete IS NULL
    ORDER BY c.fec_insert DESC;
END //

DELIMITER ;