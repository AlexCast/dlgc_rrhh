DELIMITER //

CREATE OR REPLACE PROCEDURE proc_insert_solicitudes_permisos(
    IN wid_empleado VARCHAR(20),
    IN wid_jefe_responsable VARCHAR(20),
    IN wes_por_horas BOOLEAN,
    IN wfecha_inicio DATE,
    IN wfecha_fin DATE,
    IN wtipos_permiso JSON,
    IN wdetalles_motivo JSON,
    IN whora_inicio TIME,
    IN whora_fin TIME,
    IN wmetodo_descuento VARCHAR(20)
)
proc_main: BEGIN
    DECLARE vid_permiso INT;
    DECLARE vactor VARCHAR(255);
    DECLARE i INT DEFAULT 0;
    DECLARE num_motivos INT;

    -- Manejador para llaves foráneas inexistentes
    DECLARE EXIT HANDLER FOR SQLSTATE '23000'
    BEGIN
        SELECT FALSE AS exito, 'No existe el empleado, jefe o tipo de permiso indicado.' AS mensaje, NULL AS id_permiso;
    END;

    -- Manejador para violaciones de validaciones (CHECK) y errores generales
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        SELECT FALSE AS exito, 'Ocurrió un error al crear la solicitud de permiso (o no cumple reglas).' AS mensaje, NULL AS id_permiso;
    END;

    SET vactor = COALESCE(@app_current_user, CURRENT_USER());

    IF wid_empleado IS NULL OR wid_empleado = '' THEN
        SELECT FALSE AS exito, 'El ID del empleado es obligatorio.' AS mensaje, NULL AS id_permiso;
        LEAVE proc_main;
    END IF;

    IF wid_jefe_responsable IS NULL OR wid_jefe_responsable = '' THEN
        SELECT FALSE AS exito, 'El empleado no tiene jefe directo asignado; contacta a RRHH.' AS mensaje, NULL AS id_permiso;
        LEAVE proc_main;
    END IF;

    IF wfecha_inicio IS NULL OR wfecha_fin IS NULL THEN
        SELECT FALSE AS exito, 'Las fechas de inicio y fin son obligatorias.' AS mensaje, NULL AS id_permiso;
        LEAVE proc_main;
    END IF;

    IF wfecha_fin < wfecha_inicio THEN
        SELECT FALSE AS exito, 'La fecha fin no puede ser menor que la fecha inicio.' AS mensaje, NULL AS id_permiso;
        LEAVE proc_main;
    END IF;

    IF COALESCE(wes_por_horas, FALSE) = TRUE THEN
        IF whora_inicio IS NULL OR whora_fin IS NULL OR whora_fin <= whora_inicio THEN
            SELECT FALSE AS exito, 'Para permisos por horas, hora_inicio y hora_fin deben ser válidas.' AS mensaje, NULL AS id_permiso;
            LEAVE proc_main;
        END IF;
    ELSE
        IF whora_inicio IS NOT NULL OR whora_fin IS NOT NULL THEN
            SELECT FALSE AS exito, 'Para permisos por días no se deben enviar horas.' AS mensaje, NULL AS id_permiso;
            LEAVE proc_main;
        END IF;
    END IF;

    -- Las vacaciones no requieren motivo: se identifican por metodo_descuento = 'VACACIONES'.
    -- Cualquier otra solicitud sí debe traer al menos un motivo.
    SET num_motivos = COALESCE(JSON_LENGTH(wtipos_permiso), 0);
    IF num_motivos = 0 AND UPPER(TRIM(COALESCE(wmetodo_descuento, ''))) <> 'VACACIONES' THEN
        SELECT FALSE AS exito, 'Debes seleccionar al menos un motivo.' AS mensaje, NULL AS id_permiso;
        LEAVE proc_main;
    END IF;

    INSERT INTO t_solicitudes_permisos (
        id_empleado, id_jefe_responsable, es_por_horas, fecha_inicio, fecha_fin,
        hora_inicio, hora_fin, estado, metodo_descuento, usr_insert, fec_insert
    )
    VALUES (
        wid_empleado, wid_jefe_responsable, COALESCE(wes_por_horas, FALSE), wfecha_inicio, wfecha_fin,
        whora_inicio, whora_fin, 'PENDIENTE',
        CASE WHEN wmetodo_descuento IS NULL OR TRIM(wmetodo_descuento) = '' THEN NULL ELSE UPPER(TRIM(wmetodo_descuento)) END,
        vactor, CURRENT_TIMESTAMP
    );

    -- Recupera el ID del registro recién insertado de forma atómica en esta sesión
    SET vid_permiso = LAST_INSERT_ID();

    -- Itera sobre los arrays en formato JSON
    WHILE i < num_motivos DO
        INSERT INTO t_solicitudes_permisos_motivos (id_permiso, id_tipo_permiso, detalle_motivo, usr_insert, fec_insert)
        VALUES (
            vid_permiso, 
            JSON_UNQUOTE(JSON_EXTRACT(wtipos_permiso, CONCAT('$[', i, ']'))), 
            NULLIF(TRIM(JSON_UNQUOTE(JSON_EXTRACT(wdetalles_motivo, CONCAT('$[', i, ']')))), ''), 
            vactor, 
            CURRENT_TIMESTAMP
        );
        SET i = i + 1;
    END WHILE;

    INSERT INTO t_permisos_aprobaciones (id_permiso, nivel_aprobacion, id_aprobador, estado, usr_insert, fec_insert)
    VALUES
        (vid_permiso, 'JEFE', wid_jefe_responsable, 'PENDIENTE', vactor, CURRENT_TIMESTAMP),
        (vid_permiso, 'RRHH', NULL, 'PENDIENTE', vactor, CURRENT_TIMESTAMP);

    SELECT TRUE AS exito, 'Solicitud de permiso creada correctamente.' AS mensaje, vid_permiso AS id_permiso;
END //

DELIMITER ;