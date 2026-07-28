CREATE OR REPLACE FUNCTION fun_listar_operaciones(
    wid_operacion t_operaciones.id_operacion%TYPE
) RETURNS BOOLEAN AS
$$
DECLARE
    wreg_operacion t_operaciones%ROWTYPE;
BEGIN
    IF wid_operacion IS NULL OR wid_operacion <= 0 THEN
        RAISE NOTICE 'El ID de la operación no es válido.';
        RETURN FALSE;
    END IF;

    SELECT *
    INTO wreg_operacion
    FROM t_operaciones
    WHERE id_operacion = wid_operacion
      AND fec_delete IS NULL;

    IF NOT FOUND THEN
        RAISE NOTICE 'No existe operación activa con ID %.', wid_operacion;
        RETURN FALSE;
    END IF;

    RAISE NOTICE 'ID: %, Módulo: %, Operación: %',
        wreg_operacion.id_operacion,
        wreg_operacion.id_modulo,
        wreg_operacion.nombre_operacion;
    RETURN TRUE;

EXCEPTION
    WHEN OTHERS THEN
        RAISE NOTICE 'Error: %', SQLERRM;
        RETURN FALSE;
END;
$$
LANGUAGE PLPGSQL;