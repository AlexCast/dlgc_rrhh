CREATE OR REPLACE FUNCTION fun_restore_empleados(
    wid_usuario t_empleados.id_usuario%TYPE
)
RETURNS BOOLEAN AS
$$
BEGIN
    UPDATE t_empleados
    SET
        fec_delete = NULL,
        usr_delete = NULL
    WHERE id_usuario = wid_usuario
      AND fec_delete IS NOT NULL;

    IF FOUND THEN
        RAISE NOTICE 'Empleado % restaurado correctamente.', wid_usuario;
        RETURN TRUE;
    ELSE
        RAISE NOTICE 'No se encontró el empleado eliminado.';
        RETURN FALSE;
    END IF;
END;
$$ LANGUAGE PLPGSQL;