CREATE OR REPLACE FUNCTION fun_softdelete_roles(
    wid_rol t_roles.id_rol%TYPE
) RETURNS BOOLEAN AS
$$
BEGIN
    UPDATE t_roles
    SET fec_delete = CURRENT_TIMESTAMP,
        usr_delete = COALESCE(NULLIF(current_setting('app.current_user', true), ''), CURRENT_USER)
    WHERE id_rol = wid_rol
      AND fec_delete IS NULL;

    IF FOUND THEN
        RAISE NOTICE 'Rol % eliminado lógicamente.', wid_rol;
        RETURN TRUE;
    END IF;

    RAISE NOTICE 'No se encontró rol activo con ID %.', wid_rol;
    RETURN FALSE;
END;
$$
LANGUAGE PLPGSQL;