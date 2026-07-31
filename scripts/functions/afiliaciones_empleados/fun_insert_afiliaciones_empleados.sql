CREATE OR REPLACE FUNCTION fun_insert_afiliaciones_empleados(
    wid_usuario t_afiliaciones_empleados.id_usuario%TYPE,
    wid_eps t_afiliaciones_empleados.id_eps%TYPE,
    wid_arl t_afiliaciones_empleados.id_arl%TYPE,
    wid_caja t_afiliaciones_empleados.id_caja%TYPE,
    wid_pension t_afiliaciones_empleados.id_pension%TYPE,
    wid_cesantia t_afiliaciones_empleados.id_cesantia%TYPE
) RETURNS VARCHAR AS 
$$
BEGIN
    IF wid_usuario IS NULL OR wid_usuario = '' THEN
        RETURN 'El id_usuario no puede ser nulo o vacío.';
    END IF;

    INSERT INTO t_afiliaciones_empleados (
        id_usuario,
        id_eps,
        id_arl,
        id_caja,
        id_pension,
        id_cesantia,
        usr_insert,
        fec_insert
    )
    VALUES (
        wid_usuario,
        wid_eps,
        wid_arl,
        wid_caja,
        wid_pension,
        wid_cesantia,
        COALESCE(NULLIF(current_setting('app.current_user', true), ''), CURRENT_USER),
        CURRENT_TIMESTAMP
    );
    
    RAISE NOTICE 'Ya inserté las afiliaciones para el usuario %', wid_usuario;
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
