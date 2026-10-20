DELIMITER //

CREATE OR REPLACE FUNCTION fun_update_contratos_empleados(
    wid_usuario VARCHAR(20),
    wid_contrato INT,
    wid_area INT,
    wpuesto VARCHAR(50),
    wfecha_inicio_puesto DATE,
    wtipo_contrato VARCHAR(20),
    wdireccion_oficina VARCHAR(100),
    wfecha_fin_puesto DATE
) RETURNS VARCHAR(255)
BEGIN
    DECLARE EXIT HANDLER FOR SQLSTATE '23000' RETURN 'No existe el área indicada.';
    DECLARE EXIT HANDLER FOR SQLEXCEPTION RETURN 'Ocurrió un error al actualizar el contrato o el tipo no es válido.';

    IF wid_contrato IS NULL OR wid_contrato <= 0 THEN
        RETURN 'El ID del contrato no es válido.';
    END IF;
    IF wid_usuario IS NULL OR wid_usuario = '' THEN
        RETURN 'El ID del usuario no es válido.';
    END IF;
    IF wid_area IS NULL OR wid_area <= 0 THEN
        RETURN 'El ID del área no es válido.';
    END IF;

    IF wpuesto IS NULL OR LENGTH(TRIM(wpuesto)) < 3 THEN
        RETURN 'El puesto debe tener al menos 3 caracteres.';
    END IF;

    IF wfecha_inicio_puesto IS NULL THEN
        RETURN 'La fecha de inicio del puesto es obligatoria.';
    END IF;

    IF wfecha_fin_puesto IS NOT NULL AND wfecha_fin_puesto < wfecha_inicio_puesto THEN
        RETURN 'La fecha fin no puede ser menor que la fecha inicio.';
    END IF;

    IF wdireccion_oficina IS NULL OR LENGTH(TRIM(wdireccion_oficina)) < 5 THEN
        RETURN 'La dirección de oficina debe tener al menos 5 caracteres.';
    END IF;

    UPDATE t_contratos_empleados
    SET id_area = wid_area,
        puesto = TRIM(wpuesto),
        fecha_inicio_puesto = wfecha_inicio_puesto,
        fecha_fin_puesto = wfecha_fin_puesto,
        tipo_contrato = UPPER(TRIM(wtipo_contrato)),
        direccion_oficina = TRIM(wdireccion_oficina),
        usr_update = COALESCE(@app_current_user, CURRENT_USER()),
        fec_update = CURRENT_TIMESTAMP
    WHERE id_contrato = wid_contrato
      AND fec_delete IS NULL;

    IF ROW_COUNT() > 0 THEN
        RETURN 'Contrato actualizado correctamente.';
    END IF;

    RETURN 'No se encontró el contrato activo para actualizar.';
END //

DELIMITER ;