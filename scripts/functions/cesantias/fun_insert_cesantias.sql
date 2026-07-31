CREATE OR REPLACE FUNCTION fun_insert_cesantias(
    wnombre_cesantia t_cesantias.nombre_cesantia%TYPE
) RETURNS VARCHAR AS 
$$
BEGIN
    IF wnombre_cesantia IS NULL OR LENGTH(wnombre_cesantia) < 3 THEN
        RETURN 'El nombre de la entidad de Cesantías debe tener al menos 3 caracteres y no puede ser nulo.';
    END IF;

    INSERT INTO t_cesantias (
        nombre_cesantia,
        usr_insert,
        fec_insert
    )
    VALUES (
        wnombre_cesantia,
        COALESCE(NULLIF(current_setting('app.current_user', true), ''), CURRENT_USER),
        CURRENT_TIMESTAMP
    );
    
    RAISE NOTICE 'Ya inserté la entidad de Cesantías %', wnombre_cesantia;
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
