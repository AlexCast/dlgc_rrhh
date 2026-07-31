CREATE OR REPLACE FUNCTION fun_update_solicitudes_permisos(
    wid_permiso t_solicitudes_permisos.id_permiso%TYPE,
    wes_por_horas t_solicitudes_permisos.es_por_horas%TYPE,
    wfecha_inicio t_solicitudes_permisos.fecha_inicio%TYPE,
    wfecha_fin t_solicitudes_permisos.fecha_fin%TYPE,
    whora_inicio t_solicitudes_permisos.hora_inicio%TYPE DEFAULT NULL,
    whora_fin t_solicitudes_permisos.hora_fin%TYPE DEFAULT NULL,
    westado t_solicitudes_permisos.estado%TYPE DEFAULT NULL,
    wid_aprobador t_solicitudes_permisos.id_aprobador%TYPE DEFAULT NULL,
    wobservacion_aprobador t_solicitudes_permisos.observacion_aprobador%TYPE DEFAULT NULL,
    wurl_evidencia t_solicitudes_permisos.url_evidencia%TYPE DEFAULT NULL,
    wmetodo_descuento t_solicitudes_permisos.metodo_descuento%TYPE DEFAULT NULL
) RETURNS VARCHAR AS
$$
BEGIN
    IF wid_permiso IS NULL OR wid_permiso <= 0 THEN
        RETURN 'El ID del permiso no es válido.';
    END IF;

    IF wfecha_inicio IS NULL OR wfecha_fin IS NULL OR wfecha_fin < wfecha_inicio THEN
        RETURN 'Las fechas del permiso no son válidas.';
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

    IF westado IS NULL OR TRIM(westado) = '' THEN
        RETURN 'El estado de la solicitud es obligatorio.';
    END IF;

    UPDATE t_solicitudes_permisos
    SET es_por_horas = COALESCE(wes_por_horas, FALSE),
        fecha_inicio = wfecha_inicio,
        fecha_fin = wfecha_fin,
        hora_inicio = whora_inicio,
        hora_fin = whora_fin,
        estado = UPPER(TRIM(westado)),
        id_aprobador = NULLIF(wid_aprobador, ''),
        observacion_aprobador = wobservacion_aprobador,
        url_evidencia = wurl_evidencia,
        metodo_descuento = CASE
            WHEN wmetodo_descuento IS NULL OR TRIM(wmetodo_descuento) = '' THEN NULL
            ELSE UPPER(TRIM(wmetodo_descuento))
        END,
        usr_update = COALESCE(NULLIF(current_setting('app.current_user', true), ''), CURRENT_USER),
        fec_update = CURRENT_TIMESTAMP
    WHERE id_permiso = wid_permiso
      AND fec_delete IS NULL;

    IF FOUND THEN
        RETURN 'Solicitud de permiso actualizada correctamente.';
    END IF;

    RETURN 'No se encontró la solicitud activa para actualizar.';

EXCEPTION
    WHEN SQLSTATE '23503' THEN
        RETURN 'No existe el aprobador indicado.';
    WHEN SQLSTATE '23514' THEN
        RETURN 'Los datos de estado, horas o método de descuento no cumplen las reglas.';
    WHEN OTHERS THEN
        RAISE NOTICE 'Error: %', SQLERRM;
        RETURN 'Ocurrió un error al actualizar la solicitud de permiso.';
END;
$$
LANGUAGE PLPGSQL;