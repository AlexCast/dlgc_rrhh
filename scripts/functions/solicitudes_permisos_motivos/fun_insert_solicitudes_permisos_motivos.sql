DELIMITER //

CREATE OR REPLACE FUNCTION fun_insert_solicitudes_permisos_motivos(
    wid_permiso INT,
    wid_tipo_permiso INT,
    wdetalle_motivo VARCHAR(255)
) RETURNS VARCHAR(255)
BEGIN
    -- En MariaDB SQLSTATE '23000' cubre violaciones de llave foránea y duplicidad
    DECLARE EXIT HANDLER FOR SQLSTATE '23000' RETURN 'La relación permiso-motivo ya existe o no existe la solicitud/tipo.';
    DECLARE EXIT HANDLER FOR SQLEXCEPTION RETURN 'Ocurrió un error al insertar el motivo de solicitud.';

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
        COALESCE(@app_current_user, CURRENT_USER()),
        CURRENT_TIMESTAMP
    );

    RETURN 'Motivo de solicitud insertado correctamente.';
END //

DELIMITER ;