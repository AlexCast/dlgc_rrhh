CREATE OR REPLACE FUNCTION fun_insert_contratos_empleados(
    wid_usuario t_contratos_empleados.id_usuario%TYPE,
    wid_area t_contratos_empleados.id_area%TYPE,
    wpuesto t_contratos_empleados.puesto%TYPE,
    wfecha_inicio_puesto t_contratos_empleados.fecha_inicio_puesto%TYPE,
    wtipo_contrato t_contratos_empleados.tipo_contrato%TYPE,
    wdireccion_oficina t_contratos_empleados.direccion_oficina%TYPE,
    wfecha_fin_puesto t_contratos_empleados.fecha_fin_puesto%TYPE DEFAULT NULL
) RETURNS VARCHAR AS
$$
BEGIN
    IF wid_usuario IS NULL OR wid_usuario = '' THEN
        RETURN 'El ID de usuario es obligatorio.';
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

    INSERT INTO t_contratos_empleados (
        id_usuario,
        id_area,
        puesto,
        fecha_inicio_puesto,
        fecha_fin_puesto,
        tipo_contrato,
        direccion_oficina,
        usr_insert,
        fec_insert
    )
    VALUES (
        wid_usuario,
        wid_area,
        TRIM(wpuesto),
        wfecha_inicio_puesto,
        wfecha_fin_puesto,
        UPPER(TRIM(wtipo_contrato)),
        TRIM(wdireccion_oficina),
        COALESCE(NULLIF(current_setting('app.current_user', true), ''), CURRENT_USER),
        CURRENT_TIMESTAMP
    );

    RETURN 'Contrato insertado correctamente.';

EXCEPTION
    WHEN SQLSTATE '23503' THEN
        RETURN 'No existe el empleado o área indicada.';
    WHEN SQLSTATE '23514' THEN
        RETURN 'El tipo de contrato indicado no es válido.';
    WHEN OTHERS THEN
        RAISE NOTICE 'Error: %', SQLERRM;
        RETURN 'Ocurrió un error al insertar el contrato.';
END;
$$
LANGUAGE PLPGSQL;