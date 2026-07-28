CREATE OR REPLACE FUNCTION fun_listar_permisos_usuarios(
    wid_usuario t_usuarios_operaciones.id_usuario%TYPE,
    wid_operacion t_usuarios_operaciones.id_operacion%TYPE
) RETURNS BOOLEAN AS
$$
DECLARE
    wreg_permiso t_usuarios_operaciones%ROWTYPE;
BEGIN
    IF wid_usuario IS NULL OR TRIM(wid_usuario) = '' OR wid_operacion IS NULL OR wid_operacion <= 0 THEN
        RAISE NOTICE 'Los IDs de usuario y operación deben ser válidos.';
        RETURN FALSE;
    END IF;

    SELECT *
    INTO wreg_permiso
    FROM t_usuarios_operaciones
    WHERE id_usuario = TRIM(wid_usuario)
      AND id_operacion = wid_operacion
      AND fec_delete IS NULL;

    IF NOT FOUND THEN
        RAISE NOTICE 'No existe permiso activo para usuario % y operación %.', wid_usuario, wid_operacion;
        RETURN FALSE;
    END IF;

    RAISE NOTICE 'Usuario: %, Operación: %', wreg_permiso.id_usuario, wreg_permiso.id_operacion;
    RETURN TRUE;

EXCEPTION
    WHEN OTHERS THEN
        RAISE NOTICE 'Error: %', SQLERRM;
        RETURN FALSE;
END;
$$
LANGUAGE PLPGSQL;
