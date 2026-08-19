CREATE OR REPLACE FUNCTION fun_listar_comunicados_activos(
    wid_usuario t_usuarios.id_usuario%TYPE DEFAULT NULL
) RETURNS TABLE (
    id_comunicado INTEGER,
    titulo VARCHAR,
    contenido TEXT,
    categoria VARCHAR,
    usr_insert VARCHAR,
    fec_insert TIMESTAMP WITHOUT TIME ZONE,
    visto_por_usuario BOOLEAN,
    fecha_visto TIMESTAMP WITHOUT TIME ZONE,
    total_vistos BIGINT
) AS
$$
BEGIN
    RETURN QUERY
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
END;
$$
LANGUAGE PLPGSQL;
