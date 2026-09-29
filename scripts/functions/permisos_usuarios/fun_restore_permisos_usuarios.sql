CREATE OR REPLACE FUNCTION fun_restore_permisos_usuarios(
    wid_usuario t_usuarios_operaciones.id_usuario%TYPE,
    wid_operacion t_usuarios_operaciones.id_operacion%TYPE
) RETURNS BOOLEAN AS
$$
BEGIN
    UPDATE t_usuarios_operaciones
    SET fec_delete = NULL,
        usr_delete = NULL
    WHERE id_usuario = TRIM(wid_usuario)
      AND id_operacion = wid_operacion
      AND fec_delete IS NOT NULL;

    IF FOUND THEN
        RAISE NOTICE 'Permiso usuario % - operación % restaurado correctamente.', wid_usuario, wid_operacion;
        RETURN TRUE;
    END IF;

    RAISE NOTICE 'No se encontró permiso eliminado para restaurar.';
    RETURN FALSE;
END;
$$
LANGUAGE PLPGSQL;
