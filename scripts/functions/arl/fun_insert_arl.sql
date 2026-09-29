CREATE OR REPLACE FUNCTION fun_insert_arl(
    wnombre_arl t_arl.nombre_arl%TYPE
) RETURNS VARCHAR AS 
$$
BEGIN
    IF wnombre_arl IS NULL OR LENGTH(wnombre_arl) < 3 THEN
        RETURN 'El nombre de la ARL debe tener al menos 3 caracteres y no puede ser nulo.';
    END IF;

    INSERT INTO t_arl (
        nombre_arl,
        usr_insert,
        fec_insert
    )
    VALUES (
        wnombre_arl,
        COALESCE(NULLIF(current_setting('app.current_user', true), ''), CURRENT_USER),
        CURRENT_TIMESTAMP
    );
    
    RAISE NOTICE 'Ya inserté la ARL %', wnombre_arl;
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
