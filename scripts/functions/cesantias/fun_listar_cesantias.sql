CREATE OR REPLACE FUNCTION fun_listar_cesantias(wid_cesantia t_cesantias.id_cesantia%TYPE) 
RETURNS BOOLEAN AS
$$
DECLARE 
    wreg_cesantia t_cesantias%ROWTYPE;
BEGIN
    IF wid_cesantia IS NULL OR wid_cesantia <= 0 THEN
        RAISE NOTICE 'El ID de la entidad de Cesantías no puede ser nulo o menor/igual a 0.';
        RETURN FALSE;
    END IF;

    SELECT id_cesantia, nombre_cesantia
    INTO wreg_cesantia
    FROM t_cesantias
        WHERE id_cesantia = wid_cesantia
            AND fec_delete IS NULL;

    IF NOT FOUND THEN
        RAISE NOTICE 'La entidad de Cesantías con ID % no existe.', wid_cesantia;
        RETURN FALSE;
    END IF;

    RAISE NOTICE 'ID: %, Nombre: %',
        wreg_cesantia.id_cesantia,
        wreg_cesantia.nombre_cesantia;
    RETURN TRUE;

EXCEPTION
    WHEN OTHERS THEN
        RAISE NOTICE 'Error: %', SQLERRM;
        RETURN FALSE;
END;
$$
LANGUAGE PLPGSQL;
