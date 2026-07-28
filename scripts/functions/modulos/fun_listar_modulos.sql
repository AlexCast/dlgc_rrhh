CREATE OR REPLACE FUNCTION fun_listar_modulos(
    wid_modulo t_modulos.id_modulo%TYPE
) RETURNS BOOLEAN AS
$$
DECLARE
    wreg_modulo t_modulos%ROWTYPE;
BEGIN
    IF wid_modulo IS NULL OR wid_modulo <= 0 THEN
        RAISE NOTICE 'El ID del módulo no es válido.';
        RETURN FALSE;
    END IF;

    SELECT *
    INTO wreg_modulo
    FROM t_modulos
    WHERE id_modulo = wid_modulo
      AND fec_delete IS NULL;

    IF NOT FOUND THEN
        RAISE NOTICE 'No existe módulo activo con ID %.', wid_modulo;
        RETURN FALSE;
    END IF;

    RAISE NOTICE 'ID: %, Módulo: %', wreg_modulo.id_modulo, wreg_modulo.nombre_modulo;
    RETURN TRUE;

EXCEPTION
    WHEN OTHERS THEN
        RAISE NOTICE 'Error: %', SQLERRM;
        RETURN FALSE;
END;
$$
LANGUAGE PLPGSQL;