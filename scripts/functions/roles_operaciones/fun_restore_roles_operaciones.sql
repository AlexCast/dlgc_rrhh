CREATE OR REPLACE FUNCTION fun_restore_roles_operaciones(
    wid_rol t_roles_operaciones.id_rol%TYPE,
    wid_operacion t_roles_operaciones.id_operacion%TYPE
) RETURNS BOOLEAN AS
$$
BEGIN
    UPDATE t_roles_operaciones
    SET fec_delete = NULL,
        usr_delete = NULL
    WHERE id_rol = wid_rol
      AND id_operacion = wid_operacion
      AND fec_delete IS NOT NULL;

    IF FOUND THEN
        RAISE NOTICE 'Asignación rol % - operación % restaurada correctamente.', wid_rol, wid_operacion;
        RETURN TRUE;
    END IF;

    RAISE NOTICE 'No se encontró asignación eliminada para restaurar.';
    RETURN FALSE;
END;
$$
LANGUAGE PLPGSQL;