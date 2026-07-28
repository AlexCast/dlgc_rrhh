CREATE OR REPLACE FUNCTION fun_listar_roles_operaciones(
    wid_rol t_roles_operaciones.id_rol%TYPE,
    wid_operacion t_roles_operaciones.id_operacion%TYPE
) RETURNS BOOLEAN AS
$$
DECLARE
    wreg_rel t_roles_operaciones%ROWTYPE;
BEGIN
    IF wid_rol IS NULL OR wid_rol <= 0 OR wid_operacion IS NULL OR wid_operacion <= 0 THEN
        RAISE NOTICE 'Los IDs de rol y operación deben ser válidos.';
        RETURN FALSE;
    END IF;

    SELECT *
    INTO wreg_rel
    FROM t_roles_operaciones
    WHERE id_rol = wid_rol
      AND id_operacion = wid_operacion
      AND fec_delete IS NULL;

    IF NOT FOUND THEN
        RAISE NOTICE 'No existe asignación activa para rol % y operación %.', wid_rol, wid_operacion;
        RETURN FALSE;
    END IF;

    RAISE NOTICE 'Rol: %, Operación: %', wreg_rel.id_rol, wreg_rel.id_operacion;
    RETURN TRUE;

EXCEPTION
    WHEN OTHERS THEN
        RAISE NOTICE 'Error: %', SQLERRM;
        RETURN FALSE;
END;
$$
LANGUAGE PLPGSQL;