CREATE OR REPLACE FUNCTION fun_listar_eps(wid_eps t_eps.id_eps%TYPE) 
RETURNS BOOLEAN AS
$$
DECLARE 
    wreg_eps t_eps%ROWTYPE;
BEGIN
    IF wid_eps IS NULL OR wid_eps <= 0 THEN
        RAISE NOTICE 'El ID de la EPS no puede ser nulo o menor/igual a 0.';
        RETURN FALSE;
    END IF;

    SELECT id_eps, nombre_eps
    INTO wreg_eps
    FROM t_eps
        WHERE id_eps = wid_eps
            AND fec_delete IS NULL;

    IF NOT FOUND THEN
        RAISE NOTICE 'La EPS con ID % no existe.', wid_eps;
        RETURN FALSE;
    END IF;

    RAISE NOTICE 'ID: %, Nombre: %',
        wreg_eps.id_eps,
        wreg_eps.nombre_eps;
    RETURN TRUE;

EXCEPTION
    WHEN OTHERS THEN
        RAISE NOTICE 'Error: %', SQLERRM;
        RETURN FALSE;
END;
$$
LANGUAGE PLPGSQL;
