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
