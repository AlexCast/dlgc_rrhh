CREATE OR REPLACE FUNCTION fun_restore_roles(
    wid_rol t_roles.id_rol%TYPE
) RETURNS BOOLEAN AS
$$
BEGIN
    UPDATE t_roles
    SET fec_delete = NULL,
        usr_delete = NULL
    WHERE id_rol = wid_rol
      AND fec_delete IS NOT NULL;

    IF FOUND THEN
        RAISE NOTICE 'Rol % restaurado correctamente.', wid_rol;
        RETURN TRUE;
    END IF;

    RAISE NOTICE 'No se encontró rol eliminado con ID %.', wid_rol;
    RETURN FALSE;
END;
$$
LANGUAGE PLPGSQL;