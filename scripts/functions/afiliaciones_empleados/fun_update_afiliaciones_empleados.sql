CREATE OR REPLACE FUNCTION fun_update_afiliaciones_empleados(
    wid_usuario t_afiliaciones_empleados.id_usuario%TYPE,
    wid_eps t_afiliaciones_empleados.id_eps%TYPE,
    wid_arl t_afiliaciones_empleados.id_arl%TYPE,
    wid_caja t_afiliaciones_empleados.id_caja%TYPE,
    wid_pension t_afiliaciones_empleados.id_pension%TYPE,
    wid_cesantia t_afiliaciones_empleados.id_cesantia%TYPE
) RETURNS VARCHAR AS 
$$
BEGIN
    UPDATE t_afiliaciones_empleados
    SET id_cesantia = wid_cesantia,
        usr_update = COALESCE(NULLIF(current_setting('app.current_user', true), ''), CURRENT_USER),
        fec_update = CURRENT_TIMESTAMP
        WHERE id_usuario = wid_usuario
            AND id_eps = wid_eps
            AND id_arl = wid_arl
            AND id_caja = wid_caja
            AND id_pension = wid_pension
            AND fec_delete IS NULL;
            
    IF FOUND THEN
        RAISE NOTICE 'Ya actualicé las afiliaciones para el usuario %', wid_usuario;
        RETURN 'Esta vaina funcionó.. Somos duros en ADSO';
    ELSE
        RAISE NOTICE 'Pequeño demonio, no funcionó esta joda... Y ahora????';
        RETURN 'Eche pa la primaria porque de esto no va a comer... Sorry';
    END IF;
    
EXCEPTION
    WHEN OTHERS THEN
        RAISE EXCEPTION 'Esta vaina se totió.. Y no fue de la risa.. Déjeme trabajar';
        RETURN FALSE;
END;
$$ 
LANGUAGE PLPGSQL;
