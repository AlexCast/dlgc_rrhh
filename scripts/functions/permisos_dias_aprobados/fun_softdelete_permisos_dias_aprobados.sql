CREATE OR REPLACE FUNCTION fun_softdelete_permisos_dias_aprobados(
    wid_dia_permiso t_permisos_dias_aprobados.id_dia_permiso%TYPE
) RETURNS BOOLEAN AS
$$
BEGIN
    UPDATE t_permisos_dias_aprobados
    SET fec_delete = CURRENT_TIMESTAMP,
        usr_delete = COALESCE(NULLIF(current_setting('app.current_user', true), ''), CURRENT_USER)
    WHERE id_dia_permiso = wid_dia_permiso
      AND fec_delete IS NULL;

    IF FOUND THEN
        RAISE NOTICE 'Día de permiso % eliminado lógicamente.', wid_dia_permiso;
        RETURN TRUE;
    END IF;

    RAISE NOTICE 'No se encontró día de permiso activo con ID %.', wid_dia_permiso;
    RETURN FALSE;
END;
$$
LANGUAGE PLPGSQL;