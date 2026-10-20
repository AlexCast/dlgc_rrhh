DELIMITER //

CREATE OR REPLACE FUNCTION fun_listar_permisos_dias_aprobados(
    wid_dia_permiso INT
) RETURNS BOOLEAN
BEGIN
    DECLARE v_id_dia_permiso INT;
    DECLARE v_id_permiso INT;
    DECLARE v_id_empleado VARCHAR(20);
    DECLARE v_fecha DATE;
    DECLARE v_horas_aprobadas DECIMAL(4,2);
    DECLARE v_not_found BOOLEAN DEFAULT FALSE;

    DECLARE CONTINUE HANDLER FOR NOT FOUND SET v_not_found = TRUE;
    DECLARE EXIT HANDLER FOR SQLEXCEPTION RETURN FALSE;

    IF wid_dia_permiso IS NULL OR wid_dia_permiso <= 0 THEN
        RETURN FALSE;
    END IF;

    SELECT id_dia_permiso, id_permiso, id_empleado, fecha, horas_aprobadas
    INTO v_id_dia_permiso, v_id_permiso, v_id_empleado, v_fecha, v_horas_aprobadas
    FROM t_permisos_dias_aprobados
    WHERE id_dia_permiso = wid_dia_permiso
      AND fec_delete IS NULL
    LIMIT 1;

    IF v_not_found THEN
        RETURN FALSE;
    END IF;

    RETURN TRUE;
END //

DELIMITER ;