CREATE OR REPLACE FUNCTION fun_update_comunicados(
    wid_comunicado t_comunicados.id_comunicado%TYPE,
    wtitulo t_comunicados.titulo%TYPE,
    wcontenido t_comunicados.contenido%TYPE,
    wcategoria t_comunicados.categoria%TYPE
) RETURNS VARCHAR AS
$$
BEGIN
    IF wid_comunicado IS NULL OR wid_comunicado <= 0 THEN
        RETURN 'El ID del comunicado no es válido.';
    END IF;

    IF wtitulo IS NULL OR LENGTH(TRIM(wtitulo)) < 5 THEN
        RETURN 'El título del comunicado debe tener al menos 5 caracteres.';
    END IF;

    IF wcontenido IS NULL OR LENGTH(TRIM(wcontenido)) < 10 THEN
        RETURN 'El contenido del comunicado debe tener al menos 10 caracteres.';
    END IF;

    IF wcategoria IS NULL OR TRIM(wcategoria) = '' THEN
        RETURN 'La categoría del comunicado es obligatoria.';
    END IF;

    UPDATE t_comunicados
    SET titulo = TRIM(wtitulo),
        contenido = TRIM(wcontenido),
        categoria = UPPER(TRIM(wcategoria)),
        usr_update = COALESCE(NULLIF(current_setting('app.current_user', true), ''), CURRENT_USER),
        fec_update = CURRENT_TIMESTAMP
    WHERE id_comunicado = wid_comunicado
      AND fec_delete IS NULL;

    IF FOUND THEN
        RETURN 'Comunicado actualizado correctamente.';
    END IF;

    RETURN 'No se encontró el comunicado activo para actualizar.';

EXCEPTION
    WHEN SQLSTATE '23514' THEN
        RETURN 'La categoría indicada no es válida.';
    WHEN OTHERS THEN
        RAISE NOTICE 'Error: %', SQLERRM;
        RETURN 'Ocurrió un error al actualizar el comunicado.';
END;
$$
LANGUAGE PLPGSQL;