DELIMITER //

CREATE OR REPLACE FUNCTION fun_insert_vacaciones_ajustes(
    wid_empleado VARCHAR(20),
    wdias_ajuste DECIMAL(5,2),
    wmotivo VARCHAR(255),
    wfecha_ajuste DATE
) RETURNS VARCHAR(255)
BEGIN
    DECLARE vfecha_ingreso DATE;
    DECLARE vfecha_egreso DATE;
    DECLARE vfecha_corte DATE;
    DECLARE vanios_servicio INT;
    DECLARE vperiodo_inicio DATE;
    DECLARE vperiodo_fin DATE;
    DECLARE vsaldo_disponible DECIMAL(10,2);

    DECLARE EXIT HANDLER FOR SQLEXCEPTION RETURN 'Ocurrió un error al registrar el ajuste.';

    IF wid_empleado IS NULL OR wid_empleado = '' THEN
        RETURN 'El empleado indicado no existe o está inactivo.';
    END IF;

    SELECT fecha_ingreso, fecha_egreso
    INTO vfecha_ingreso, vfecha_egreso
    FROM t_empleados
    WHERE id_usuario = wid_empleado AND fec_delete IS NULL LIMIT 1;

    IF vfecha_ingreso IS NULL THEN
        RETURN 'El empleado indicado no existe o está inactivo.';
    END IF;

    IF wdias_ajuste IS NULL OR wdias_ajuste >= 0 THEN
        RETURN 'El ajuste debe ser un número de días negativo (solo se permite restar días ya disfrutados).';
    END IF;

    IF wmotivo IS NULL OR LENGTH(TRIM(wmotivo)) < 5 THEN
        RETURN 'El motivo del ajuste debe tener al menos 5 caracteres.';
    END IF;

    SET wfecha_ajuste = COALESCE(wfecha_ajuste, CURRENT_DATE);

    SET vfecha_corte = LEAST(COALESCE(vfecha_egreso, CURRENT_DATE), CURRENT_DATE);
    SET vanios_servicio = GREATEST(0, TIMESTAMPDIFF(YEAR, vfecha_ingreso, vfecha_corte));

    IF vanios_servicio < 1 THEN
        RETURN 'El empleado aún no cumple su primer año de servicio; no tiene un ciclo de vacaciones vigente.';
    END IF;

    SET vperiodo_inicio = DATE_ADD(vfecha_ingreso, INTERVAL vanios_servicio YEAR);
    SET vperiodo_fin = DATE_SUB(DATE_ADD(vperiodo_inicio, INTERVAL 1 YEAR), INTERVAL 1 DAY);

    IF wfecha_ajuste < vperiodo_inicio OR wfecha_ajuste > vperiodo_fin THEN
        RETURN CONCAT('La fecha del ajuste debe estar dentro del ciclo vigente (', vperiodo_inicio, ' a ', vperiodo_fin, ').');
    END IF;

    -- Utilizamos la función escalar de apoyo creada arriba
    SET vsaldo_disponible = fun_get_saldo_vacaciones(wid_empleado);

    IF ABS(wdias_ajuste) > vsaldo_disponible THEN
        RETURN CONCAT('No puedes restar ', ABS(wdias_ajuste), ' días: el saldo disponible actual es de solo ', vsaldo_disponible, ' días.');
    END IF;

    INSERT INTO t_vacaciones_ajustes (
        id_empleado,
        dias_ajuste,
        motivo,
        fecha_ajuste,
        usr_insert,
        fec_insert
    )
    VALUES (
        wid_empleado,
        wdias_ajuste,
        TRIM(wmotivo),
        wfecha_ajuste,
        COALESCE(@app_current_user, CURRENT_USER()),
        CURRENT_TIMESTAMP
    );

    RETURN 'Ajuste de vacaciones registrado correctamente.';
END //

DELIMITER ;