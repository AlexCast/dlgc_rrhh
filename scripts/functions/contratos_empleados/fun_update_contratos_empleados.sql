CREATE OR REPLACE FUNCTION fun_update_contratos_empleados(
    wid_usuario t_contratos_empleados.id_usuario%TYPE,
    wid_contrato t_contratos_empleados.id_contrato%TYPE,
    wid_area t_contratos_empleados.id_area%TYPE,
    wpuesto t_contratos_empleados.puesto%TYPE,
    wfecha_inicio_puesto t_contratos_empleados.fecha_inicio_puesto%TYPE,
    wtipo_contrato t_contratos_empleados.tipo_contrato%TYPE,
    wdireccion_oficina t_contratos_empleados.direccion_oficina%TYPE,
    wfecha_fin_puesto t_contratos_empleados.fecha_fin_puesto%TYPE DEFAULT NULL
) RETURNS VARCHAR AS
$$
BEGIN
    IF wid_contrato IS NULL OR wid_contrato <= 0 THEN
        RETURN 'El ID del contrato no es válido.';
    END IF;
    IF wid_usuario IS NULL OR wid_usuario <= 0 THEN
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
        usr_update = COALESCE(NULLIF(current_setting('app.current_user', true), ''), CURRENT_USER),
        fec_update = CURRENT_TIMESTAMP
    WHERE id_contrato = wid_contrato
      AND fec_delete IS NULL;

    IF FOUND THEN
        RETURN 'Contrato actualizado correctamente.';
    END IF;

    RETURN 'No se encontró el contrato activo para actualizar.';

EXCEPTION
    WHEN SQLSTATE '23503' THEN
        RETURN 'No existe el área indicada.';
    WHEN SQLSTATE '23514' THEN
        RETURN 'El tipo de contrato indicado no es válido.';
    WHEN OTHERS THEN
        RAISE NOTICE 'Error: %', SQLERRM;
        RETURN 'Ocurrió un error al actualizar el contrato.';
END;
$$
LANGUAGE PLPGSQL;