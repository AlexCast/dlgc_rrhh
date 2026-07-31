CREATE OR REPLACE FUNCTION fun_insert_cajacompensacion(
    wnombre_caja t_caja_compensacion.nombre_caja%TYPE
) RETURNS VARCHAR AS 
$$
BEGIN
    IF wnombre_caja IS NULL OR LENGTH(wnombre_caja) < 3 THEN
        RETURN 'El nombre de la Caja de Compensación debe tener al menos 3 caracteres y no puede ser nulo.';
    END IF;

    INSERT INTO t_caja_compensacion (
        nombre_caja,
        usr_insert,
        fec_insert
    )
    VALUES (
        wnombre_caja,
        COALESCE(NULLIF(current_setting('app.current_user', true), ''), CURRENT_USER),
        CURRENT_TIMESTAMP
    );
    
    RAISE NOTICE 'Ya inserté la Caja de Compensación %', wnombre_caja;
    RETURN 'Esta vaina funcionó.. Somos duros en ADSO';

EXCEPTION
    WHEN SQLSTATE '23505' THEN  
        RAISE NOTICE 'El registro ya existe.. Trabaje bien o ábrase llaveee';
        RETURN 'El registro ya existe.. Trabaje bien o ábrase llaveee';

    WHEN OTHERS THEN
        RAISE NOTICE 'Esta vaina se totió.. Y no fue de la risa.. Déjeme trabajar';
        RETURN FALSE;
END;
$$ 
LANGUAGE PLPGSQL;
--