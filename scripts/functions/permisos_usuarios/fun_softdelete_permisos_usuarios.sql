CREATE OR REPLACE FUNCTION fun_softdelete_permisos_usuarios(
    wid_usuario t_usuarios_operaciones.id_usuario%TYPE,
    wid_operacion t_usuarios_operaciones.id_operacion%TYPE
) RETURNS BOOLEAN AS
$$
BEGIN
    UPDATE t_usuarios_operaciones
    SET fec_delete = CURRENT_TIMESTAMP,
        usr_delete = COALESCE(NULLIF(current_setting('app.current_user', true), ''), CURRENT_USER)
    WHERE id_usuario = TRIM(wid_usuario)
      AND id_operacion = wid_operacion
      AND fec_delete IS NULL;

    IF FOUND THEN
        RAISE NOTICE 'Permiso usuario % - operación % eliminado lógicamente.', wid_usuario, wid_operacion;
        RETURN TRUE;
    END IF;

    RAISE NOTICE 'No se encontró permiso activo para eliminar.';
    RETURN FALSE;
END;
$$
LANGUAGE PLPGSQL;
