DELIMITER //

CREATE OR REPLACE FUNCTION fun_update_solicitudes_permisos(
    wid_permiso INT,
    wes_por_horas BOOLEAN,
    wfecha_inicio DATE,
    wfecha_fin DATE,
    whora_inicio TIME,
    whora_fin TIME,
    wmetodo_descuento VARCHAR(20)
) RETURNS VARCHAR(255)
BEGIN
    -- Maneja violaciones del constraint CHECK 'chk_t_solicitudes_permisos_horas' y otros
    DECLARE EXIT HANDLER FOR SQLEXCEPTION RETURN 'Ocurrió un error al actualizar la solicitud de permiso (o no cumple reglas).';

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
        usr_update = COALESCE(@app_current_user, CURRENT_USER()),
        fec_update = CURRENT_TIMESTAMP
    WHERE id_permiso = wid_permiso
      AND estado = 'PENDIENTE'
      AND fec_delete IS NULL;

    IF ROW_COUNT() > 0 THEN
        RETURN 'Solicitud de permiso actualizada correctamente.';
    END IF;

    RETURN 'No se encontró una solicitud pendiente con ese ID para actualizar.';
END //

DELIMITER ;