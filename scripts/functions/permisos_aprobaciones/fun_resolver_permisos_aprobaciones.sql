-- Resuelve (aprueba/rechaza) el nivel de aprobación (JEFE o RRHH) de una solicitud y recalcula
-- el estado agregado en t_solicitudes_permisos. Reglas de autorización (quién puede resolver cada
-- nivel) se validan aquí para que nunca queden solo del lado de PHP:
--   - Nivel JEFE: solo puede resolverla el usuario que quedó fijado como id_aprobador al crear
--     la solicitud (el jefe directo de ese momento).
--   - Nivel RRHH: cualquier usuario puede resolverla (la verificación de que tenga permiso del
--     módulo 27 la hace el endpoint PHP); wid_actor se guarda como el id_aprobador definitivo.
-- Depende de fun_calcular_saldo_vacaciones y fun_dias_habiles_rango (scripts/functions/vacaciones/):
-- al aprobar en RRHH con metodo_descuento = 'VACACIONES' se valida que el saldo disponible del
-- empleado alcance para los días solicitados; no se permiten anticipos (saldo negativo).
CREATE OR REPLACE FUNCTION fun_resolver_permisos_aprobaciones(
    wid_permiso t_permisos_aprobaciones.id_permiso%TYPE,
    wnivel_aprobacion t_permisos_aprobaciones.nivel_aprobacion%TYPE,
    wid_actor t_permisos_aprobaciones.id_aprobador%TYPE,
    waccion t_permisos_aprobaciones.estado%TYPE,
    wobservacion t_permisos_aprobaciones.observacion%TYPE DEFAULT NULL,
    wmetodo_descuento t_solicitudes_permisos.metodo_descuento%TYPE DEFAULT NULL
) RETURNS TABLE (exito BOOLEAN, mensaje VARCHAR, nuevo_estado_solicitud VARCHAR) AS
$$
DECLARE
    vfila t_permisos_aprobaciones%ROWTYPE;
    vactor VARCHAR;
    vestado_jefe VARCHAR;
    vestado_rrhh VARCHAR;
    vestado_final VARCHAR;
    vsolicitud t_solicitudes_permisos%ROWTYPE;
    vdias_solicitados NUMERIC;
    vsaldo NUMERIC;
BEGIN
    vactor := COALESCE(NULLIF(current_setting('app.current_user', true), ''), CURRENT_USER);
    wnivel_aprobacion := UPPER(TRIM(COALESCE(wnivel_aprobacion, '')));
    waccion := UPPER(TRIM(COALESCE(waccion, '')));

    IF wnivel_aprobacion NOT IN ('JEFE', 'RRHH') THEN
        RETURN QUERY SELECT FALSE, 'Nivel de aprobación no válido.'::VARCHAR, NULL::VARCHAR;
        RETURN;
    END IF;

    IF waccion NOT IN ('APROBADO', 'RECHAZADO') THEN
        RETURN QUERY SELECT FALSE, 'La acción debe ser APROBADO o RECHAZADO.'::VARCHAR, NULL::VARCHAR;
        RETURN;
    END IF;

    SELECT * INTO vfila
    FROM t_permisos_aprobaciones
    WHERE id_permiso = wid_permiso
      AND nivel_aprobacion = wnivel_aprobacion
      AND fec_delete IS NULL
    FOR UPDATE;

    IF NOT FOUND THEN
        RETURN QUERY SELECT FALSE, 'No existe ese nivel de aprobación para la solicitud.'::VARCHAR, NULL::VARCHAR;
        RETURN;
    END IF;

    IF vfila.estado <> 'PENDIENTE' THEN
        RETURN QUERY SELECT FALSE, 'Ese nivel de aprobación ya fue resuelto anteriormente.'::VARCHAR, NULL::VARCHAR;
        RETURN;
    END IF;

    IF wnivel_aprobacion = 'JEFE' AND vfila.id_aprobador IS DISTINCT FROM wid_actor THEN
        RETURN QUERY SELECT FALSE, 'Solo el jefe directo asignado puede resolver este nivel.'::VARCHAR, NULL::VARCHAR;
        RETURN;
    END IF;

    -- Solo RRHH decide si el permiso es remunerado (dinero/vacaciones) o no, y únicamente al aprobar.
    IF wnivel_aprobacion = 'RRHH' AND waccion = 'APROBADO' THEN
        wmetodo_descuento := UPPER(TRIM(COALESCE(wmetodo_descuento, '')));
        IF wmetodo_descuento NOT IN ('DINERO', 'VACACIONES', 'NO') THEN
            RETURN QUERY SELECT FALSE, 'RRHH debe indicar si el permiso es remunerado (Dinero, Vacaciones o No).'::VARCHAR, NULL::VARCHAR;
            RETURN;
        END IF;

        -- Vacaciones: no se permiten anticipos. El saldo disponible debe alcanzar
        -- para cubrir los días (o su equivalente en horas) de esta solicitud.
        IF wmetodo_descuento = 'VACACIONES' THEN
            SELECT * INTO vsolicitud FROM t_solicitudes_permisos WHERE id_permiso = wid_permiso;

            vdias_solicitados := CASE WHEN vsolicitud.es_por_horas
                THEN EXTRACT(EPOCH FROM (vsolicitud.hora_fin - vsolicitud.hora_inicio)) / 3600 / 8
                ELSE fun_dias_habiles_rango(vsolicitud.fecha_inicio, vsolicitud.fecha_fin)
            END;

            SELECT saldo_disponible INTO vsaldo FROM fun_calcular_saldo_vacaciones(vsolicitud.id_empleado);

            IF vsaldo IS NULL THEN
                RETURN QUERY SELECT FALSE, 'No se pudo calcular el saldo de vacaciones del empleado.'::VARCHAR, NULL::VARCHAR;
                RETURN;
            END IF;

            IF vdias_solicitados > vsaldo THEN
                RETURN QUERY SELECT FALSE, format('Saldo de vacaciones insuficiente: disponible %s día(s), solicitado %s.', vsaldo, vdias_solicitados)::VARCHAR, NULL::VARCHAR;
                RETURN;
            END IF;
        END IF;

        UPDATE t_solicitudes_permisos
        SET metodo_descuento = CASE WHEN wmetodo_descuento = 'NO' THEN 'N/A' ELSE wmetodo_descuento END,
            usr_update = vactor,
            fec_update = CURRENT_TIMESTAMP
        WHERE id_permiso = wid_permiso;
    END IF;

    UPDATE t_permisos_aprobaciones
    SET estado = waccion,
        id_aprobador = COALESCE(vfila.id_aprobador, wid_actor),
        observacion = wobservacion,
        fec_resolucion = CURRENT_TIMESTAMP,
        usr_update = vactor,
        fec_update = CURRENT_TIMESTAMP
    WHERE id_aprobacion = vfila.id_aprobacion;

    SELECT estado INTO vestado_jefe FROM t_permisos_aprobaciones WHERE id_permiso = wid_permiso AND nivel_aprobacion = 'JEFE';
    SELECT estado INTO vestado_rrhh FROM t_permisos_aprobaciones WHERE id_permiso = wid_permiso AND nivel_aprobacion = 'RRHH';

    vestado_final := CASE
        WHEN vestado_jefe = 'RECHAZADO' OR vestado_rrhh = 'RECHAZADO' THEN 'RECHAZADO'
        WHEN vestado_jefe = 'APROBADO' AND vestado_rrhh = 'APROBADO' THEN 'APROBADO'
        WHEN vestado_jefe = 'APROBADO' OR vestado_rrhh = 'APROBADO' THEN 'EN_REVISION'
        ELSE 'PENDIENTE'
    END;

    UPDATE t_solicitudes_permisos
    SET estado = vestado_final,
        usr_update = vactor,
        fec_update = CURRENT_TIMESTAMP
    WHERE id_permiso = wid_permiso;

    RETURN QUERY SELECT TRUE, 'Aprobación registrada correctamente.'::VARCHAR, vestado_final;

EXCEPTION
    WHEN OTHERS THEN
        RAISE NOTICE 'Error: %', SQLERRM;
        RETURN QUERY SELECT FALSE, 'Ocurrió un error al resolver la aprobación.'::VARCHAR, NULL::VARCHAR;
END;
$$
LANGUAGE PLPGSQL;
