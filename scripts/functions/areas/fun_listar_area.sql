CREATE OR REPLACE FUNCTION fun_listar_area(wid_area t_areas.id_area%TYPE) 
RETURNS BOOLEAN AS
$$
DECLARE 
    wreg_area t_areas%ROWTYPE;
BEGIN
    IF wid_area IS NULL OR wid_area <= 0 THEN
        RAISE NOTICE 'El ID del área no puede ser nulo o menor/igual a 0.';
        RETURN FALSE;
    END IF;

    SELECT id_area, nombre_area
    INTO wreg_area
    FROM t_areas
        WHERE id_area = wid_area
            AND fec_delete IS NULL;

    IF NOT FOUND THEN
        RAISE NOTICE 'El área con ID % no existe.', wid_area;
        RETURN FALSE;
    END IF;

    RAISE NOTICE 'ID: %, Nombre: %',
        wreg_area.id_area,
        wreg_area.nombre_area;
    RETURN TRUE;

EXCEPTION
    WHEN OTHERS THEN
        RAISE NOTICE 'Error: %', SQLERRM;
        RETURN FALSE;
END;
$$
LANGUAGE PLPGSQL;
