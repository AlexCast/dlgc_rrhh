CREATE OR REPLACE FUNCTION fun_listar_nomina(wid_nomina t_nomina.id_nomina%TYPE) 
RETURNS BOOLEAN AS
$$
DECLARE 
    wreg_nomina t_nomina%ROWTYPE;
BEGIN
    IF wid_nomina IS NULL OR wid_nomina <= 0 THEN
        RAISE NOTICE 'El ID de la nómina no puede ser nulo o menor/igual a 0.';
        RETURN FALSE;
    END IF;

    SELECT id_nomina, id_usuario, id_banco, num_cuenta, salario
    INTO wreg_nomina
    FROM t_nomina
        WHERE id_nomina = wid_nomina
            AND fec_delete IS NULL;

    IF NOT FOUND THEN
        RAISE NOTICE 'La nómina con ID % no existe.', wid_nomina;
        RETURN FALSE;
    END IF;

    RAISE NOTICE 'ID Nómina: %, Usuario: %, Banco ID: %, Cuenta: %, Salario: %',
        wreg_nomina.id_nomina,
        wreg_nomina.id_usuario,
        wreg_nomina.id_banco,
        wreg_nomina.num_cuenta,
        wreg_nomina.salario;
    RETURN TRUE;

EXCEPTION
    WHEN OTHERS THEN
        RAISE NOTICE 'Error: %', SQLERRM;
        RETURN FALSE;
END;
$$
LANGUAGE PLPGSQL;
