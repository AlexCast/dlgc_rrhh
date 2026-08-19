CREATE OR REPLACE FUNCTION fun_update_arl(
    wid_arl t_arl.id_arl%TYPE,
    wnombre_arl t_arl.nombre_arl%TYPE
) RETURNS VARCHAR AS 
$$
BEGIN
    IF wid_arl IS NULL OR wid_arl <= 0 THEN
        RETURN 'el id de la ARL no puede ser nulo';
    END IF;
    IF wnombre_arl IS NULL OR LENGTH(wnombre_arl) < 3 THEN
        RETURN 'El nombre de la ARL debe tener al menos 3 caracteres y no puede ser nulo.';
    END IF;

    UPDATE t_arl
    SET nombre_arl = wnombre_arl,
        usr_update = COALESCE(NULLIF(current_setting('app.current_user', true), ''), CURRENT_USER),
        fec_update = CURRENT_TIMESTAMP
        WHERE id_arl = wid_arl
            AND fec_delete IS NULL;
            
    IF FOUND THEN
        RAISE NOTICE 'Ya actualicé la ARL %', wnombre_arl;
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
