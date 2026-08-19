CREATE OR REPLACE FUNCTION fun_listar_caja(wid_caja t_caja_compensacion.id_caja%TYPE) 
RETURNS BOOLEAN AS
$$
DECLARE 
    wreg_caja t_caja_compensacion%ROWTYPE;
BEGIN
    IF wid_caja IS NULL OR wid_caja <= 0 THEN
        RAISE NOTICE 'El ID de la Caja de Compensación no puede ser nulo o menor/igual a 0.';
        RETURN FALSE;
    END IF;

    SELECT id_caja, nombre_caja
    INTO wreg_caja
    FROM t_caja_compensacion
        WHERE id_caja = wid_caja
            AND fec_delete IS NULL;

    IF NOT FOUND THEN
        RAISE NOTICE 'La Caja de Compensación con ID % no existe.', wid_caja;
        RETURN FALSE;
    END IF;

    RAISE NOTICE 'ID: %, Nombre: %',
        wreg_caja.id_caja,
        wreg_caja.nombre_caja;
    RETURN TRUE;

EXCEPTION
    WHEN OTHERS THEN
        RAISE NOTICE 'Error: %', SQLERRM;
        RETURN FALSE;
END;
$$
LANGUAGE PLPGSQL;
