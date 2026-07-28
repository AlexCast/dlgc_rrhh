CREATE OR REPLACE FUNCTION fun_update_permisos_usuarios(
    wid_usuario_actual t_usuarios_operaciones.id_usuario%TYPE,
    wid_operacion_actual t_usuarios_operaciones.id_operacion%TYPE,
    wid_usuario_nuevo t_usuarios_operaciones.id_usuario%TYPE,
    wid_operacion_nueva t_usuarios_operaciones.id_operacion%TYPE
) RETURNS VARCHAR AS
$$
BEGIN
    IF wid_usuario_actual IS NULL OR TRIM(wid_usuario_actual) = '' OR wid_operacion_actual IS NULL OR wid_operacion_actual <= 0 THEN
        RETURN 'La llave actual del permiso no es válida.';
    END IF;

    IF wid_usuario_nuevo IS NULL OR TRIM(wid_usuario_nuevo) = '' OR wid_operacion_nueva IS NULL OR wid_operacion_nueva <= 0 THEN
        RETURN 'La nueva llave del permiso no es válida.';
    END IF;

    UPDATE t_usuarios_operaciones
    SET id_usuario = TRIM(wid_usuario_nuevo),
        id_operacion = wid_operacion_nueva,
        usr_update = COALESCE(NULLIF(current_setting('app.current_user', true), ''), CURRENT_USER),
        fec_update = CURRENT_TIMESTAMP
    WHERE id_usuario = TRIM(wid_usuario_actual)
      AND id_operacion = wid_operacion_actual
      AND fec_delete IS NULL;

    IF FOUND THEN
        RETURN 'Permiso individual actualizado correctamente.';
    END IF;

    RETURN 'No se encontró el permiso activo para actualizar.';

EXCEPTION
    WHEN SQLSTATE '23505' THEN
        RETURN 'El nuevo permiso individual ya existe.';
    WHEN SQLSTATE '23503' THEN
        RETURN 'El usuario u operación de destino no existe.';
    WHEN OTHERS THEN
        RAISE NOTICE 'Error: %', SQLERRM;
        RETURN 'Ocurrió un error al actualizar el permiso individual.';
END;
$$
LANGUAGE PLPGSQL;
