CREATE OR REPLACE FUNCTION fun_update_area(
    wid_area t_areas.id_area%TYPE,
    wnombre_area t_areas.nombre_area%TYPE
) RETURNS VARCHAR AS 
$$
BEGIN
    IF wid_area IS NULL OR wid_area <= 0 THEN
        RETURN 'el id del área no puede ser nulo';
    END IF;
    IF wnombre_area IS NULL OR LENGTH(wnombre_area) < 3 THEN
        RETURN 'El nombre del área debe tener al menos 3 caracteres y no puede ser nulo.';
    END IF;

    UPDATE t_areas
    SET nombre_area = wnombre_area,
        usr_update = COALESCE(NULLIF(current_setting('app.current_user', true), ''), CURRENT_USER),
        fec_update = CURRENT_TIMESTAMP
        WHERE id_area = wid_area
            AND fec_delete IS NULL;
            
    IF FOUND THEN
        RAISE NOTICE 'Ya actualicé el área %', wnombre_area;
        RETURN 'Esta vaina funcionó.. Somos duros en ADSO';
    ELSE
        RAISE NOTICE 'Pequeño demonio, no funcionó esta joda... Y ahora????';
        RETURN 'Eche pa la primaria porque de esto no va a comer... Sorry';
    END IF;
    
EXCEPTION
    WHEN SQLSTATE '23505' THEN
        RAISE EXCEPTION 'El registro ya existe.. Trabaje bien o ábrase llaveee';
        RETURN FALSE;
    WHEN OTHERS THEN
        RAISE EXCEPTION 'Esta vaina se totió.. Y no fue de la risa.. Déjeme trabajar';
        RETURN FALSE;
END;
$$ 
LANGUAGE PLPGSQL;
