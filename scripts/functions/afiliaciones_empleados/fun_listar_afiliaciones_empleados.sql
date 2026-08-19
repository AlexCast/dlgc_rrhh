CREATE OR REPLACE FUNCTION fun_listar_afiliaciones_empleados(
    wid_usuario t_afiliaciones_empleados.id_usuario%TYPE,
    wid_eps t_afiliaciones_empleados.id_eps%TYPE,
    wid_arl t_afiliaciones_empleados.id_arl%TYPE,
    wid_caja t_afiliaciones_empleados.id_caja%TYPE,
    wid_pension t_afiliaciones_empleados.id_pension%TYPE
) 
RETURNS BOOLEAN AS
$$
DECLARE 
    wreg_af t_afiliaciones_empleados%ROWTYPE;
BEGIN
    SELECT id_usuario, id_eps, id_arl, id_caja, id_pension, id_cesantia
    INTO wreg_af
    FROM t_afiliaciones_empleados
        WHERE id_usuario = wid_usuario
          AND id_eps = wid_eps
          AND id_arl = wid_arl
          AND id_caja = wid_caja
          AND id_pension = wid_pension
          AND fec_delete IS NULL;

    IF NOT FOUND THEN
        RAISE NOTICE 'No se encontró el registro para el usuario %.', wid_usuario;
        RETURN FALSE;
    END IF;

    RAISE NOTICE 'Usuario: %, EPS: %, ARL: %, Caja: %, Pensión: %, Cesantías: %',
        wreg_af.id_usuario,
        wreg_af.id_eps,
        wreg_af.id_arl,
        wreg_af.id_caja,
        wreg_af.id_pension,
        wreg_af.id_cesantia;
    RETURN TRUE;

EXCEPTION
    WHEN OTHERS THEN
        RAISE NOTICE 'Error: %', SQLERRM;
        RETURN FALSE;
END;
$$
LANGUAGE PLPGSQL;
