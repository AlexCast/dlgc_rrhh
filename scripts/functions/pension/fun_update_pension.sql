CREATE OR REPLACE FUNCTION fun_update_pension(
    wid_pension t_pension.id_pension%TYPE,
    wnombre_pension t_pension.nombre_pension%TYPE
) RETURNS VARCHAR AS 
$$
BEGIN
    IF wid_pension IS NULL OR wid_pension <= 0 THEN
        RETURN 'el id de la entidad de Pensión no puede ser nulo';
    END IF;
    IF wnombre_pension IS NULL OR LENGTH(wnombre_pension) < 3 THEN
        RETURN 'El nombre de la entidad de Pensión debe tener al menos 3 caracteres y no puede ser nulo.';
    END IF;

    UPDATE t_pension
    SET nombre_pension = wnombre_pension
        WHERE id_pension = wid_pension
            AND fec_delete IS NULL;
            
    IF FOUND THEN
        RAISE NOTICE 'Ya actualicé la entidad de Pensión %', wnombre_pension;
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
