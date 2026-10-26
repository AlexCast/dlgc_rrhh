DELIMITER //

CREATE OR REPLACE FUNCTION fun_insert_permisos_dias_aprobados(
    wid_permiso INT,
    wid_empleado VARCHAR(20),
    wfecha DATE,
    whoras_aprobadas DECIMAL(4,2)
) RETURNS VARCHAR(255)
BEGIN
    DECLARE EXIT HANDLER FOR SQLSTATE '23000' RETURN 'No existe el permiso o empleado indicado.';
    DECLARE EXIT HANDLER FOR SQLEXCEPTION RETURN 'Las horas aprobadas no cumplen las reglas de la tabla u ocurrió un error.';

    IF wid_permiso IS NULL OR wid_permiso <= 0 THEN
        RETURN 'El ID del permiso no es válido.';
    END IF;

    IF wid_empleado IS NULL OR wid_empleado = '' THEN
        RETURN 'El ID del empleado es obligatorio.';
    END IF;

    IF wfecha IS NULL THEN
        RETURN 'La fecha es obligatoria.';
    END IF;

    IF whoras_aprobadas IS NULL OR whoras_aprobadas <= 0 OR whoras_aprobadas > 24 THEN
        RETURN 'Las horas aprobadas deben ser mayores que 0 y no superar 24.';
    END IF;

    INSERT INTO t_permisos_dias_aprobados (
        id_permiso,
        id_empleado,
        fecha,
        horas_aprobadas,
        usr_insert,
        fec_insert
    )
    VALUES (
        wid_permiso,
        wid_empleado,
        wfecha,
        whoras_aprobadas,
        COALESCE(@app_current_user, CURRENT_USER()),
        CURRENT_TIMESTAMP
    );

    RETURN 'Día de permiso aprobado insertado correctamente.';
END //

DELIMITER ;