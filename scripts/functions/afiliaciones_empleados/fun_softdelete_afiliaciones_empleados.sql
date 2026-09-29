CREATE OR REPLACE FUNCTION fun_softdelete_afiliaciones_empleados(
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
    SET 
        fec_delete = CURRENT_TIMESTAMP,
        usr_delete = COALESCE(NULLIF(current_setting('app.current_user', true), ''), CURRENT_USER)
        WHERE id_usuario = wid_usuario
          AND id_eps = wid_eps
          AND id_arl = wid_arl
          AND id_caja = wid_caja
          AND id_pension = wid_pension
          AND fec_delete IS NULL;

    IF FOUND THEN
        RAISE NOTICE 'Afiliaciones del usuario % eliminadas lógicamente.', wid_usuario;
        RETURN TRUE;
    ELSE
        RAISE NOTICE 'No se encontró el registro o ya está eliminado.';
        RETURN FALSE;
    END IF;
END;
$$ LANGUAGE PLPGSQL;
