-- Edita los datos propios de la solicitud (fechas/horas/método de descuento) mientras sigue
-- PENDIENTE. El estado agregado y las observaciones de aprobación ya NO se tocan aquí:
-- eso lo resuelve fun_resolver_aprobacion_permiso sobre t_permisos_aprobaciones.
CREATE OR REPLACE FUNCTION fun_update_solicitudes_permisos(
    wid_permiso t_solicitudes_permisos.id_permiso%TYPE,
    wes_por_horas t_solicitudes_permisos.es_por_horas%TYPE,
    wfecha_inicio t_solicitudes_permisos.fecha_inicio%TYPE,
    wfecha_fin t_solicitudes_permisos.fecha_fin%TYPE,
    whora_inicio t_solicitudes_permisos.hora_inicio%TYPE DEFAULT NULL,
    whora_fin t_solicitudes_permisos.hora_fin%TYPE DEFAULT NULL,
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

    UPDATE t_solicitudes_permisos
    SET es_por_horas = COALESCE(wes_por_horas, FALSE),
        fecha_inicio = wfecha_inicio,
        fecha_fin = wfecha_fin,
        hora_inicio = whora_inicio,
        hora_fin = whora_fin,
        metodo_descuento = CASE
            WHEN wmetodo_descuento IS NULL OR TRIM(wmetodo_descuento) = '' THEN NULL
            ELSE UPPER(TRIM(wmetodo_descuento))
        END,
        usr_update = COALESCE(NULLIF(current_setting('app.current_user', true), ''), CURRENT_USER),
        fec_update = CURRENT_TIMESTAMP
    WHERE id_permiso = wid_permiso
      AND estado = 'PENDIENTE'
      AND fec_delete IS NULL;

    IF FOUND THEN
        RETURN 'Solicitud de permiso actualizada correctamente.';
    END IF;

    RETURN 'No se encontró una solicitud pendiente con ese ID para actualizar.';

EXCEPTION
    WHEN SQLSTATE '23514' THEN
        RETURN 'Los datos de horas o método de descuento no cumplen las reglas.';
    WHEN OTHERS THEN
        RAISE NOTICE 'Error: %', SQLERRM;
        RETURN 'Ocurrió un error al actualizar la solicitud de permiso.';
END;
$$
LANGUAGE PLPGSQL;