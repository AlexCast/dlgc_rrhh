CREATE OR REPLACE FUNCTION fun_update_cesantias(
    wid_cesantia t_cesantias.id_cesantia%TYPE,
    wnombre_cesantia t_cesantias.nombre_cesantia%TYPE
) RETURNS VARCHAR AS 
$$
BEGIN
    IF wid_cesantia IS NULL OR wid_cesantia <= 0 THEN
        RETURN 'el id de la entidad de Cesantías no puede ser nulo';
    END IF;
    IF wnombre_cesantia IS NULL OR LENGTH(wnombre_cesantia) < 3 THEN
        RETURN 'El nombre de la entidad de Cesantías debe tener al menos 3 caracteres y no puede ser nulo.';
    END IF;

    UPDATE t_cesantias
    SET nombre_cesantia = wnombre_cesantia,
        usr_update = COALESCE(NULLIF(current_setting('app.current_user', true), ''), CURRENT_USER),
        fec_update = CURRENT_TIMESTAMP
        WHERE id_cesantia = wid_cesantia
            AND fec_delete IS NULL;
            
    IF FOUND THEN
        RAISE NOTICE 'Ya actualicé la entidad de Cesantías %', wnombre_cesantia;
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
