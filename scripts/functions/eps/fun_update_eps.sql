CREATE OR REPLACE FUNCTION fun_update_eps(
    wid_eps t_eps.id_eps%TYPE,
    wnombre_eps t_eps.nombre_eps%TYPE
) RETURNS VARCHAR AS 
$$
BEGIN
    IF wid_eps IS NULL OR wid_eps <= 0 THEN
        RETURN 'el id de la EPS no puede ser nulo';
    END IF;
    IF wnombre_eps IS NULL OR LENGTH(wnombre_eps) < 3 THEN
        RETURN 'El nombre de la EPS debe tener al menos 3 caracteres y no puede ser nulo.';
    END IF;

    UPDATE t_eps
    SET nombre_eps = wnombre_eps
        WHERE id_eps = wid_eps
            AND fec_delete IS NULL;
            
    IF FOUND THEN
        RAISE NOTICE 'Ya actualicé la EPS %', wnombre_eps;
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
