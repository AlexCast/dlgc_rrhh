CREATE OR REPLACE FUNCTION fun_restore_permisos_dias_aprobados(
    wid_dia_permiso t_permisos_dias_aprobados.id_dia_permiso%TYPE
) RETURNS BOOLEAN AS
$$
BEGIN
    UPDATE t_permisos_dias_aprobados
    SET fec_delete = NULL,
        usr_delete = NULL
    WHERE id_dia_permiso = wid_dia_permiso
      AND fec_delete IS NOT NULL;

    IF FOUND THEN
        RAISE NOTICE 'Día de permiso % restaurado correctamente.', wid_dia_permiso;
        RETURN TRUE;
    END IF;

    RAISE NOTICE 'No se encontró día de permiso eliminado con ID %.', wid_dia_permiso;
    RETURN FALSE;
END;
$$
LANGUAGE PLPGSQL;