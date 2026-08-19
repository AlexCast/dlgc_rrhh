CREATE OR REPLACE FUNCTION fun_insert_nomina(
    wid_usuario t_nomina.id_usuario%TYPE,
    wid_banco t_nomina.id_banco%TYPE,
    wnum_cuenta t_nomina.num_cuenta%TYPE,
    wsalario t_nomina.salario%TYPE
) RETURNS VARCHAR AS 
$$
BEGIN
    IF wid_usuario IS NULL OR wid_usuario = '' THEN
        RETURN 'El id_usuario no puede ser nulo.';
    END IF;
    IF wid_banco IS NULL OR wid_banco <= 0 THEN
        RETURN 'El id_banco no puede ser nulo.';
    END IF;
    IF wnum_cuenta IS NULL OR wnum_cuenta = '' THEN
        RETURN 'El número de cuenta no puede ser nulo.';
    END IF;
    IF wsalario IS NULL OR wsalario < 0 THEN
        RETURN 'El salario no puede ser nulo o negativo.';
    END IF;

    INSERT INTO t_nomina (
        id_usuario,
        id_banco,
        num_cuenta,
        salario,
        usr_insert,
        fec_insert
    )
    VALUES (
        wid_usuario,
        wid_banco,
        wnum_cuenta,
        wsalario,
        COALESCE(NULLIF(current_setting('app.current_user', true), ''), CURRENT_USER),
        CURRENT_TIMESTAMP
    );
    
    RAISE NOTICE 'Ya inserté la nómina para el usuario %', wid_usuario;
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
