CREATE OR REPLACE FUNCTION fun_insert_pension(
    wnombre_pension t_pension.nombre_pension%TYPE
) RETURNS VARCHAR AS 
$$
BEGIN
    IF wnombre_pension IS NULL OR LENGTH(wnombre_pension) < 3 THEN
        RETURN 'El nombre de la entidad de Pensión debe tener al menos 3 caracteres y no puede ser nulo.';
    END IF;

    INSERT INTO t_pension (
        nombre_pension,
        usr_insert,
        fec_insert
    )
    VALUES (
        wnombre_pension,
        COALESCE(NULLIF(current_setting('app.current_user', true), ''), CURRENT_USER),
        CURRENT_TIMESTAMP
    );
    
    RAISE NOTICE 'Ya inserté la entidad de Pensión %', wnombre_pension;
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
