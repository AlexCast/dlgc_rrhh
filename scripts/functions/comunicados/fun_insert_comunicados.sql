CREATE OR REPLACE FUNCTION fun_insert_comunicados(
    wtitulo t_comunicados.titulo%TYPE,
    wcontenido t_comunicados.contenido%TYPE,
    wcategoria t_comunicados.categoria%TYPE
) RETURNS VARCHAR AS
$$
BEGIN
    IF wtitulo IS NULL OR LENGTH(TRIM(wtitulo)) < 5 THEN
        RETURN 'El título del comunicado debe tener al menos 5 caracteres.';
    END IF;

    IF wcontenido IS NULL OR LENGTH(TRIM(wcontenido)) < 10 THEN
        RETURN 'El contenido del comunicado debe tener al menos 10 caracteres.';
    END IF;

    IF wcategoria IS NULL OR TRIM(wcategoria) = '' THEN
        RETURN 'La categoría del comunicado es obligatoria.';
    END IF;

    INSERT INTO t_comunicados (
        titulo,
        contenido,
        categoria,
        usr_insert,
        fec_insert
    )
    VALUES (
        TRIM(wtitulo),
        TRIM(wcontenido),
        UPPER(TRIM(wcategoria)),
        COALESCE(NULLIF(current_setting('app.current_user', true), ''), CURRENT_USER),
        CURRENT_TIMESTAMP
    );

    RETURN 'Comunicado insertado correctamente.';

EXCEPTION
    WHEN SQLSTATE '23514' THEN
        RETURN 'La categoría indicada no es válida.';
    WHEN OTHERS THEN
        RAISE NOTICE 'Error: %', SQLERRM;
        RETURN 'Ocurrió un error al insertar el comunicado.';
END;
$$
LANGUAGE PLPGSQL;