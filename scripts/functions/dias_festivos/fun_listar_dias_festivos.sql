CREATE OR REPLACE FUNCTION fun_listar_dias_festivos(
    wid_festivo t_dias_festivos.id_festivo%TYPE
) RETURNS BOOLEAN AS
$$
DECLARE
    wreg_festivo t_dias_festivos%ROWTYPE;
BEGIN
    IF wid_festivo IS NULL OR wid_festivo <= 0 THEN
        RAISE NOTICE 'El ID del festivo no es válido.';
        RETURN FALSE;
    END IF;

    SELECT *
    INTO wreg_festivo
    FROM t_dias_festivos
    WHERE id_festivo = wid_festivo
      AND fec_delete IS NULL;

    IF NOT FOUND THEN
        RAISE NOTICE 'No existe festivo activo con ID %.', wid_festivo;
        RETURN FALSE;
    END IF;

    RAISE NOTICE 'ID: %, Fecha: %, Descripción: %',
        wreg_festivo.id_festivo,
        wreg_festivo.fecha,
        wreg_festivo.descripcion;
    RETURN TRUE;

EXCEPTION
    WHEN OTHERS THEN
        RAISE NOTICE 'Error: %', SQLERRM;
        RETURN FALSE;
END;
$$
LANGUAGE PLPGSQL;