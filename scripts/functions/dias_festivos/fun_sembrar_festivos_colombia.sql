-- Siembra en t_dias_festivos los festivos civiles colombianos calculados para un año.
-- Idempotente: ignora fechas que ya existan (t_dias_festivos.fecha es UNIQUE).
-- Pensada para ejecutarse una vez por año (manualmente o desde un endpoint de RRHH).
CREATE OR REPLACE FUNCTION fun_sembrar_festivos_colombia(wanio INT) RETURNS VARCHAR AS
$$
DECLARE
    vactor VARCHAR;
    vinsertados INT;
BEGIN
    IF wanio IS NULL OR wanio < 2000 OR wanio > 2100 THEN
        RETURN 'El año indicado no es válido.';
    END IF;

    vactor := COALESCE(NULLIF(current_setting('app.current_user', true), ''), CURRENT_USER);

    INSERT INTO t_dias_festivos (fecha, descripcion, tipo_festivo, descuenta_salario, usr_insert, fec_insert)
    SELECT f.fecha, f.descripcion, 'NACIONAL', FALSE, vactor, CURRENT_TIMESTAMP
    FROM fun_calcular_festivos_colombia(wanio) f
    ON CONFLICT (fecha) DO NOTHING;

    GET DIAGNOSTICS vinsertados = ROW_COUNT;

    RETURN vinsertados || ' festivo(s) insertado(s) para el año ' || wanio || '.';

EXCEPTION
    WHEN OTHERS THEN
        RAISE NOTICE 'Error: %', SQLERRM;
        RETURN 'Ocurrió un error al sembrar los festivos del año ' || wanio || '.';
END;
$$
LANGUAGE PLPGSQL;
