CREATE OR REPLACE FUNCTION fun_insert_solicitudes_permisos(
    wid_empleado t_solicitudes_permisos.id_empleado%TYPE,
    wes_por_horas t_solicitudes_permisos.es_por_horas%TYPE,
    wfecha_inicio t_solicitudes_permisos.fecha_inicio%TYPE,
    wfecha_fin t_solicitudes_permisos.fecha_fin%TYPE,
    whora_inicio t_solicitudes_permisos.hora_inicio%TYPE DEFAULT NULL,
    whora_fin t_solicitudes_permisos.hora_fin%TYPE DEFAULT NULL,
    westado t_solicitudes_permisos.estado%TYPE DEFAULT 'PENDIENTE',
    wid_aprobador t_solicitudes_permisos.id_aprobador%TYPE DEFAULT NULL,
    wobservacion_aprobador t_solicitudes_permisos.observacion_aprobador%TYPE DEFAULT NULL,
    wurl_evidencia t_solicitudes_permisos.url_evidencia%TYPE DEFAULT NULL,
    wmetodo_descuento t_solicitudes_permisos.metodo_descuento%TYPE DEFAULT NULL
) RETURNS VARCHAR AS
$$
BEGIN
    IF wid_empleado IS NULL OR wid_empleado = '' THEN
        RETURN 'El ID del empleado es obligatorio.';
    END IF;

    IF wfecha_inicio IS NULL OR wfecha_fin IS NULL THEN
        RETURN 'Las fechas de inicio y fin son obligatorias.';
    END IF;

    IF wfecha_fin < wfecha_inicio THEN
        RETURN 'La fecha fin no puede ser menor que la fecha inicio.';
    END IF;

    IF COALESCE(wes_por_horas, FALSE) = TRUE THEN
        IF whora_inicio IS NULL OR whora_fin IS NULL OR whora_fin <= whora_inicio THEN
            RETURN 'Para permisos por horas, hora_inicio y hora_fin deben ser válidas.';
        END IF;
    ELSE
        IF whora_inicio IS NOT NULL OR whora_fin IS NOT NULL THEN
            RETURN 'Para permisos por días no se deben enviar horas.';
        END IF;
    END IF;

    INSERT INTO t_solicitudes_permisos (
        id_empleado,
        es_por_horas,
        fecha_inicio,
        fecha_fin,
        hora_inicio,
        hora_fin,
        estado,
        id_aprobador,
        observacion_aprobador,
        url_evidencia,
        metodo_descuento,
        usr_insert,
        fec_insert
    )
    VALUES (
        wid_empleado,
        COALESCE(wes_por_horas, FALSE),
        wfecha_inicio,
        wfecha_fin,
        whora_inicio,
        whora_fin,
        COALESCE(UPPER(TRIM(westado)), 'PENDIENTE'),
        NULLIF(wid_aprobador, ''),
        wobservacion_aprobador,
        wurl_evidencia,
        CASE
            WHEN wmetodo_descuento IS NULL OR TRIM(wmetodo_descuento) = '' THEN NULL
            ELSE UPPER(TRIM(wmetodo_descuento))
        END,
        COALESCE(NULLIF(current_setting('app.current_user', true), ''), CURRENT_USER),
        CURRENT_TIMESTAMP
    );

    RETURN 'Solicitud de permiso insertada correctamente.';

EXCEPTION
    WHEN SQLSTATE '23503' THEN
        RETURN 'No existe el empleado o aprobador indicado.';
    WHEN SQLSTATE '23514' THEN
        RETURN 'Los datos de estado, horas o método de descuento no cumplen las reglas.';
    WHEN OTHERS THEN
        RAISE NOTICE 'Error: %', SQLERRM;
        RETURN 'Ocurrió un error al insertar la solicitud de permiso.';
END;
$$
LANGUAGE PLPGSQL;