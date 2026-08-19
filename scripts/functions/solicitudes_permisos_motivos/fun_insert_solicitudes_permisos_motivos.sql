CREATE OR REPLACE FUNCTION fun_insert_solicitudes_permisos_motivos(
    wid_permiso t_solicitudes_permisos_motivos.id_permiso%TYPE,
    wid_tipo_permiso t_solicitudes_permisos_motivos.id_tipo_permiso%TYPE,
    wdetalle_motivo t_solicitudes_permisos_motivos.detalle_motivo%TYPE DEFAULT NULL
) RETURNS VARCHAR AS
$$
BEGIN
    IF wid_permiso IS NULL OR wid_permiso <= 0 THEN
        RETURN 'El ID del permiso no es válido.';
    END IF;

    IF wid_tipo_permiso IS NULL OR wid_tipo_permiso <= 0 THEN
        RETURN 'El ID del tipo de permiso no es válido.';
    END IF;

    INSERT INTO t_solicitudes_permisos_motivos (
        id_permiso,
        id_tipo_permiso,
        detalle_motivo,
        usr_insert,
        fec_insert
    )
    VALUES (
        wid_permiso,
        wid_tipo_permiso,
        NULLIF(TRIM(COALESCE(wdetalle_motivo, '')), ''),
        COALESCE(NULLIF(current_setting('app.current_user', true), ''), CURRENT_USER),
        CURRENT_TIMESTAMP
    );

    RETURN 'Motivo de solicitud insertado correctamente.';

EXCEPTION
    WHEN SQLSTATE '23505' THEN
        RETURN 'La relación permiso-motivo ya existe.';
    WHEN SQLSTATE '23503' THEN
        RETURN 'No existe la solicitud o el tipo de permiso indicado.';
    WHEN OTHERS THEN
        RAISE NOTICE 'Error: %', SQLERRM;
        RETURN 'Ocurrió un error al insertar el motivo de solicitud.';
END;
$$
LANGUAGE PLPGSQL;