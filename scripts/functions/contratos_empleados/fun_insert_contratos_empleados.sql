DELIMITER //

CREATE OR REPLACE FUNCTION fun_insert_contratos_empleados(
    wid_usuario VARCHAR(20),
    wid_area INT,
    wpuesto VARCHAR(50),
    wfecha_inicio_puesto DATE,
    wtipo_contrato VARCHAR(20),
    wdireccion_oficina VARCHAR(100),
    wfecha_fin_puesto DATE
) RETURNS VARCHAR(255)
BEGIN
    DECLARE EXIT HANDLER FOR SQLSTATE '23000' RETURN 'No existe el empleado o área indicada.';
    DECLARE EXIT HANDLER FOR SQLEXCEPTION RETURN 'Ocurrió un error al insertar el contrato o el tipo no es válido.';

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
        COALESCE(@app_current_user, CURRENT_USER()),
        CURRENT_TIMESTAMP
    );

    RETURN 'Contrato insertado correctamente.';
END //

DELIMITER ;