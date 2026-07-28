CREATE OR REPLACE FUNCTION fun_listar_comunicados(
    wid_comunicado t_comunicados.id_comunicado%TYPE
) RETURNS BOOLEAN AS
$$
DECLARE
    wreg_comunicado t_comunicados%ROWTYPE;
BEGIN
    IF wid_comunicado IS NULL OR wid_comunicado <= 0 THEN
        RAISE NOTICE 'El ID del comunicado no es válido.';
        RETURN FALSE;
    END IF;

    SELECT *
    INTO wreg_comunicado
    FROM t_comunicados
    WHERE id_comunicado = wid_comunicado
      AND fec_delete IS NULL;

    IF NOT FOUND THEN
        RAISE NOTICE 'No existe comunicado activo con ID %.', wid_comunicado;
        RETURN FALSE;
    END IF;

    RAISE NOTICE 'ID: %, Título: %, Categoría: %',
        wreg_comunicado.id_comunicado,
        wreg_comunicado.titulo,
        wreg_comunicado.categoria;
    RETURN TRUE;

EXCEPTION
    WHEN OTHERS THEN
        RAISE NOTICE 'Error: %', SQLERRM;
        RETURN FALSE;
END;
$$
LANGUAGE PLPGSQL;