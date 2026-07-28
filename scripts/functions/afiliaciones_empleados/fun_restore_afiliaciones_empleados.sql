CREATE OR REPLACE FUNCTION fun_restore_afiliaciones_empleados(
    wid_usuario t_afiliaciones_empleados.id_usuario%TYPE,
    wid_eps t_afiliaciones_empleados.id_eps%TYPE,
    wid_arl t_afiliaciones_empleados.id_arl%TYPE,
    wid_caja t_afiliaciones_empleados.id_caja%TYPE,
    wid_pension t_afiliaciones_empleados.id_pension%TYPE
)
RETURNS BOOLEAN AS
$$
BEGIN
    UPDATE t_afiliaciones_empleados
    SET fec_delete = NULL, usr_delete = NULL
    WHERE id_usuario = wid_usuario 
      AND id_eps = wid_eps
      AND id_arl = wid_arl
      AND id_caja = wid_caja
      AND id_pension = wid_pension
      AND fec_delete IS NOT NULL;

    IF FOUND THEN
        RAISE NOTICE 'Afiliaciones del usuario % restauradas correctamente.', wid_usuario;
        RETURN TRUE;
    ELSE
        RAISE NOTICE 'No se encontró el registro eliminado.';
        RETURN FALSE;
    END IF;
END;
$$ LANGUAGE plpgsql;
