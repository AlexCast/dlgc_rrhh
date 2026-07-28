CREATE OR REPLACE FUNCTION fun_insert_area(
    wnombre_area t_areas.nombre_area%TYPE
) RETURNS VARCHAR AS 
$$
BEGIN
    IF wnombre_area IS NULL OR LENGTH(wnombre_area) < 3 THEN
        RETURN 'El nombre del área debe tener al menos 3 caracteres y no puede ser nulo.';
    END IF;

    INSERT INTO t_areas (nombre_area)
    VALUES (wnombre_area);
    
    RAISE NOTICE 'Ya inserté el área %', wnombre_area;
    RETURN 'Esta vaina funcionó.. Somos duros en ADSO';

EXCEPTION
    WHEN SQLSTATE '23505' THEN  
        RAISE NOTICE 'El registro ya existe.. Trabaje bien o ábrase llaveee';
        RETURN FALSE;
    WHEN OTHERS THEN
        RAISE NOTICE 'Esta vaina se totió.. Y no fue de la risa.. Déjeme trabajar';
        RETURN FALSE;
END;
$$ 
LANGUAGE PLPGSQL;
