CREATE OR REPLACE FUNCTION fun_listar_arl(wid_arl t_arl.id_arl%TYPE) 
RETURNS BOOLEAN AS
$$
DECLARE 
    wreg_arl t_arl%ROWTYPE;
BEGIN
    IF wid_arl IS NULL OR wid_arl <= 0 THEN
        RAISE NOTICE 'El ID de la ARL no puede ser nulo o menor/igual a 0.';
        RETURN FALSE;
    END IF;

    SELECT id_arl, nombre_arl
    INTO wreg_arl
    FROM t_arl
        WHERE id_arl = wid_arl
            AND fec_delete IS NULL;

    IF NOT FOUND THEN
        RAISE NOTICE 'La ARL con ID % no existe.', wid_arl;
        RETURN FALSE;
    END IF;

    RAISE NOTICE 'ID: %, Nombre: %',
        wreg_arl.id_arl,
        wreg_arl.nombre_arl;
    RETURN TRUE;

EXCEPTION
    WHEN OTHERS THEN
        RAISE NOTICE 'Error: %', SQLERRM;
        RETURN FALSE;
END;
$$
LANGUAGE PLPGSQL;
