CREATE OR REPLACE FUNCTION fun_listar_roles(
    wid_rol t_roles.id_rol%TYPE
) RETURNS BOOLEAN AS
$$
DECLARE
    wreg_rol t_roles%ROWTYPE;
BEGIN
    IF wid_rol IS NULL OR wid_rol <= 0 THEN
        RAISE NOTICE 'El ID del rol no es válido.';
        RETURN FALSE;
    END IF;

    SELECT *
    INTO wreg_rol
    FROM t_roles
    WHERE id_rol = wid_rol
      AND fec_delete IS NULL;

    IF NOT FOUND THEN
        RAISE NOTICE 'No existe rol activo con ID %.', wid_rol;
        RETURN FALSE;
    END IF;

    RAISE NOTICE 'ID: %, Rol: %', wreg_rol.id_rol, wreg_rol.nombre_rol;
    RETURN TRUE;

EXCEPTION
    WHEN OTHERS THEN
        RAISE NOTICE 'Error: %', SQLERRM;
        RETURN FALSE;
END;
$$
LANGUAGE PLPGSQL;