DELIMITER //

CREATE OR REPLACE PROCEDURE proc_resolver_permisos_aprobaciones(
    IN wid_permiso INT,
    IN wnivel_aprobacion VARCHAR(10),
    IN wid_actor VARCHAR(20),
    IN waccion VARCHAR(20),
    IN wobservacion VARCHAR(255),
    IN wmetodo_descuento VARCHAR(20)
)
proc_main: BEGIN
    -- Variables para la aprobación
    DECLARE v_id_aprobacion INT;
    DECLARE v_estado VARCHAR(20);
    DECLARE v_id_aprobador VARCHAR(20);
    
    -- Variables para estados y sesión
    DECLARE v_actor VARCHAR(255);
    DECLARE v_estado_jefe VARCHAR(20);
    DECLARE v_estado_rrhh VARCHAR(20);
    DECLARE v_estado_final VARCHAR(20);

    -- Variables para la solicitud original
    DECLARE v_es_por_horas BOOLEAN;
    DECLARE v_fecha_inicio DATE;
    DECLARE v_fecha_fin DATE;
    DECLARE v_hora_inicio TIME;
    DECLARE v_hora_fin TIME;
    DECLARE v_id_empleado VARCHAR(20);

    -- Variables de cálculo de vacaciones
    DECLARE vdias_solicitados DECIMAL(10,2);
    DECLARE vsaldo DECIMAL(10,2);
    
    -- Control de cursor
    DECLARE v_not_found BOOLEAN DEFAULT FALSE;

    -- Manejador global de excepciones
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        SELECT FALSE AS exito, 'Ocurrió un error al resolver la aprobación.' AS mensaje, NULL AS nuevo_estado_solicitud;
    END;

    SET v_actor = COALESCE(@app_current_user, CURRENT_USER());
    SET wnivel_aprobacion = UPPER(TRIM(COALESCE(wnivel_aprobacion, '')));
    SET waccion = UPPER(TRIM(COALESCE(waccion, '')));

    IF wnivel_aprobacion NOT IN ('JEFE', 'RRHH') THEN
        SELECT FALSE AS exito, 'Nivel de aprobación no válido.' AS mensaje, NULL AS nuevo_estado_solicitud;
        LEAVE proc_main;
    END IF;

    IF waccion NOT IN ('APROBADO', 'RECHAZADO') THEN
        SELECT FALSE AS exito, 'La acción debe ser APROBADO o RECHAZADO.' AS mensaje, NULL AS nuevo_estado_solicitud;
        LEAVE proc_main;
    END IF;

    -- Bloque aislado para manejar el NOT FOUND del SELECT FOR UPDATE
    BEGIN
        DECLARE CONTINUE HANDLER FOR NOT FOUND SET v_not_found = TRUE;
        SELECT id_aprobacion, estado, id_aprobador
        INTO v_id_aprobacion, v_estado, v_id_aprobador
        FROM t_permisos_aprobaciones
        WHERE id_permiso = wid_permiso
          AND nivel_aprobacion = wnivel_aprobacion
          AND fec_delete IS NULL
        FOR UPDATE;
    END;

    IF v_not_found THEN
        SELECT FALSE AS exito, 'No existe ese nivel de aprobación para la solicitud.' AS mensaje, NULL AS nuevo_estado_solicitud;
        LEAVE proc_main;
    END IF;

    IF v_estado <> 'PENDIENTE' THEN
        SELECT FALSE AS exito, 'Ese nivel de aprobación ya fue resuelto anteriormente.' AS mensaje, NULL AS nuevo_estado_solicitud;
        LEAVE proc_main;
    END IF;

    -- Reemplazo seguro de IS DISTINCT FROM
    IF wnivel_aprobacion = 'JEFE' AND NOT (v_id_aprobador <=> wid_actor) THEN
        SELECT FALSE AS exito, 'Solo el jefe directo asignado puede resolver este nivel.' AS mensaje, NULL AS nuevo_estado_solicitud;
        LEAVE proc_main;
    END IF;

    -- Lógica exclusiva de RRHH
    IF wnivel_aprobacion = 'RRHH' AND waccion = 'APROBADO' THEN
        SET wmetodo_descuento = UPPER(TRIM(COALESCE(wmetodo_descuento, '')));
        IF wmetodo_descuento NOT IN ('DINERO', 'VACACIONES', 'NO') THEN
            SELECT FALSE AS exito, 'RRHH debe indicar si el permiso es remunerado (Dinero, Vacaciones o No).' AS mensaje, NULL AS nuevo_estado_solicitud;
            LEAVE proc_main;
        END IF;

        IF wmetodo_descuento = 'VACACIONES' THEN
            -- Extraer datos de la solicitud
            SELECT es_por_horas, fecha_inicio, fecha_fin, hora_inicio, hora_fin, id_empleado
            INTO v_es_por_horas, v_fecha_inicio, v_fecha_fin, v_hora_inicio, v_hora_fin, v_id_empleado
            FROM t_solicitudes_permisos 
            WHERE id_permiso = wid_permiso;

            -- Cálculo de días solicitados
            IF v_es_por_horas THEN
                SET vdias_solicitados = TIME_TO_SEC(TIMEDIFF(v_hora_fin, v_hora_inicio)) / 3600 / 8;
            ELSE
                SET vdias_solicitados = fun_dias_habiles_rango(v_fecha_inicio, v_fecha_fin);
            END IF;

            -- Saldo disponible de vacaciones (helper escalar; proc_calcular_saldo_vacaciones
            -- devuelve un result set y no puede usarse como expresión escalar).
            SET vsaldo = fun_saldo_disponible_vacaciones(v_id_empleado);

            IF vsaldo IS NULL THEN
                SELECT FALSE AS exito, 'No se pudo calcular el saldo de vacaciones del empleado.' AS mensaje, NULL AS nuevo_estado_solicitud;
                LEAVE proc_main;
            END IF;

            IF vdias_solicitados > vsaldo THEN
                SELECT FALSE AS exito, CONCAT('Saldo de vacaciones insuficiente: disponible ', vsaldo, ' día(s), solicitado ', vdias_solicitados, '.') AS mensaje, NULL AS nuevo_estado_solicitud;
                LEAVE proc_main;
            END IF;
        END IF;

        UPDATE t_solicitudes_permisos
        SET metodo_descuento = CASE WHEN wmetodo_descuento = 'NO' THEN 'N/A' ELSE wmetodo_descuento END,
            usr_update = v_actor,
            fec_update = CURRENT_TIMESTAMP
        WHERE id_permiso = wid_permiso;
    END IF;

    -- Actualizar el estado de este nivel de aprobación
    UPDATE t_permisos_aprobaciones
    SET estado = waccion,
        id_aprobador = COALESCE(v_id_aprobador, wid_actor),
        observacion = wobservacion,
        fec_resolucion = CURRENT_TIMESTAMP,
        usr_update = v_actor,
        fec_update = CURRENT_TIMESTAMP
    WHERE id_aprobacion = v_id_aprobacion;

    -- Recalcular el estado consolidado de la solicitud
    SELECT estado INTO v_estado_jefe FROM t_permisos_aprobaciones WHERE id_permiso = wid_permiso AND nivel_aprobacion = 'JEFE';
    SELECT estado INTO v_estado_rrhh FROM t_permisos_aprobaciones WHERE id_permiso = wid_permiso AND nivel_aprobacion = 'RRHH';

    SET v_estado_final = CASE
        WHEN v_estado_jefe = 'RECHAZADO' OR v_estado_rrhh = 'RECHAZADO' THEN 'RECHAZADO'
        WHEN v_estado_jefe = 'APROBADO' AND v_estado_rrhh = 'APROBADO' THEN 'APROBADO'
        WHEN v_estado_jefe = 'APROBADO' OR v_estado_rrhh = 'APROBADO' THEN 'EN_REVISION'
        ELSE 'PENDIENTE'
    END;

    UPDATE t_solicitudes_permisos
    SET estado = v_estado_final,
        usr_update = v_actor,
        fec_update = CURRENT_TIMESTAMP
    WHERE id_permiso = wid_permiso;

    SELECT TRUE AS exito, 'Aprobación registrada correctamente.' AS mensaje, v_estado_final AS nuevo_estado_solicitud;
END //

DELIMITER ;