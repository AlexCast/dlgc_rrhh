CREATE OR REPLACE FUNCTION fun_update_roles_operaciones(
    wid_rol_actual t_roles_operaciones.id_rol%TYPE,
    wid_operacion_actual t_roles_operaciones.id_operacion%TYPE,
    wid_rol_nuevo t_roles_operaciones.id_rol%TYPE,
    wid_operacion_nueva t_roles_operaciones.id_operacion%TYPE
) RETURNS VARCHAR AS
$$
BEGIN
    IF wid_rol_actual IS NULL OR wid_rol_actual <= 0 OR wid_operacion_actual IS NULL OR wid_operacion_actual <= 0 THEN
        RETURN 'La llave actual de la asignación no es válida.';
    END IF;

    IF wid_rol_nuevo IS NULL OR wid_rol_nuevo <= 0 OR wid_operacion_nueva IS NULL OR wid_operacion_nueva <= 0 THEN
        RETURN 'La nueva llave de la asignación no es válida.';
    END IF;

    UPDATE t_roles_operaciones
    SET id_rol = wid_rol_nuevo,
        id_operacion = wid_operacion_nueva,
        usr_update = COALESCE(NULLIF(current_setting('app.current_user', true), ''), CURRENT_USER),
        fec_update = CURRENT_TIMESTAMP
    WHERE id_rol = wid_rol_actual
      AND id_operacion = wid_operacion_actual
      AND fec_delete IS NULL;

    IF FOUND THEN
        RETURN 'Asignación rol-operación actualizada correctamente.';
    END IF;

    RETURN 'No se encontró la asignación activa para actualizar.';

EXCEPTION
    WHEN SQLSTATE '23505' THEN
        RETURN 'La nueva asignación ya existe.';
    WHEN SQLSTATE '23503' THEN
        RETURN 'El rol u operación de destino no existe.';
    WHEN OTHERS THEN
        RAISE NOTICE 'Error: %', SQLERRM;
        RETURN 'Ocurrió un error al actualizar la asignación rol-operación.';
END;
$$
LANGUAGE PLPGSQL;