CREATE OR REPLACE FUNCTION fun_softdelete_roles_operaciones(
    wid_rol t_roles_operaciones.id_rol%TYPE,
    wid_operacion t_roles_operaciones.id_operacion%TYPE
) RETURNS BOOLEAN AS
$$
BEGIN
    UPDATE t_roles_operaciones
    SET fec_delete = CURRENT_TIMESTAMP,
        usr_delete = COALESCE(NULLIF(current_setting('app.current_user', true), ''), CURRENT_USER)
    WHERE id_rol = wid_rol
      AND id_operacion = wid_operacion
      AND fec_delete IS NULL;

    IF FOUND THEN
        RAISE NOTICE 'Asignación rol % - operación % eliminada lógicamente.', wid_rol, wid_operacion;
        RETURN TRUE;
    END IF;

    RAISE NOTICE 'No se encontró asignación activa para eliminar.';
    RETURN FALSE;
END;
$$
LANGUAGE PLPGSQL;