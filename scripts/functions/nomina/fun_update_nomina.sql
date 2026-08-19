CREATE OR REPLACE FUNCTION fun_update_nomina(
    wid_nomina t_nomina.id_nomina%TYPE,
    wid_usuario t_nomina.id_usuario%TYPE,
    wid_banco t_nomina.id_banco%TYPE,
    wnum_cuenta t_nomina.num_cuenta%TYPE,
    wsalario t_nomina.salario%TYPE
) RETURNS VARCHAR AS 
$$
BEGIN
    IF wid_nomina IS NULL OR wid_nomina <= 0 THEN
        RETURN 'el id de la nómina no puede ser nulo';
    END IF;

    UPDATE t_nomina
    SET id_usuario = wid_usuario,
        id_banco = wid_banco,
        num_cuenta = wnum_cuenta,
        salario = wsalario,
        usr_update = COALESCE(NULLIF(current_setting('app.current_user', true), ''), CURRENT_USER),
        fec_update = CURRENT_TIMESTAMP
        WHERE id_nomina = wid_nomina
            AND fec_delete IS NULL;
            
    IF FOUND THEN
        RAISE NOTICE 'Ya actualicé la nómina %', wid_nomina;
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
