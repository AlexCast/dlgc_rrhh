CREATE OR REPLACE FUNCTION fun_update_caja(
    wid_caja t_caja_compensacion.id_caja%TYPE,
    wnombre_caja t_caja_compensacion.nombre_caja%TYPE
) RETURNS VARCHAR AS 
$$
BEGIN
    IF wid_caja IS NULL OR wid_caja <= 0 THEN
        RETURN 'el id de la Caja de Compensación no puede ser nulo';
    END IF;
    IF wnombre_caja IS NULL OR LENGTH(wnombre_caja) < 3 THEN
        RETURN 'El nombre de la Caja de Compensación debe tener al menos 3 caracteres y no puede ser nulo.';
    END IF;

    UPDATE t_caja_compensacion
    SET nombre_caja = wnombre_caja,
        usr_update = COALESCE(NULLIF(current_setting('app.current_user', true), ''), CURRENT_USER),
        fec_update = CURRENT_TIMESTAMP
        WHERE id_caja = wid_caja
            AND fec_delete IS NULL;
            
    IF FOUND THEN
        RAISE NOTICE 'Ya actualicé la Caja de Compensación %', wnombre_caja;
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
