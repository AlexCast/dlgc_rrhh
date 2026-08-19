CREATE OR REPLACE FUNCTION fun_listar_pension(wid_pension t_pension.id_pension%TYPE) 
RETURNS BOOLEAN AS
$$
DECLARE 
    wreg_pension t_pension%ROWTYPE;
BEGIN
    IF wid_pension IS NULL OR wid_pension <= 0 THEN
        RAISE NOTICE 'El ID de la entidad de Pensión no puede ser nulo o menor/igual a 0.';
        RETURN FALSE;
    END IF;

    SELECT id_pension, nombre_pension
    INTO wreg_pension
    FROM t_pension
        WHERE id_pension = wid_pension
            AND fec_delete IS NULL;

    IF NOT FOUND THEN
        RAISE NOTICE 'La entidad de Pensión con ID % no existe.', wid_pension;
        RETURN FALSE;
    END IF;

    RAISE NOTICE 'ID: %, Nombre: %',
        wreg_pension.id_pension,
        wreg_pension.nombre_pension;
    RETURN TRUE;

EXCEPTION
    WHEN OTHERS THEN
        RAISE NOTICE 'Error: %', SQLERRM;
        RETURN FALSE;
END;
$$
LANGUAGE PLPGSQL;
