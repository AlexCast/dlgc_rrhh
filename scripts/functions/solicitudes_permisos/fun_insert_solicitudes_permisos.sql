-- Crea una solicitud de permiso de forma atómica: la solicitud, sus motivos (N:M) y las
-- 2 filas de aprobación (JEFE y RRHH) quedan en una sola transacción implícita de la función.
-- wtipos_permiso/wdetalles_motivo son arreglos paralelos (mismo índice = mismo motivo).
-- El nivel JEFE queda asignado a wid_jefe_responsable; el nivel RRHH queda sin aprobador fijo
-- (cualquier usuario con permiso del módulo 27 puede resolverlo).
CREATE OR REPLACE FUNCTION fun_insert_solicitudes_permisos(
    wid_empleado t_solicitudes_permisos.id_empleado%TYPE,
    wid_jefe_responsable t_solicitudes_permisos.id_jefe_responsable%TYPE,
    wes_por_horas t_solicitudes_permisos.es_por_horas%TYPE,
    wfecha_inicio t_solicitudes_permisos.fecha_inicio%TYPE,
    wfecha_fin t_solicitudes_permisos.fecha_fin%TYPE,
    wtipos_permiso INT[],
    wdetalles_motivo VARCHAR[],
    whora_inicio t_solicitudes_permisos.hora_inicio%TYPE DEFAULT NULL,
    whora_fin t_solicitudes_permisos.hora_fin%TYPE DEFAULT NULL,
    wmetodo_descuento t_solicitudes_permisos.metodo_descuento%TYPE DEFAULT NULL
) RETURNS TABLE (exito BOOLEAN, mensaje VARCHAR, id_permiso INT) AS
$$
DECLARE
    vid_permiso INT;
    vactor VARCHAR;
    i INT;
BEGIN
    vactor := COALESCE(NULLIF(current_setting('app.current_user', true), ''), CURRENT_USER);

    IF wid_empleado IS NULL OR wid_empleado = '' THEN
        RETURN QUERY SELECT FALSE, 'El ID del empleado es obligatorio.'::VARCHAR, NULL::INT;
        RETURN;
    END IF;

    IF wid_jefe_responsable IS NULL OR wid_jefe_responsable = '' THEN
        RETURN QUERY SELECT FALSE, 'El empleado no tiene jefe directo asignado; contacta a RRHH.'::VARCHAR, NULL::INT;
        RETURN;
    END IF;

    IF wfecha_inicio IS NULL OR wfecha_fin IS NULL THEN
        RETURN QUERY SELECT FALSE, 'Las fechas de inicio y fin son obligatorias.'::VARCHAR, NULL::INT;
        RETURN;
    END IF;

    IF wfecha_fin < wfecha_inicio THEN
        RETURN QUERY SELECT FALSE, 'La fecha fin no puede ser menor que la fecha inicio.'::VARCHAR, NULL::INT;
        RETURN;
    END IF;

    IF COALESCE(wes_por_horas, FALSE) = TRUE THEN
        IF whora_inicio IS NULL OR whora_fin IS NULL OR whora_fin <= whora_inicio THEN
            RETURN QUERY SELECT FALSE, 'Para permisos por horas, hora_inicio y hora_fin deben ser válidas.'::VARCHAR, NULL::INT;
            RETURN;
        END IF;
    ELSE
        IF whora_inicio IS NOT NULL OR whora_fin IS NOT NULL THEN
            RETURN QUERY SELECT FALSE, 'Para permisos por días no se deben enviar horas.'::VARCHAR, NULL::INT;
            RETURN;
        END IF;
    END IF;

    IF wtipos_permiso IS NULL OR array_length(wtipos_permiso, 1) IS NULL THEN
        RETURN QUERY SELECT FALSE, 'Debes seleccionar al menos un motivo.'::VARCHAR, NULL::INT;
        RETURN;
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
    )
    RETURNING t_solicitudes_permisos.id_permiso INTO vid_permiso; -- id_permiso es ambiguo: coincide con el OUT param de RETURNS TABLE

    FOR i IN 1 .. array_length(wtipos_permiso, 1) LOOP
        INSERT INTO t_solicitudes_permisos_motivos (id_permiso, id_tipo_permiso, detalle_motivo, usr_insert, fec_insert)
        VALUES (vid_permiso, wtipos_permiso[i], NULLIF(TRIM(COALESCE(wdetalles_motivo[i], '')), ''), vactor, CURRENT_TIMESTAMP);
    END LOOP;

    INSERT INTO t_permisos_aprobaciones (id_permiso, nivel_aprobacion, id_aprobador, estado, usr_insert, fec_insert)
    VALUES
        (vid_permiso, 'JEFE', wid_jefe_responsable, 'PENDIENTE', vactor, CURRENT_TIMESTAMP),
        (vid_permiso, 'RRHH', NULL, 'PENDIENTE', vactor, CURRENT_TIMESTAMP);

    RETURN QUERY SELECT TRUE, 'Solicitud de permiso creada correctamente.'::VARCHAR, vid_permiso;

EXCEPTION
    WHEN SQLSTATE '23503' THEN
        RETURN QUERY SELECT FALSE, 'No existe el empleado, jefe o tipo de permiso indicado.'::VARCHAR, NULL::INT;
    WHEN SQLSTATE '23514' THEN
        RETURN QUERY SELECT FALSE, 'Los datos de horas o método de descuento no cumplen las reglas.'::VARCHAR, NULL::INT;
    WHEN OTHERS THEN
        RAISE NOTICE 'Error: %', SQLERRM;
        RETURN QUERY SELECT FALSE, 'Ocurrió un error al crear la solicitud de permiso.'::VARCHAR, NULL::INT;
END;
$$
LANGUAGE PLPGSQL;
