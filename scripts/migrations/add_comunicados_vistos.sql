-- Migración: tabla auxiliar para tracking de comunicados vistos por usuario
-- Fecha: 2026-07-28

CREATE TABLE IF NOT EXISTS t_comunicados_vistos (
    id_visto        SERIAL,
    id_comunicado   INTEGER NOT NULL,
    id_usuario      VARCHAR(20) NOT NULL,
    fec_visto       TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP,
    usr_insert      VARCHAR NOT NULL DEFAULT CURRENT_USER,
    fec_insert      TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_visto),
    FOREIGN KEY (id_comunicado) REFERENCES t_comunicados(id_comunicado),
    FOREIGN KEY (id_usuario) REFERENCES t_usuarios(id_usuario),
    CONSTRAINT uq_comunicado_usuario UNIQUE (id_comunicado, id_usuario)
);

CREATE INDEX IF NOT EXISTS idx_comunicados_vistos_usuario
    ON t_comunicados_vistos (id_usuario);

CREATE INDEX IF NOT EXISTS idx_comunicados_vistos_comunicado
    ON t_comunicados_vistos (id_comunicado);

-- Función para marcar un comunicado como visto por un usuario (idempotente)
CREATE OR REPLACE FUNCTION fun_marcar_visto_comunicado(
    wid_comunicado t_comunicados.id_comunicado%TYPE,
    wid_usuario t_usuarios.id_usuario%TYPE
) RETURNS BOOLEAN AS
$$
BEGIN
    IF wid_comunicado IS NULL OR wid_comunicado <= 0 THEN
        RETURN FALSE;
    END IF;

    IF wid_usuario IS NULL OR TRIM(wid_usuario) = '' THEN
        RETURN FALSE;
    END IF;

    IF NOT EXISTS (
        SELECT 1 FROM t_comunicados
        WHERE id_comunicado = wid_comunicado
          AND fec_delete IS NULL
    ) THEN
        RETURN FALSE;
    END IF;

    INSERT INTO t_comunicados_vistos (id_comunicado, id_usuario, fec_visto)
    VALUES (wid_comunicado, TRIM(wid_usuario), CURRENT_TIMESTAMP)
    ON CONFLICT (id_comunicado, id_usuario) DO NOTHING;

    RETURN TRUE;

EXCEPTION
    WHEN OTHERS THEN
        RAISE NOTICE 'Error: %', SQLERRM;
        RETURN FALSE;
END;
$$
LANGUAGE PLPGSQL;

-- Función para listar comunicados activos enriquecida con información de lectura
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
