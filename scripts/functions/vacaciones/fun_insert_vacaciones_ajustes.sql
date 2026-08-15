-- Registra un ajuste manual de saldo de vacaciones (reconoce días ya disfrutados históricamente
-- que no quedaron como solicitud en el sistema, p. ej. al migrar a producción). Por ley el
-- empleado causa exactamente 15 días por ciclo: el ajuste SOLO puede restar (dias_ajuste < 0),
-- nunca sumar, y no puede dejar el saldo disponible por debajo de 0 (no se pueden restar más
-- días de los que realmente quedan disponibles en el ciclo vigente). Ledger append-only: para
-- corregir un ajuste mal cargado se anula con fun_softdelete_vacaciones_ajustes y se inserta uno
-- nuevo, nunca se edita el valor existente.
-- wfecha_ajuste ancla el ajuste al ciclo aniversario vigente en esa fecha (vacaciones no
-- acumulables): deja de contar automáticamente cuando ese ciclo se reinicia. Debe caer dentro
-- del ciclo vigente del empleado (fun_calcular_saldo_vacaciones); si no, quedaría "huérfano"
-- en un ciclo que nunca se vuelve a mostrar.
CREATE OR REPLACE FUNCTION fun_insert_vacaciones_ajustes(
    wid_empleado t_vacaciones_ajustes.id_empleado%TYPE,
    wdias_ajuste t_vacaciones_ajustes.dias_ajuste%TYPE,
    wmotivo t_vacaciones_ajustes.motivo%TYPE,
    wfecha_ajuste t_vacaciones_ajustes.fecha_ajuste%TYPE DEFAULT CURRENT_DATE
) RETURNS VARCHAR AS
$$
DECLARE
    vperiodo_inicio DATE;
    vperiodo_fin DATE;
    vsaldo_disponible NUMERIC;
BEGIN
    IF wid_empleado IS NULL OR NOT EXISTS (
        SELECT 1 FROM t_empleados WHERE id_usuario = wid_empleado AND fec_delete IS NULL
    ) THEN
        RETURN 'El empleado indicado no existe o está inactivo.';
    END IF;

    IF wdias_ajuste IS NULL OR wdias_ajuste >= 0 THEN
        RETURN 'El ajuste debe ser un número de días negativo (solo se permite restar días ya disfrutados).';
    END IF;

    IF wmotivo IS NULL OR LENGTH(TRIM(wmotivo)) < 5 THEN
        RETURN 'El motivo del ajuste debe tener al menos 5 caracteres.';
    END IF;

    wfecha_ajuste := COALESCE(wfecha_ajuste, CURRENT_DATE);

    SELECT periodo_inicio, periodo_fin, saldo_disponible INTO vperiodo_inicio, vperiodo_fin, vsaldo_disponible
    FROM fun_calcular_saldo_vacaciones(wid_empleado);

    IF vperiodo_inicio IS NULL THEN
        RETURN 'El empleado aún no cumple su primer año de servicio; no tiene un ciclo de vacaciones vigente.';
    END IF;

    IF wfecha_ajuste < vperiodo_inicio OR wfecha_ajuste > vperiodo_fin THEN
        RETURN format('La fecha del ajuste debe estar dentro del ciclo vigente (%s a %s).', vperiodo_inicio, vperiodo_fin);
    END IF;

    IF ABS(wdias_ajuste) > vsaldo_disponible THEN
        RETURN format('No puedes restar %s días: el saldo disponible actual es de solo %s días.', ABS(wdias_ajuste), vsaldo_disponible);
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
        COALESCE(NULLIF(current_setting('app.current_user', true), ''), CURRENT_USER),
        CURRENT_TIMESTAMP
    );

    RETURN 'Ajuste de vacaciones registrado correctamente.';

EXCEPTION
    WHEN OTHERS THEN
        RAISE NOTICE 'Error: %', SQLERRM;
        RETURN 'Ocurrió un error al registrar el ajuste.';
END;
$$
LANGUAGE PLPGSQL;

